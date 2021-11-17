<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Category;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use WhiteOctober\BreadcrumbsBundle\Model\Breadcrumbs;

class MainController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function home(): Response
    {


        return $this->render('main/home.html.twig');
    }


    #[Route('/liste-articles/{slug}/', name: 'article_list')]
    public function articleList(Category $category, Request $request, PaginatorInterface $paginator): Response
    {

        // Récupération du numéro de page
        $requestedPage = $request->query->getInt('page', 1);

        // Vérification que le numéro est positif
        if ($requestedPage < 1) {
            throw new NotFoundHttpException();
        }

        $query = $category->getArticles();

        // On stocke dans $pageArticles les 10 articles de la page demandée dans l'URL
        $articles = $paginator->paginate(
            $query,
            $requestedPage,
            10
        );

        return $this->render('article/articleList.html.twig', [
            'category' => $category,
            'articles' => $articles
        ]);
    }

    #[Route('/{slug_category}/consulter-article/{slug}/', name: 'article_view')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function viewArticle(Article $article, Category $category): Response
    {

        $medias = $article->getMedia();

        return $this->render('article/viewArticle.html.twig', [
            'article' => $article,
            'slug_category' => $category->getSlug(),
            'medias' => $medias
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


    //      return $this->render('main/articleList.html.twig');

    //  }


}
