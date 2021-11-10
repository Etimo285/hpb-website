<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\EditArticleFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    // MODIFIER ARTICLE
    #[Route('/modifier-article/{slug}/', name: 'article_edit')]
    public function projectEdit(Article $article, Request $request): Response
    {

        // Création du formulaire de modification de service et réinjection de la requête
        $form = $this->createForm(EditArticleFormType::class, $article);
        $form->handleRequest($request);

        // Contrôle sur la validité d'un formulaire envoyé
        if($form->isSubmitted() && $form->isValid()){

            // Gestion de l'envoi des données en base de données
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection sur la vue détaillée du projet
            return $this->redirectToRoute('article_view', [
                'slug' => $article->getSlug(),
            ]);
        }

        // Envoi de l'utilisateur sur la page d'édition du projet
        return $this->render('article/editArticle.html.twig', [
            'form' => $form->createView(),
            'slug' => $article->getSlug(),
            'article' => $article
        ]);
    }

    // SUPPRIMER ARTICLE
    #[Route('/supprimer-article/{id}/', name: 'article_delete')]
    public function projectDelete(Article $article, Request $request): Response
    {
        // Contrôle du token csrf
        if(!$this->isCsrfTokenValid('article_delete_' . $article->getId(), $request->query->get('csrf_token'))){

            // Message flash
            $this->addFlash('error', 'Token sécurité invalide, veuillez ré-essayer.');
        } else {

            // Gestion de la suppression des données en base de données
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();

            // Message flash
            $this->addFlash('success', 'L\'article a été supprimé avec succès !');
        }

        // Redirection sur la page d'interface
        return $this->redirectToRoute('home');
    }
}
