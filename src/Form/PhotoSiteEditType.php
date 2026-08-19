<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;

class PhotoSiteEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Légende',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 255, 'maxMessage' => 'La légende ne peut dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Texte associé',
                'required' => false,
                'constraints' => [
                    new Length(['max' => 500, 'maxMessage' => 'Le texte ne peut dépasser {{ limit }} caractères.']),
                ],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Nouvelle photo (optionnel)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
                        'mimeTypesMessage' => 'Seuls les formats JPG, PNG et WEBP sont acceptés.',
                        'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5Mo.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'photo_site_edit',
        ]);
    }
}
