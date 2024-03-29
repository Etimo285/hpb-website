<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Form\AdminEditUserFormType;
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

    // GESTIONNAIRE ADMIN
    #[Route('/gestion/', name: 'gestion')]
    public function gestion(): Response
    {
        return $this->render('admin/adminGestion.html.twig');
    }

    // Liste des utilisateurs du site
    #[Route('/liste-utilisateurs/{orderBy}/{orderParam}', name: 'list_users')]
    public function userList($orderBy, $orderParam, UserRepository $userRepository): Response
    {
        // Switch d'ordre de tri (Ascendant ou descendant)
        if ($orderParam === 'ASC') {
            $orderParam = 'DESC';
        } else {
            $orderParam = 'ASC';
        }

        // On récupère la liste des utilisateurs selon le critère envoyé en donnée GET
        $users = $userRepository->findBy(array(), array($orderBy => $orderParam), null, 0);

        return $this->render('admin/usersList.html.twig', [
            'users' => $users,
            'orderBy' => $orderBy,
            'orderParam' => $orderParam
        ]);
    }

    // MODIFIER LE STATUS D'UN UTILISATEUR
    #[Route('/liste-utilisateurs/{id}', name: 'edit_user')]
    public function editUser(User $user, Request $request): Response
    {
        // Redirige vers la liste si l'utilisateur selectionné est l'utilisateur connecté.
        if($this->getUser() === $user) {

            $this->addFlash('error', 'Erreur : Vous ne pouvez modifier vos propres rôles et status.');

            return $this->redirectToRoute('admin_list_users', [
                'orderBy' => 'createdAt',
                'orderParam' => 'DESC'
            ]);
        } else {

            // Création du formulaire de modification des rôles et status
            $form = $this->createForm(AdminEditUserFormType::class, $user);
            $form->handleRequest($request);

            // Contrôle sur la validité du formulaire envoyé
            if ($form->isSubmitted() && $form->isValid()) {

                // Gestion de l'envoi des données en base de données
                $em = $this->getDoctrine()->getManager();

                // Hydratation du champ de date de mis à jour
                $user->setUpdatedAt();

                $em->persist($user);
                $em->flush();

                // Message flash
                $this->addFlash('success', "Le status de l\'utilisateur a été mis à jour avec succès !");

                // Redirection sur la page interface adhérent
                return $this->redirectToRoute('admin_list_users', [
                    'orderBy' => 'createdAt',
                    'orderParam' => 'DESC'
                ]);
            }

            return $this->render('admin/editUser.html.twig', [
                'form' => $form->createView(),
                'user' => $user
            ]);
        }
    }

    // SUPPRIMER UN UTILISATEUR
    #[Route('/supprimer-utilisateur/{id}/{origin_orderBy}/{origin_orderParam}', name: 'delete_user')]
    public function deleteUser($origin_orderBy, $origin_orderParam, User $user, Request $request): Response
    {
        // Si l'administrateur décide de supprimer son propre compte, n'effectue pas la suppression et redirige sur la liste.
        if($this->getUser() === $user) {

            $this->addFlash('error', 'Erreur : Vous ne pouvez supprimer votre propre compte.');

        } else {

            // Contrôle du token csrf
            if (!$this->isCsrfTokenValid('user_delete_' . $user->getId(), $request->query->get('csrf_token'))) {

                // Message flash
                $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer.');
            } else {

                // Gestion de la suppression des données en base de données
                $em = $this->getDoctrine()->getManager();

                /*
                 * [ Purge en BDD de tout ce qui est associé à l'utilisateur supprimé ]
                 */

                // Pour chaque articles écris par l'utilisateur...
                foreach ($user->getArticles() as $article) {

                    // Pour chaque commentaires de l'article (de n'importe quel utilisateur)...
                    foreach ($article->getComment() as $comment) {

                        // On supprimme ce commentaire
                        $em->remove($comment);
                    }

                    // Une fois tous les commentaires supprimés, on supprime cet article
                    $em->remove($article);
                }

                // Pour chaque commentaires postés par l'utilisateur (où qu'ils soient)...
                foreach ($user->getComments() as $comment) {

                    // On supprime ce commentaire
                    $em->remove($comment);
                }

                // Pour chaque alertes postés par l'utilisateur...
                foreach ($user->getAlerts() as $alert) {

                    // Pour chaque messages de discussion d'alerte (que ce soit par l'utilisateur ou un administrateur)...
                    foreach ($alert->getAlertMessage() as $alertMessage) {

                        // On supprime le message
                        $em->remove($alertMessage);
                    }

                    // Une fois tous les messages supprimés, on supprime l'alerte
                    $em->remove($alert);
                }

                // Pour chaque messages de discussion d'alerte posté par l'utilisateur (où qu'ils soient)...
                foreach ($user->getAlertMessages() as $alertMessage) {

                    // On supprime ce message
                    $em->remove($alertMessage);
                }

                // Pour chaque 'vues d'alerte' de la part de l'utilisateur (où qu'elles soient)...
                foreach ($user->getAlertViews() as $alertView) {

                    // On supprime la vue d'alerte
                    $em->remove($alertView);
                }

                // Pour chaque évènements écris par l'utilisateur...
                foreach ($user->getEvents() as $event) {

                    // On supprime l'évènement
                    $em->remove($event);
                }

                // Et enfin, suppression de l'utilisateur
                $em->remove($user);

                // Validation de toutes les requêtes de suppression
                $em->flush();

                // Message flash
                $this->addFlash('success', 'L\'utilisateur et tout ce qu\'il lui est associé ont étés supprimés avec succès !');

            }
        }

        return $this->redirectToRoute('admin_list_users', [
            'orderBy' => $origin_orderBy,
            'orderParam' => $origin_orderParam
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
    public function gestionCategory(Request $request): Response
    {
        // AJOUTER CATEGORIE
        $newCategory = new Category();
        $form = $this->createForm(CreateCategoryFormType::class, $newCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($newCategory);

            $em->flush();

            // Message flash
            $this->addFlash('success', 'Votre catégorie a été créé avec succès !');

            // Redirection sur la page de gestionnaire de catégories
            return $this->redirectToRoute('admin_category_gestion');
        }



        return $this->render('category/gestionCategory.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    // MODIFIER CATEGORIE
    #[Route('/gestionnaire-categories/modifier/{slug}', name: 'category_edit')]
    public function editCategory(Category $category, Request $request): Response
    {

        // Formulaire de modification de catégorie
        $form = $this->createForm(EditCategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            // Message flash
            $this->addFlash('success', 'La catégorie à été modifiée avec succès !');

            // Redirection sur la page de gestionnaire de catégories
            return $this->redirectToRoute('admin_category_gestion');
        }

        return $this->render('category/editCategory.html.twig', [
            'form' => $form->createView(),
            'category' => $category // Récupération des données via l'id, pour afficher une prévisualisation
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

    // SUPPRESSION CATEGORIE
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
