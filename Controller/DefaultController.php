<?php

namespace Hasheado\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Hasheado\BlogBundle\Entity\BlogComment as Comment;
use Hasheado\BlogBundle\Form\BlogCommentPostType as CommentType;

class DefaultController extends Controller
{
    /** Homepage */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('HasheadoBlogBundle:BlogPost')->getLatest();

        return $this->render('HasheadoBlogBundle:Default:index.html.twig', array(
        	'posts' => $posts,
    	));
    }

    /** Post detail action */
    public function postDetailAction($slug)
    {
    	$em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('HasheadoBlogBundle:BlogPost')->findOneBySlug($slug);

    	if (!$post) {
            throw $this->createNotFoundException(
                'No post found for slug '.$slug
            );
        }

        $comment = new Comment();
        $comment->setPost($post);
        $comment_form = $this->createForm(new CommentType(), $comment);

    	return $this->render('HasheadoBlogBundle:Default:post_detail.html.twig', array(
    		'post' => $post,
    		'form' => $comment_form->createView(),
		));
    }

    /** Comment action */
    public function commentAction()
    {
        $request = $this->getRequest();

        if ($request->isMethod('POST')) {

            $comment = new Comment();
            $form = $this->createForm(new CommentType(), $comment);

            $form->bind($request);
            $post = $comment->getPost();
            if ($form->isValid()) {
                $comment->setIsAccepted(false);
                $em = $this->getDoctrine()->getManager();
                $em->persist($comment);
                $em->flush();
                
                return $this->redirect(
                    $this->generateUrl('hasheado_blog_post_detail', array('slug' => $post->getSlug())) . '#comment-' . $comment->getId()
                );

            } else { //If it's not valid, render comment form with errors.
                return $this->render('HasheadoBlogBundle:Default:post_detail.html.twig', array(
                    'post' => $post,
                    'form' => $form->createView(),
                ));
            }

        } else {
            throw $this->createNotFoundException('Page not found.');
        }
    }

    /** Posts by category */
    public function byCategoryAction($slug)
    {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('HasheadoBlogBundle:BlogCategory')->findOneBySlug($slug);

        if (!$category) {
            throw $this->createNotFoundException(
                'No category found for slug '.$slug
            );
        }

        return $this->render('HasheadoBlogBundle:Default:by_category.html.twig', array(
            'category' => $category,
            'posts' => $category->getPosts(),
        ));
    }
}
