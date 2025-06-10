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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

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
                'label' => 'Acceptez vous la prise de photo et l\'éventuelle diffusion de votre image? *',
                'label_attr' => [
                    'style' => 'color: #333 !important'
                ]
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
            ->add('NoLicence', TextType::class, [
                'label' => 'Numéro de licence FFE',
                'required' => false,
            ])
//             Combine plainPassword et plainPasswordConfirmation on les appel désormais dans le template avec :
//            {{ form_row(form.plainPassword.first) }}
//            {{ form_row(form.plainPassword.second) }}
            ->add('plainPassword', RepeatedType::class, [
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
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'J\'accepte les termes et conditions d\'utilisation *',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Veuillez accepter les conditions d\'utilisation pour continuer',
                    ]),
                ],
                'label_attr' => [
                    'style' => 'color: #333 !important'
                ]
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
