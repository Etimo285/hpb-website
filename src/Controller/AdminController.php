<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\PersistentCollection;
use App\Form\CreateArticleFormType;
use App\Form\CreateCategoryFormType;
use App\Form\EditCategoryFormType;
use App\Form\EditArticleFormType;
use App\Repository\CategoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\All;

// Les pages ne sont accessibles qu'aux administrateurs
#[IsGranted('ROLE_ADMIN')]

// Préfixes de la route et du nom des pages adhérent
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{

    /* GESTIONNAIRE ADMIN */
    #[Route('/gestion/', name: 'gestion')]
    public function gestion(): Response
    {
        return $this->render('admin/adminGestion.html.twig');
    }

    #[Route('/liste-adherents/', name: 'list_adherent')]
    public function userList(UserRepository $userRepository): Response
    {

        return $this->render('admin/adherentList.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    // AJOUTER ARTICLE
    #[Route('/creer-article/', name: 'article_create')]
    public function createArticle(Request $request): Response
    {

        // Création d'un nouvel objet de la classe Article, vide pour le moment
        $article = new Article();
        $form = $this->createForm(CreateArticleFormType::class, $article);
        $form->handleRequest($request);

        // Contrôle sur la validité du formulaire envoyé
        if ($form->isSubmitted() && $form->isValid()) {

            // Gestion de l'envoi des données en base de données
            $em = $this->getDoctrine()->getManager();

            /** @var $user User **/
            $user = $this->getUser();
            $article->setAuthor($user);
            $article->setCreatedAt();
            $article->setUpdatedAt();
            $article->setHidden(0);
            $em->persist($article);
            $em->flush();

            // Message flash
            $this->addFlash('success', 'Votre article à été créé avec succès !');

            // Redirection sur la page interface adhérent
            return $this->redirectToRoute('home');
        }

        // Pour que la vue puisse afficher le formulaire, on doit lui envoyer le formulaire généré, avec $form->createView()
        return $this->render('article/createArticle.html.twig', [
            'form' => $form->createView()
        ]);

    }

    // MODIFIER ARTICLE
    #[Route('/{slug_category}/modifier-article/{slug}/', name: 'article_edit')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function articleEdit(Category $category, Article $article, Request $request): Response
    {

        // Condition d'interdiction d'accès à cette page pour ceux qui ne sont pas auteurs de leur articles
        if ($this->getUser() !== $article->getAuthor()) {

            $this->addFlash('error', 'Accès restreint : Vous n\'avez pas accès à la modification de cet article car vous n\'en n\'êtes pas l\'auteur.');

            return $this->redirectToRoute('article_view', [
                'slug' => $article->getSlug(),
                'slug_category' => $category->getSlug()
            ]);

        }

        // Création du formulaire de modification d'article et réinjection de la requête
        $form = $this->createForm(EditArticleFormType::class, $article);
        $form->handleRequest($request);

        // Contrôle sur la validité d'un formulaire envoyé
        if($form->isSubmitted() && $form->isValid()){

            // Hydratation
            $article->setUpdatedAt();

            // Gestion de l'envoi des données en base de données
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection sur la vue détaillée du projet
            return $this->redirectToRoute('article_view', [
                'slug' => $article->getSlug(),
                'slug_category' => $category->getSlug(),
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
    #[Route('{slug_category}/supprimer-article/{id}/', name: 'article_delete')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function articleDelete(Category $category, Article $article, Request $request): Response
    {
        // Contrôle du token csrf
        if (!$this->isCsrfTokenValid('article_delete_' . $article->getId(), $request->query->get('csrf_token'))){

            // Message flash
            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer.');
        } else {

            // Gestion de la suppression des données en base de données
            $em = $this->getDoctrine()->getManager();
            $em->remove($article);
            $em->flush();

            // Message flash
            $this->addFlash('success', "L'article {$article->getTitle()} a été supprimé avec succès !");
        }

        // Redirection sur la page d'interface
        return $this->redirectToRoute('article_list', [
            'slug' => $category->getSlug(),
        ]);
    }

    // CACHER ARTICLE
    #[Route('/{slug_category}/cacher-article/{id}/{origin}', name: 'article_hide')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function hideArticle($origin, Category $category, Article $article, Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();

        if (!$article->getHidden()) {
            $article->setHidden(true);
        } else {
            $article->setHidden(false);
        }

        $em->flush();

        // Condition pour sélectionner la bonne route grâce à $origin, variable contenant le nom de la route précédente
        $redirectionParams = [];

        if ($origin === 'article_view') {
            $redirectionParams = [
                'slug' => $article->getSlug(),
                'slug_category' => $category->getSlug()
            ];
        } elseif ($origin === 'article_list') {
            $redirectionParams = [
                'slug' => $category->getSlug()
            ];
        }

        return $this->redirectToRoute($origin, $redirectionParams);
    }

    /* GESTIONNAIRE DES CATEGORIES */

    #[Route('/gestionnaire-categories/', name: 'category_gestion')]
    //#[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function gestionCategory(Request $request): Response
    {

        //-- Ajout --//
        $newCategory = new Category();
        $creationForm = $this->createForm(CreateCategoryFormType::class, $newCategory);
        $creationForm->handleRequest($request);

        if ($creationForm->isSubmitted() && $creationForm->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($newCategory);

            $em->flush();

            // Message flash
            $this->addFlash('success', 'Votre catégorie a été créé avec succès !');

            // Redirection sur la page de gestionnaire de catégories
            return $this->redirectToRoute('admin_category_gestion');
        }

        //-- Modification --//
        //$editForm = $this->createForm(EditCategoryFormType::class, $category);
        //$editForm->handleRequest($request);
//
        //if ($creationForm->isSubmitted() && $creationForm->isValid()) {
//
        //    $em = $this->getDoctrine()->getManager();
        //    $em->persist($newCategory);
        //    $em->flush();
//
        //    // Message flash
        //    $this->addFlash('success', 'Le nom de votre catégorie à été modifié avec succès !');
//
        //    // Redirection sur la page de gestionnaire de catégories
        //    return $this->redirectToRoute('category_gestion');
        //}

        return $this->render('category/gestionCategory.html.twig', [
            'createCategoryForm' => $creationForm->createView(),
            //'editCategoryForm' => $editForm->createView()
        ]);

    }

    // --  Suppression -- //

    // Page de confirmation de suppression
    #[Route('/gestionnaire-categories/confirmer-suppression/{slug}', name: 'category_confirm_delete')]
    public function confirmDelete(Category $category, Request $request)
    {


        return $this->render('category/confirmDeletion.html.twig', [
            'category' => $category,
            'articles' => $category->getArticles()
        ]);
    }

    // Suppression
    #[Route('/gestionnaire-categories/supprimer/{id}', name: 'category_delete')]
    public function deleteCategory(Category $category, Request $request): Response
    {

        // Contrôle du token csrf
        if(!$this->isCsrfTokenValid('category_delete_' . $category->getId(), $request->query->get('csrf_token'))){

            // Message flash
            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer.');
        } else {



            // Gestion de la suppression des données en base de données
            $em = $this->getDoctrine()->getManager();

            $categoryArticles = $category->getArticles();

            foreach ($categoryArticles as $article) {
                $em->remove($article);
            }

            $em->remove($category);
            $em->flush();

            // Message flash
            $this->addFlash('success', 'La catégorie et tout ses articles associés ont étés supprimés avec succès !');

        }

        return $this->redirectToRoute('category_gestion');
    }


}
