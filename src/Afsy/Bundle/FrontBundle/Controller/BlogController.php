<?php

namespace Afsy\Bundle\FrontBundle\Controller;

use Afsy\Bundle\CoreBundle\Entity\Article;
use Afsy\Bundle\CoreBundle\Entity\Tag;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Blog controller
 */
class BlogController extends Controller
{
    /**
     * homepage
     *
     * @Template()
     */
    public function indexAction()
    {
        $query = $this->getDoctrine()->getRepository('AfsyCoreBundle:Article')->getQuery();
        $pagination = $this->get('knp_paginator')->paginate(
            $query,
            $this->get('request')->query->get('page', 1)
        );
        $tagManager = $this->get('fpn_tag.tag_manager');

        foreach ($pagination as $article) {
            $tagManager->loadTagging($article);
        }

        return array('pagination' => $pagination);
    }

    /**
     * @Template()
     */
    public function showAction(Article $article, $preview = false)
    {
        if (!$preview && !$article->getIsPublished()) {
            throw new NotFoundHttpException('Unknown article slug.');
        }

        $tagManager = $this->get('fpn_tag.tag_manager');
        $tagManager->loadTagging($article);

        return array('article' => $article);
    }

    /**
     * @Template("AfsyFrontBundle:Blog:index.html.twig")
     */
    public function showTagAction(Tag $tag)
    {
        $query = $this->getDoctrine()->getRepository('AfsyCoreBundle:Article')->getQueryForTag($tag);
        $pagination = $this->get('knp_paginator')->paginate(
            $query,
            $this->get('request')->query->get('page', 1)
        );
        $tagManager = $this->get('fpn_tag.tag_manager');

        foreach ($pagination as $article) {
            $tagManager->loadTagging($article);
        }

        return array(
            'pagination' => $pagination,
            'tag' => $tag
        );
    }

    public function feedAction()
    {
        $articles = $this->getDoctrine()->getRepository('AfsyCoreBundle:Article')->getLast(10);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/atom+xml');

        return $this->render('AfsyFrontBundle:Blog:feed.atom.twig', array(
            'articles'  => $articles
        ), $response);
    }
}
