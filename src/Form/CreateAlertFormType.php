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
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateAlertFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ objet de l'alerte
            ->add('object',TextType::class,[
                'label' => 'Objet de votre alerte',
                'attr' => array(
                    'placeholder' => 'Champs obligatoire'
                ),
            ])

            // Champ contenu de l'alerte
            ->add('content',CKEditorType::class, [
                'label' => 'Décrivez votre alerte',
                'attr' => [
                    // Masque le chargement de CKEditor dans le contenu
                    'class' => 'd-none',
                    ],
                    // Nettoie le code html du contenu du texte (bundle htmlpurifier à installer)
                    'purify_html' => true,
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Merci de renseigner un contenu',
                        ]),
                ]])
            ->add('city',TextType::class, [
                'label' => 'Ville concernée',
                'attr' => array(
                    'placeholder' => 'Champs obligatoire'
                ),
            ])
            ->add('postcode',TextType::class, [
                'label' => 'Code postal',
                'attr' => array(
                    'placeholder' => 'Champs obligatoire'
                ),
            ])
            ->add('address1', TextType::class, [
                'label' => 'Adresse',
                'attr' => array(
                    'placeholder' => 'Champs obligatoire'
                ),
            ])
            ->add('address2', TextType::class, [
                'label' => 'Complément d\'adresse',
                'required' => false,
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
