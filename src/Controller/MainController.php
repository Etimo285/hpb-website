<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(): Response
    {
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
        ]);
    }


     // On ajoute une sécurité pour être sûr que l'utilisateur est bien connecté, sinon on ne pourra pas changer son mot de passe.

    //  #[Route("/changer-mot-de-passe/", name: "change_password_test")]
    //  #[Security("is_granted('ROLE_ADHERENT')")]
    //  public function changePasswordTest(UserPasswordHasherInterface $encoder): Response
    //  {

    //      // On récupère l'utilisateur connecté
    //      $connectedUser = $this->getUser();

    //      // Définition complètement arbitraire d'un nouveau mot de passe, le mieux serait de récupérer un nouveau mot de passe depuis un formulaire
    //      $newPassword = 'azerty';

    //      // Grâce au service, on génère un nouveau hash de notre nouveau mot de passe
    //      $hashOfNewPassword = $encoder->hashPassword($connectedUser, $newPassword);

    //      // On change l'ancien mot de passe hashé par le nouveau que l'on a généré juste au dessus
    //      $connectedUser->setPassword( $hashOfNewPassword );


    //      // Sauvegarde des modifications en BDD grâce au manager des entités
    //      $em = $this->getDoctrine()->getManager();

    //      $em->flush();


    //      return $this->render('main/index.html.twig');

    //  }


}
