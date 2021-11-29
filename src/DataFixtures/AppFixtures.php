<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\Event;
use App\Entity\Alert;
use App\Entity\User;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {

        // Instanciation du Faker en français
        $faker = Faker\Factory::create('fr_FR');

        // Attribution d'un nombre aux boucles des entités
        $articleCount = 40;
        $eventCount = 5;
        $alertCount = 10;
        $adminCount = 10;
        $adherentCount = 25;
        $userBasicCount = 10;
        $commentCount = rand(0, 10);

        // --USER ADMIN-- //

        // Instanciation du tableau des admins
        $admins = [];

        for ($i = 0; $i < $adminCount; $i++) {
            $newAdmin = new User();

            // Hydratation du compte admin
            $newAdmin
                ->setRoles(['ROLE_ADMIN'])
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail('admin'.$i.'@a.fr')
                ->setPseudo($faker->userName)
                ->setIsVerified(true)
                ->setIsMember(false)
                ->setPassword($this->encoder->hashPassword($newAdmin, 'Password1*'));
            $newAdmin->setCreatedAt();
            $newAdmin->setUpdatedAt();


            // Persistance de l'admin
            $manager->persist($newAdmin);

            $admins[] = $newAdmin;
        }

        // --USER ADHERENT--//

        for($i = 1; $i < $adherentCount; $i++) {

            // Création d'un nouvel utilisateur
            $newAdherent = new User();

            // Hydratation du nouvel utilisateur
            $newAdherent
                ->setRoles(['ROLE_ADHERENT'])
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setPhone($faker->e164PhoneNumber)
                ->setPseudo($faker->userName)
                ->setIsVerified(true)
                ->setIsMember($faker->randomElement( [true, false] ))
                ->setPassword( $this->encoder->hashPassword($newAdherent, 'Password1*') );
            $newAdherent->setCreatedAt();
            $newAdherent->setUpdatedAt();


            // Enregistrement du nouvel utilisateur auprès de Doctrine
            $manager->persist($newAdherent);

        }

        // --USER (DE BASE)--//

        $users = [];

        for($i = 1; $i < $userBasicCount; $i++) {

            // Création d'un nouvel utilisateur
            $newUser = new User();

            // Hydratation du nouvel utilisateur
            $newUser
                ->setRoles(['ROLE_USER'])
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setPhone($faker->e164PhoneNumber)
                ->setPseudo($faker->userName)
                ->setIsVerified(false)
                ->setIsMember(false)
                ->setPassword( $this->encoder->hashPassword($newUser, 'Password1*') );
            $newUser->setCreatedAt();
            $newUser->setUpdatedAt();


            // Enregistrement du nouvel utilisateur auprès de Doctrine
            $manager->persist($newUser);

            $users[] = $newUser;
        }


        // --CATEGORY-- //

        $categoryList = ['News', 'Blog', 'Documentation',
            'Conseil d\'administration', 'Grande Dépendance',
            'Vie scolaire', 'Vie professionnelle',
            'Culture, sport et loisirs', 'Accessibilité',
            'Handi-Acceuillance', 'Humour', 'Nos Amis'];

        $categories = [];
        $icon = '';

        // Boucle qui récupère chaque element du tableau ci-dessus, pour hydrater la BDD
        foreach ($categoryList as $categoryName) {

            $newCategory = new Category();

            // Hydratation du champ name
            $newCategory->setName($categoryName);

            switch ($categoryName) {

                case 'News': $icon = 'newspaper';
                    break;
                case 'Blog': $icon = 'comment';
                    break;
                case 'Documentation': $icon = 'book';
                    break;
                case 'Conseil d\'administration': $icon = 'users';
                    break;
                case 'Grande Dépendance': $icon = 'wheelchair';
                    break;
                case 'Vie scolaire': $icon = 'graduation-cap';
                    break;
                case 'Vie professionnelle': $icon = 'briefcase';
                    break;
                case 'Culture, sport et loisirs': $icon = 'futbol';
                    break;
                case 'Accessibilité': $icon = 'low-vision';
                    break;
                case 'Handi-Acceuillance': $icon = 'door-open';
                    break;
                case 'Humour': $icon = 'laugh-beam';
                    break;
                case 'Nos Amis': $icon = 'universal-access';
                    break;

            }

            $newCategory->setIcon($icon);

            $manager->persist($newCategory);

            $categories[] = $newCategory;
        }


        // --ARTICLES-- //

        for($i = 1; $i < $articleCount; $i++) {

            // Création d'un nouvel article
            $newArticle = new Article();

            // Hydratation du nouvel article
            $newArticle
                ->setTitle($faker->sentence(10))
                ->setContent($faker->paragraph(10))
                ->setAuthor($faker->randomElement($admins))
                ->setHidden(false)
                ->setCategory($faker->randomElement($categories));
            $newArticle->setCreatedAt();
            $newArticle->setUpdatedAt();


            // --COMMENT-- //

            for($j = 1; $j < $commentCount; $j++) {

                // Création d'un nouvel article
                $newComment = new Comment();

                // Hydratation du nouvel article
                $newComment
                    ->setContent($faker->paragraph(10))
                    ->setAuthor($faker->randomElement($users))
                    ->setArticle($newArticle);
                $newComment->setCreatedAt();
                $newComment->setUpdatedAt();

                // Enregistrement du nouvel article auprès de Doctrine
                $manager->persist($newComment);

            }

            // Enregistrement du nouvel article auprès de Doctrine
            $manager->persist($newArticle);

        }

        // --EVENT-- //

        for($i = 1; $i < $eventCount; $i++) {

            // Création d'un nouvel évènement
            $newEvent = new Event();

            // Hydratation du nouvel évènement
            $newEvent
                ->setTitle($faker->sentence(10))
                ->setContent($faker->paragraph(10))
                ->setCity($faker->city)
                ->setPostcode($faker->postcode)
                ->setAddress1($faker->address)
                ->setAddress2('Batîment '. $faker->randomDigitNot(0))
                ->setAuthor($newAdmin); // 'HPB' sera l'auteur;
            $newEvent->setCreatedAt();
            $newEvent->setUpdatedAt();


            // Enregistrement du nouvel évènement auprès de Doctrine
            $manager->persist($newEvent);

        }

        // --ALERT-- //

        for($i = 1; $i < $alertCount; $i++) {

            // Création d'une nouvelle alerte
            $newAlert = new Alert();

            // Hydratation de la nouvelle alerte
            $newAlert
                ->setObject($faker->sentence(10))
                ->setContent($faker->paragraph(10))
                ->setCity($faker->city)
                ->setPostcode($faker->postcode)
                ->setAddress1($faker->address)
                ->setAddress2('Batîment '. $faker->randomDigitNot(0))
                ->setAuthor($newAdmin);// 'HPB' sera l'auteur;
            $newAlert->setCreatedAt();
            $newAlert->setUpdatedAt();


            // Enregistrement de la nouvelle alerte auprès de Doctrine
            $manager->persist($newAlert);

        }


        // Sauvegarde des nouvelles entités dans la BDD, via le manager général des entités
        $manager->flush();
    }
}
