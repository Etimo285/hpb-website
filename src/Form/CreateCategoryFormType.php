<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCategoryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $icons = [
            '- - Aucune - -' => '', 'Journal' => 'newspaper', 'Ballon' => 'futbol',
            'Fauteuil roulant' => 'wheelchair', 'Point d\'exclamation' => 'exclamation-triangle',
            'Ajustement' => 'adjust', 'Vision' => 'eye',
            'Vision restreinte' => 'eye-slash', 'Vision réduite' => 'low-vision',
            'Connexion' => 'sign-in', 'Déconnexion' => 'sign-out',
            'Liste' => 'list', 'Bouquin' => 'book',
            'Discussion' => 'comment', 'Validation' => 'check', 'Chapeau de dîplomé' => 'graduation-cap',
            'Livre d\'adresses' => 'address-book', 'Carte d\'identité' => 'address-card',
            'Symbole +' => 'plus-square', 'Porte-documents' => 'briefcase',
            'Calendrier 1' => 'calendar', 'Calendrier 2' => 'calendar-alt',
            'Jeu vidéo' => 'gamepad', 'Trophée' => 'trophy',
            'Accès universel' => 'universal-access', 'Sourdité' => 'deaf'
        ];

        // Champ de nom pour la catégorie que l'admin souhaite créer
        $builder
            ->add('name', TextType::class)
            ->add('icon', ChoiceType::class, [
                'choices' => $icons,
                'label' => false,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
