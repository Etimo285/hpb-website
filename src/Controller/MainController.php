<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Form\NewFunctionTitleFormType;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function home(): Response
    {


        return $this->render('main/home.html.twig');
    }

    #[Route('/actualité/{limit}', name: 'new_articles_list')]
    public function newArticlesList($limit ,ArticleRepository $articleRepository, Request $request): Response
    {
        //Récupération des $limit derniers articles selon leur date de création
        $articles = $articleRepository->findBy(array(), array('createdAt' => 'DESC'), $limit, 0);

        return $this->render('article/newArticlesList.html.twig', [
            'articles' => $articles,
            'limit' => $limit
        ]);
    }

    // Liste des articles d'une catégorie
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

        $admins = $userRepository->findUserByRole(['ROLE_ADMIN']);
        $users = [];

        foreach ($admins as $admin) {
            $form = $this->createFormBuilder($admin)
                ->add('functionTitle', TextType::class, array('label' => false, 'required' => false))
                ->add('save', SubmitType::class)
                ->getForm();

            $form->handleRequest($request);

            // Tableau associatif de l'utilisateur et son formulaire
            $user = array('entity' => $admin, 'entityForm' => $form->createView());

            // Push dans un tableau "users"
            $users[] = $user;


            if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                $admin->setFunctionTitle($form->get('functionTitle')->getData());

                $em->persist($admin);
                $em->flush();

            }

        }

        return $this->render('article/articleList.html.twig', [
            'category' => $category,
            'articles' => $articles,
            'users' => $users,
        ]);
    }

    #[Route('/{slug_category}/consulter-article/{slug}/', name: 'article_view')]
    #[ParamConverter('category', class: 'App\Entity\Category', options: ['mapping' =>['slug_category' => 'slug']])]
    public function viewArticle(Article $article, Category $category, Request $request): Response
    {

        // Restriction d'un article caché si l'utilisateur n'est pas connecté.
        if ($article->getHidden() && $this->getUser() === null) {

        $this->addFlash('error', 'Accès restreint : Cet article est caché. Veuillez vous connecter en tant qu\'administrateur pour y accéder.');

            return $this->redirectToRoute('app_login');

        // Condition pour restreindre l'accès si l'utilisateur connecté n'est pas un administrateur
        } elseif ($article->getHidden() && $this->getUser()->getRoles()[0] !== "ROLE_ADMIN") {

            $this->addFlash('error', 'Accès restreint : L\'article que vous essayez de consulter à été caché par un administrateur.');

            return $this->redirectToRoute('article_list', [
                'slug' => $category->getSlug()
            ]);

        }

        // Formulaire d'un nouveau commentaire
        $newComment = new Comment();
        $form = $this->createFormBuilder($newComment)
            ->add('content', TextareaType::class, array('label' => false))
            ->getForm();

        // Vérification si l'utilisateur connecté est (au moins) un adhérent
        if($this->isGranted('ROLE_ADHERENT')) {

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {

                $em = $this->getDoctrine()->getManager();

                /** @var $user User **/
                $user = $this->getUser();
                $newComment->setAuthor($user);
                $newComment->setCreatedAt();
                $newComment->setUpdatedAt();
                $newComment->setArticle($article);

                $em->persist($newComment);

                $em->flush();

                // Message flash
                $this->addFlash('success', 'Votre commentaire a été posté avec succès !');

                // Redirection sur la page de gestionnaire de catégories
                return $this->redirectToRoute('article_view', [
                    'slug' => $article->getSlug(),
                    'slug_category' => $category->getSlug()
                ]);
            }

        }

        $medias = $article->getMedia();

        return $this->render('article/viewArticle.html.twig', [
            'article' => $article,
            'slug_category' => $category->getSlug(),
            'comments' => $article->getComment(),
            'form' => $form->createView(),
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
