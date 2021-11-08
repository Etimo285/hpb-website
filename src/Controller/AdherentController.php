<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Pages accessibles aux adhérents
#[IsGranted('ROLE_ADHERENT')]

// Préfixes de la route et du nom des pages adhérent
#[Route("/adherent", name:"adherent_")]

class AdherentController extends AbstractController
{
    #[Route('/')]
    public function index(): Response
    {
        return $this->render('adherent/index.html.twig', [
            'controller_name' => 'AdherentController',
        ]);
    }

    #[Route('/mon-profil/', name: 'profil')]

    public function profil(): Response
    {
        return $this->render('adherent/profil.html.twig');
    }
}
