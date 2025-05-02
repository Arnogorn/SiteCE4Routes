<?php

namespace App\Form;

use App\Entity\Niveau;
use App\Entity\User;
use App\Repository\NiveauRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')

            ->add('nom', TextType::class, [
                'label' => 'Nom :'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom :'
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse :'
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance :'
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone :'
            ])
            ->add('droitImage', CheckboxType::class, [
                'label' => 'Acceptez vous la prise de photo et l\'éventuelle diffusion de votre image?'

            ])
            ->add('actif', CheckboxType::class, [

            ])
            ->add('niveau', EntityType::class, [
                'label' => 'Niveau :',
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez un niveau',
                // Remove 'mapped' => false to allow automatic mapping
                'query_builder' => function (NiveauRepository $niveauRepository) {
                    return $niveauRepository->createQueryBuilder('n')
                        ->addOrderBy('n.libelle');
                }
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('plainPasswordConfirmation', PasswordType::class, [

                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Confirmer le mot de passe :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer le mot de passe',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
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
