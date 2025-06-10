<?php

namespace App\Form;

use App\Entity\Famille;
use App\Entity\Niveau;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom')
            ->add('prenom')
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
            ])
            ->add('adresse')
            ->add('telephone')
            ->add('photo', FileType::class, [
                'label' => 'Photo : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image(
                        maxSize: '5M',
                        maxSizeMessage: "Format trop volumineux !",
                        mimeTypesMessage: "Format non valide !"
                    )
                ]
            ])
            ->add('telPersContact')
            ->add('allergies')
            ->add('medecinTraitant')
            ->add('telMedecin')
            ->add('infos', TextareaType::class, [
                'label' => "Informations",
                'required' => false,
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'label' => 'Veuillez sélectionner votre niveau',
                'choice_label' => 'libelle',
            ])
            ->add('NoLicence')
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
            ])
            ->add('droitImage', CheckboxType::class, [
                'label' => 'Acceptez vous la prise de photo et l\'éventuelle diffusion de votre image? *',
                'label_attr' => [
                    'style' => 'color: #333 !important'
                ]
            ])
            ->add('new_Password', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => [
                    'label' => 'Mot de passe * :',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmez le mot de passe * :',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'invalid_message' => 'Les mots de passe doivent correspondre.',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Le mot de passe doit comporter au moins {{ limit }} caractères.',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/^(?=.*[A-Z])(?=.*[\d\W]).{8,}$/',
                        'message' => 'Le mot de passe doit comporter au moins une majuscule et un chiffre ou caractère spécial.',
                    ]),
                ],
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
