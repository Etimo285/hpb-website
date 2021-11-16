<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ProfilUpdateFormType;
use App\Form\RegistrationFormType;
use App\Recaptcha\RecaptchaValidator;
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
#[Route("/adherent", name:"adherent_")]

class AdherentController extends AbstractController
{
    // Page de gestion de l'adhérent
    #[Route('/gestion/', name: 'gestion')]
    public function gestion(): Response
    {
        return $this->render('adherent/gestion.html.twig');
    }

    // Page du profil de l'adhérent
    #[Route('/mon-profil/', name: 'profil')]
    public function profil(): Response
    {
        return $this->render('adherent/profil.html.twig');
    }

    // Page permettant de modifier un profil adhérent existant
    #[Route('/modifier-mon-profil/', name: 'profil_update')]
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

    #[Route("/changer-mon-mot-de-passe/", name: "change_password")]
    public function changePassword(Request $request, UserPasswordHasherInterface $encoder, RecaptchaValidator $recaptcha): Response
    {
        // On récupère l'utilisateur connecté

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





    // Page des alertes créées par l'adhérent
    #[Route("/mes-alertes/", name: 'alert')]

    public function alert(): Response
    {
        return $this->render('adherent/alert.html.twig');
    }

}


