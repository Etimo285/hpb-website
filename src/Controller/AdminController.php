<?php

namespace App\Controller;

use App\Entity\Alert;
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
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

            // Redirection sur la vue détaillée de l'article
            return $this->redirectToRoute('article_view', [
                'slug' => $article->getSlug(),
                // On récupère le slug de la donnée envoyée en formulaire afin de rediriger sur la bonne page de catégorie.
                'slug_category' => $form->get('category')->getData()->getSlug(),
            ]);
        }

        // Envoi de l'utilisateur sur la page d'édition de l'article
        return $this->render('article/editArticle.html.twig', [
            'form' => $form->createView(),
            'slug' => $article->getSlug(),
            'slug_category' => $category->getSlug(),
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

            // Suppression de tous les commentaires appartenant à l'article via une boucle Foreach
            $comments = $article->getComment();

            foreach ($comments as $comment) {
                $em->remove($comment);
            }

            // Suppression de l'article
            $em->remove($article);
            $em->flush();

            // Message flash en cas de succès
            $this->addFlash('success', "L'article {$article->getTitle()} et tout ses commentaires ont étés supprimé avec succès !");
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

        return $this->redirectToRoute('admin_category_gestion');
    }

    // Page qui liste toutes les alertes
    #[Route("/liste-alertes/", name: "list_alert")]
    public function alertList(Request $request, PaginatorInterface $paginator): Response
    {
        // Récupération du numéro de la page demandée dans l'URL
        $requestedPage = $request->query->getInt('page', 1);

        // Vérification que le numéro est positif
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager général des entités
        $em = $this->getDoctrine()->getManager();

        // Création d'une requête permettant de récupérer les alertes (uniquement ceux de la page demandée, grâce au paginator,et non toutes les alertes)
        $query = $em->createQuery('SELECT a FROM App\Entity\Alert a ORDER BY a.createdAt DESC');

        // Récupération des alertes
        $alerts = $paginator->paginate(
            $query,             // Requête créée précedemment
            $requestedPage,     // Numéro de la page demandée
            10              // Nombre d'articles affichés par page
        );

        // Appel de la vue en envoyant les alertes à afficher
        return $this->render('alert/alertList.html.twig', [
            'alerts' => $alerts,
        ]);
    }


    // Page admin servant à supprimer une alerte via son id passé dans l'URL
    #[Route('/supprimer-alerte/{id}/', name: 'alert_delete')]
    public function alertDelete(Alert $alert, Request $request): Response
    {
        // Si le token CSRF passé dans l'url n'est pas le token valide, message d'erreur
        if(!$this->isCsrfTokenValid('alert_delete_' . $alert->getId(), $request->query->get('csrf_token'))){

            // Message flash d'erreur
            $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer !.');
        } else {

            // Suppression de l'alerte via le manager général des entités
            $em = $this->getDoctrine()->getManager();
            $em->remove($alert);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'L\'alerte a été supprimé avec succès !');
        }

        // Redirection de l'utilisateur sur la liste des alertes
        return $this->redirectToRoute('admin_list_alert');
    }

}
