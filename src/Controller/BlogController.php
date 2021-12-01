<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
use App\Form\ArticleType;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ["is_published" => true],
            ["publication_date" => "desc"]
        );

        return $this->render('blog/index.html.twig', [
            "articles" => $articles
        ]);
    }

    /** 
    * @Route("/edit/{id}", name= "article_edit")
    */
    public function edit(Article $article, Request $request): Response {
        $oldPicture = $article->getPicture();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateTime(new \DateTime());

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            if ($article->getPicture() !== null && $article->getPicture() !== $oldPicture) {
                $file = $form->get('picture')->getData();
                $fileName = uniqid() . '.' . $file->guessExtension();

            try {
                $file->move(
                $this->getParameter('images_directory'),
                $fileName
                );
            } catch (FileException $e) {
                return new Response($e->getMessage());
            }

            $article->setPicture($fileName);
            } else {
                $article->setPicture($oldPicture);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute("homepage");
        }

    return $this->render("blog/edit.html.twig", [
        "article" => $article,
        "form" => $form->createView()
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

            if ($article->getPicture() != null) {
                $file = $form->get("picture")->getData();
                $filename = uniqid() . "." . $file->guessExtension();

                try {
                    $file->move(
                // Le dossier dans lequel le fichier va être chargé
                    $this->getParameter("images_directory"),
                    $filename
                );
                } catch (FileException $e) {
                    return new Response($e->getMessage());
                }

                $article->setPicture($filename);
            }

            if($article->getIsPublished()) {
                $article->setPublicationDate(new DateTime());
            }
    
            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute("homepage");
        }


        return $this->render('blog/add.html.twig', [
            "form" => $form->createView()
        ]);
    }

    /** 
    * @Route("/remove/{id}", name= "article_remove")
    */
    public function remove(Article $article): Response
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        return $this->redirectToRoute("homepage");
    }

    /** 
    * @Route("/show/{id}", name= "article_show")
    */
    public function show(Article $article): Response {
        return $this->render('blog/show.html.twig', [
            "article" => $article
        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin() {
        $articles = $this->getDoctrine()->getRepository(Article::class)-> findBy(
            [],
            ["last_update_time" => "desc"]
        );

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render("admin/index.html.twig", [
            "articles" => $articles,
            "users" => $users
        ]);
    }
}
