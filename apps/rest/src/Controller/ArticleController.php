<?php

namespace App\Controller;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;

use App\Entity\Article;

class ArticleController extends AbstractFOSRestController {

    private function getById(Request $request)
    {
        return $this->getDoctrine()
            ->getRepository('App\Entity\Article')
            ->find($request->get('id'));
    }

    private function setArticleFields(Article $article, Request $request)
    {
        $article->setTitle($request->get('title'));
        $article->setAuthor($request->get('author'));
        $article->setBody($request->get('body'));
        $article->setUrl($request->get('url'));
    }

    /**
     * @Rest\Post("/articles")
     * @param Request $request
     * @return View
     */
    public function postArticle(Request $request): View
    {
        $article = new Article();
        $this->setArticleFields($article, $request);

        $em = $this->getDoctrine()->getManager();
        $em->persist($article);
        $em->flush();

        return View::create($article, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/articles/{id}")
     * @param Request $request
     * @return View
     */
    public function getArticle(Request $request): View
    {
        $article = $this->getById($request);
        return View::create($article, Response::HTTP_OK);
    }

    /**
     * @Rest\Get("/articles")
     * @return View
     */
    public function getArticles(): View
    {
        $articles = $this->getDoctrine()
            ->getRepository('App\Entity\Article')
            ->findAll();

        return View::create($articles, Response::HTTP_OK);
    }

    /**
     * @Rest\Put("/articles/{id}")
     * @param Request $request
     * @return View
     */
    public function putArticle(Request $request): View
    {
        $article = $this->getById($request);

        if ($article) {
            $this->setArticleFields($article, $request);

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
        }

        return View::create($article, Response::HTTP_OK);
    }

    /**
     * @Rest\Delete("/articles/{id}")
     * @param Request $request
     * @return View
     */
    public function deleteArticle(Request $request)
    {
        $article = $this->getById($request);

        if ($article) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();
        }

        return View::create($article, Response::HTTP_OK);
    }

}