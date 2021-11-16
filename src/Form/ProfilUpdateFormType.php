<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfilUpdateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ Pseudo
            ->add('pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => false,
            ])

            // Champ nom
            ->add('last_name', TextType::class, [
                'label' => 'Nom',
            ])

            // Champ prénom
            ->add('first_name', TextType::class, [
                'label' => 'Prénom',
            ])

            // Champ téléphone
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])

            // Champ email
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
            ])

            // Bouton de validation
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
