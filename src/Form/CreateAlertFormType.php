<?php

namespace App\Form;

use App\Entity\Alert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateAlertFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ objet de l'alerte
            ->add('object',TextType::class,[
                'label' => 'Objet de votre alerte',
            ])

            // Champ contenu de l'alerte
            ->add('content',CKEditorType::class, [
                'label' => 'Décrivez votre alerte',
                'attr' => [
                    // Masque le chargement de CKEditor dans le contenu
                    'class' => 'd-none',
                    ],
                    // Enlève les balises html du contenu du texte
                    // TODO: purify_html n'existe pas CKEditor ????
                    //'purify_html' => true,
            ])
            ->add('city',TextType::class, [
                'label' => 'Ville concernée',
                'attr' => array(
                    'placeholder' => 'Champs obligatoire'
                ),
            ])
            ->add('postcode',TextType::class, [
                'label' => 'Code postal',
                'required' => false,
            ])
            ->add('address1', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
            ])
            ->add('address2', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false,
            ])

            // Bouton de validation
            ->add('save', SubmitType::class, [
                'label' => 'Publier',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alert::class,
        ]);
    }
}
