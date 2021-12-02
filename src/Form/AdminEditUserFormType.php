<?php

namespace App\Form;

use App\Entity\User;
use \Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEditUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Adhérent' => 'ROLE_ADHERENT',
                    'Utilisateur' => 'ROLE_USER',
                ]
            ])
            ->add('functionTitle', TextType::class)
            ->add('isVerified')
            ->add('isMember')
        ;

        // Convertissage du tableau rôles sous forme de chaîne de caractères
        $builder->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                function ($rolesArray) {
                    // transforme le tableau en 'string'
                    return count($rolesArray) ? $rolesArray[0]: null;
                },
                function ($rolesString) {
                    // retourne le 'string' sous forme de tableau
                    return [$rolesString];
                }
            ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
