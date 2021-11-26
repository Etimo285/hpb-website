<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\CreateAlertFormType;
use App\Form\CreateArticleFormType;
use App\Form\EditArticleFormType;
use App\Form\ProfilUpdateFormType;
use App\Form\RegistrationFormType;
use App\Recaptcha\RecaptchaValidator;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

// Pages accessibles aux adhérents
#[IsGranted('ROLE_ADHERENT')]

// Préfixes de la route et du nom des pages adhérent
#[Route(name:"adherent_")]

class AdherentController extends AbstractController
{

    // Page de gestion de l'adhérent
    #[Route('/gestionnaire-utilisateur/', name: 'gestion')]
    public function gestion(): Response
    {
        return $this->render('adherent/adherentGestion.html.twig');
    }

    // Page du profil de l'adhérent
    #[Route('/gestionnaire-utilisateur/mon-profil/', name: 'profil')]
    public function profil(): Response
    {
        return $this->render('adherent/profil.html.twig');
    }


    // Page permettant de modifier un profil adhérent existant
    #[Route('/gestionnaire-utilisateur/mon-profil/modifier-mon-profil/', name: 'profil_update')]
    public function profilUpdate(Request $request): Response
    {
        // Récupération des données de l'utilisateur
        $user = $this->getUser();

        // Création du formulaire de modification de profil
        $form = $this->createForm(ProfilUpdateFormType::class, $user);

        // Liaison des données de requête (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if ($form->isSubmitted() && $form->isValid()) {

            // Sauvegarde des changements faits dans le profil via le manager général des entités
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Profil modifié avec succès !');

            // Redirection vers la page de profil modifié
            return $this->redirectToRoute('adherent_profil', [
            ]);
        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('adherent/profil_update.html.twig', [
            'profilUpdateForm' => $form->createView(),
        ]);
    }


    // Page de changement de mot de passe de l'adhérent
    // On ajoute une sécurité pour être sûr que l'utilisateur est bien connecté, sinon on ne pourra pas changer son mot de passe.
    #[Route("/gestionnaire-utilisateur/mon-profil/changer-mon-mot-de-passe/", name: "change_password")]
    public function changePassword(Request $request, UserPasswordHasherInterface $encoder, RecaptchaValidator $recaptcha): Response
    {

        // On récupère l'utilisateur connecté
        /** @var $connectedUser PasswordAuthenticatedUserInterface * */
        $connectedUser = $this->getUser();

        // Création d'un nouveau formulaire de modification de mot de passe
        $form = $this->createForm(ChangePasswordFormType::class, $connectedUser);

        // Remplissage du formulaire avec les données POST (dans $request)
        $form->handleRequest($request);

        // Si le formulaire a été envoyé
        if ($form->isSubmitted()) {

            // Récupération de la valeur du captcha ( $_POST['g-recaptcha-response'] )
            $captchaResponse = $request->request->get('g-recaptcha-response', null);

            // Récupération de l'adresse IP de l'utilisateur ( $_SERVER['REMOTE_ADDR'] )
            $ip = $request->server->get('REMOTE_ADDR');

            // Si le captcha est null ou si il est invalide, ajout d'une erreur générale sur le formulaire (qui sera considéré comme échoué après)
            if ($captchaResponse == null || !$recaptcha->verify($captchaResponse, $ip)) {

                // Ajout d'une nouvelle erreur dans le formulaire
                $form->addError(new FormError('Veuillez remplir le captcha de sécurité'));
            }

            // Si le formulaire n'a pas d'erreur
            if ($form->isValid()) {

                // Nouveau mot de passe récupéré depuis le formulaire
                $newPassword = $form->get('plainPassword')->getData();

                // Grâce au service, on génère un nouveau hash de notre nouveau mot de passe
                $hashOfNewPassword = $encoder->hashPassword($connectedUser, $newPassword);

                // On change l'ancien mot de passe hashé par le nouveau que l'on a généré juste au dessus
                $connectedUser->setPassword($hashOfNewPassword);

                // Sauvegarde des modifications en BDD grâce au manager des entités
                $em = $this->getDoctrine()->getManager();
                $em->flush();

                // Message flash de succès
                $this->addFlash('success', 'Votre mot de passe a bien été modifié.');

                // Redirection vers la page de profil modifié
                return $this->redirectToRoute('adherent_profil', [
                ]);

            }
        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('adherent/change_password.html.twig', [
            'ChangePasswordForm' => $form->createView(),
        ]);

        }


    // Page d'accueil de l'alerte-Inclusion
    #[Route('/alerte-inclusion/', name: 'alert_def')]
    public function defAlert(): Response
    {
        // Appel de la vue correspondante
        return $this->render('alert/defAlert.html.twig');
    }

    // Création d'une Alerte Adhérent
    #[Route('/creer-alerte/', name: 'alert_create')]
    public function createAlert(Request $request): Response
    {

        // Création d'une nouvelle alerte
        $alert= new Alert();
        // Création d'un formulaire de création d'alerte, lié à l'alerte vide
        $form = $this->createForm(CreateAlertFormType::class, $alert);

        // Liaison des données de requête (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if ($form->isSubmitted() && $form->isValid()) {

            // Hydratation de l'alerte pour la date et l'auteur

            /** @var $user User **/
            $user = $this->getUser();
            $alert->setAuthor($user);     // L'auteur est l'adhérent connecté
            $alert->setCreatedAt();       // Date actuelle
            $alert->setUpdatedAt();

            // Sauvegarde de l'alerte dans la base de données via le manager général des entités
            $em = $this->getDoctrine()->getManager();
            $em->persist($alert);
            $em->flush();

            // Message flash
            $this->addFlash('success', 'Votre alerte à bien été créée !');

            // Redirection sur la page interface adhérent
            return $this->redirectToRoute('adherent_alert');
        }

        // // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('alert/createAlert.html.twig', [
            'createAlertForm' => $form->createView()
        ]);

    }

    // Page permettant de lire une alerte en détail par l'admin ET l'adhérent concerné
    #[Route("/consulter-alerte/{slug}", name: "alert_view")]
    public function viewAlert(Alert $alert, Request $request): Response
    {
        $medias = $alert->getMedia();

        return $this->render('alert/viewAlert.html.twig', [
            'alert' => $alert,
            'slug' => $alert  ->getSlug(),
            'medias' => $medias
        ]);
    }

    // Modification d'une alerte adhérent
    #[Route('/modifier-alerte/{slug}/', name: 'edit_alert')]
    public function alertEdit(Alert $alert, Request $request): Response
    {

        // Création du formulaire de modification d'alerte (c'est le même que le formulaire permettant de créer une nouvelle alerte, sauf qu'il sera déjà rempli avec les données de l'alerte existante "$alert")
        $form = $this->createForm(CreateAlertFormType::class, $alert);

        // Liaison des données de requête (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            //TODO: Si modification avec content vide -> Error500 (NULL : expected string)

            // Hydratation de la modification de l'alerte pour la date et l'auteur
            /** @var $user User **/
            $user = $this->getUser();
            $alert->setAuthor($user);     // L'auteur est l'adhérent connecté
            $alert->setUpdatedAt();

            // Sauvegarde des changements faits dans l'alerte via le manager général des entités
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash de succès
            $this->addFlash('success', 'Alerte modifiée avec succès !');

            // Redirection vers la page de l'alerte modifiée suivant la hierarchie
            if($this->isGranted('ROLE_ADMIN')){
                return $this->redirectToRoute('admin_list_alert', [
                    'slug' => $alert->getSlug(),
                ]);
            } else {
                return $this->redirectToRoute('adherent_alert', [
                    'slug' => $alert->getSlug(),
                ]);

            }

        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('alert/editAlert.html.twig', [
            'createAlertForm' => $form->createView(),
            'slug' => $alert->getSlug(),
            'alert' => $alert
        ]);
    }


    // Page qui liste les alertes créées par l'adhérent

    #[Route('/gestionnaire-utilisateur/mes-alertes/', name: 'alert')]
    public function alertAdherentList(): Response
    {
        // Récupération du manager général des entités
        $em = $this->getDoctrine()->getManager();

        // Récupération des alertes de l'adhérent connecté
        $user = $this->getUser();
        $alertsRepository = $this->getDoctrine()->getRepository(Alert::class);
        $alerts = $alertsRepository->findByAuthor($user);


        // Appel de la vue pour affichage de la liste des alertes créées par l'adhérent
        return $this->render('adherent/alertAdherent.html.twig', [
            'alerts' => $alerts,
        ]);
    }

    // Modifier commentaire
    #[Route('{slug_category}/{slug}/modifier-commentaire/{id}', name: 'comment_edit')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function editComment(Category $category, Comment $comment, Request $request): Response
    {
        // Redirection vers la view si l'utilisateur connecté n'est pas l'auteur du commentaire
        if ($this->getUser() !== $comment->getAuthor()) {

            $this->addFlash('error', 'Accès restreint : La modification est impossible, vous n\'êtes pas auteur de ce commentaire.');

            return $this->redirectToRoute('article_view', [
                'article', $comment->getArticle(),
                'slug' => $comment->getArticle()->getSlug(),
                'slug_category' => $category->getSlug()
            ]);

        }

        // Création du formulaire de modification de commentaire et réinjection de la requête
        $form = $this->createFormBuilder($comment)
            ->add('content', TextareaType::class, array('label' => false))
            ->getForm();

        $form->handleRequest($request);

        // Contrôle sur la validité du formulaire envoyé
        if($form->isSubmitted() && $form->isValid()){

            // Hydratation
            $comment->setUpdatedAt();

            // Gestion de l'envoi des données en base de données
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash
            $this->addFlash('success', 'Le commentaire a été modifié avec succès !');

            // Redirection sur la vue détaillé de l'article
            return $this->redirectToRoute('article_view', [
                'slug' => $comment->getArticle()->getSlug(),
                'slug_category' => $category->getSlug(),
            ]);

        }

        return $this->render('article/editComment.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'slug' => $comment->getArticle()->getSlug(),
            'slug_category' => $category->getSlug()
        ]);
    }

    // Lorsque qu'un commentaire est supprimé
    #[Route('{slug_category}/supprimer-commentaire/{id}', name: 'comment_delete')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function deleteComment(Category $category, Comment $comment, Request $request): Response
    {
        $article = $comment->getArticle();

        if ($comment->getAuthor() !== $this->getUser() && !$this->isGranted("ROLE_ADMIN")) {

            $this->addFlash('error', 'Accès restreint : Vous n\'êtes ni administrateur, ni auteur de ce commentaire.');

        } else {

            // Contrôle du token csrf
            if (!$this->isCsrfTokenValid('comment_delete_' . $comment->getId(), $request->query->get('csrf_token'))) {

                // Message flash
                $this->addFlash('error', 'Token de sécurité invalide, veuillez ré-essayer.');
            } else {

                // Gestion de la suppression des données en base de données
                $em = $this->getDoctrine()->getManager();

                $em->remove($comment);
                $em->flush();

                // Message flash
                $this->addFlash('success', 'Le commentaire a été supprimé avec succès !');

            }

        }

        return $this->redirectToRoute('article_view', [
            'article' => $article,
            'slug' => $article->getSlug(),
            'slug_category' => $category->getSlug()
        ]);
    }
}


