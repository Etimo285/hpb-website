<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Page de connexion
    #[Route('/connexion/', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {

            $this->addFlash('success', 'Vous êtes déjà connecté.');
            return $this->redirectToRoute('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Redirection en cas d'erreur d'authentification
        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    // Page de deconnexion
    #[Route('/deconnexion/', name: 'app_logout')]
    public function logout(): void
    {
        // Le code ici ne sera jamais lu car la page de déconnexion est déjà gérée en interne par le bundle security.

        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
