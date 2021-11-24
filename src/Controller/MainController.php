<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Entity\Article;
use App\Entity\Category;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
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
    public function articleList(UserRepository $userRepository, Category $category, Request $request, PaginatorInterface $paginator): Response
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
            'articles' => $articles,
            'users' => $userRepository->findAll()
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



    // Tester l'envoi d'email
    #[Route("/envoyer-un-email-de-test/", name:"send_email_test")]

    public function sendEmailTest(MailerInterface $mailer): Response
    {

        // Le mailer est récupéré automatiquement en paramètre par autowiring dans $mailer

        // Création du mail
        $email = (new TemplatedEmail())
            ->from(new Address('expediteur@exemple.fr', 'noreply'))
            ->to('destinataire@gmail.com')
            ->subject('Sujet du mail')
            ->htmlTemplate('test/test.html.twig')    // Fichier twig du mail en version html
            ->textTemplate('test/test.txt.twig')     // Fichier twig du mail en version text
            /* Il est possible de faire passer aux deux  templates twig des variables en ajoutant le code suivant :
            ->context([
                'fruits' => ['Pomme', 'Cerise', 'Poire']
            ])
            */
        ;

        // Envoi du mail
        $mailer->send($email);

        // Affichage d'une vue quelconque
        return $this->render('main/sendEmailTest.html.twig');
    }






}
