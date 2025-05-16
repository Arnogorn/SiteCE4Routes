<?php

namespace App\Form;

use App\Entity\Niveau;
use App\Entity\User;
use App\Repository\NiveauRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            ->add('email',  EmailType::class, [
                'label' => 'Email * :'])

            ->add('nom', TextType::class, [
                'label' => 'Nom * :'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom * :'
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse * :'
            ])
            ->add('dateNaissance', DateType::class, [
                'label' => 'Date de naissance * :',
                'widget' => 'single_text',
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone * :'
            ])
            ->add('droitImage', CheckboxType::class, [
                'label' => 'Acceptez vous la prise de photo et l\'éventuelle diffusion de votre image? *'

            ])
            ->add('niveau', EntityType::class, [
                'label' => 'Niveau * :',
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez un niveau',
                // Remove 'mapped' => false to allow automatic mapping
                'query_builder' => function (NiveauRepository $niveauRepository) {
                    return $niveauRepository->createQueryBuilder('n')
                        ->where('n.libelle != :libelleAExclure')
                        ->setParameter('libelleAExclure', 'Tous niveaux')
                        ->addOrderBy('n.libelle');
                }
            ])
            ->add('telPersContact', TextType::class, [
                'label' => 'numéro de téléphone de la personne à contacter en cas de besoin :',
                'required' => false,
            ])
            ->add('allergies', TextType::class, [
                'label' => 'Avez vous des allergies, si oui lesquelles? :',
                'required' => false,
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Mot de passe * :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit comporter au minimum {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('plainPasswordConfirmation', PasswordType::class, [

                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'label' => 'Confirmer le mot de passe * :',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez confirmer le mot de passe',
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions d\'utilisation *',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Veuillez accepter les conditions d\'utilisation pour continuer',
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
