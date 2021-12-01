<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogController extends AbstractController
{
    /** 
    * @Route("/", name= "homepage")
    */
    public function index(): Response
    {
        return $this->render('blog/index.html.twig');
    }

    /** 
    * @Route("/edit/{id}", name= "article_edit")
    */
    public function edit($id): Response
    {
        return $this->render('blog/edit.html.twig', [
            "id" => $id
        ]);
    }

    /** 
    * @Route("/add", name= "article_add")
    */
    public function add(Request $request): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest(($request));
        if($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateTime(new DateTime());
            if($article->getIsPublished()) {
                $article->setPublicationDate(new DateTime());
            }
    
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return new Response("L'article a bien été enregistré");
        }


        return $this->render('blog/add.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /** 
    * @Route("/remove/{id}", name= "article_remove")
    */
    public function remove($id): Response
    {
        return new Response("<h1>Article $id supprimé</h1>");
    }

    /** 
    * @Route("/show/{id}", name= "article_show")
    */
    public function show($id): Response
    {
        return $this->render('blog/show.html.twig', [
            "id" => $id
        ]);
    }
}