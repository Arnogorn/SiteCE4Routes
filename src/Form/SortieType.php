<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\MembreFamille;
use App\Entity\Moniteur;
use App\Entity\Niveau;
use App\Entity\Sortie;
use App\Entity\TypeSortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\ORM\EntityManagerInterface;

class SortieType extends AbstractType
{
    private Security $security;
    private EntityManagerInterface $em;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->security = $security;
        $this->em       = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'class' => 'form-control-modern',
                    'placeholder' => 'Ex: Randonnée découverte en forêt'
                ],
                'required' => true,
            ])
            ->add('typeSortie', EntityType::class, [
                'class' => TypeSortie::class,
                'choice_label' => 'libelle',
                'label' => 'Type de sortie',
                'attr' => [
                    'class' => 'form-control-modern form-select-modern'
                ],
                'required' => true,
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de la sortie',
                'attr' => [
                    'class' => 'form-control-modern'
                ],
                'required' => true,
            ])
            ->add('duree', IntegerType::class, [
                'label' => 'Durée de la sortie (en minutes)',
                'attr' => [
                    'class' => 'form-control-modern d-none',
                    'min' => 30,
                    'max' => 480,
                    'step' => 15,
                    'id' => 'duree-input'
                ],
                'required' => true,
                'data' => 60,
            ])
            ->add('nbInscriptionMax', IntegerType::class, [
                'label' => 'Nombre d\'inscriptions maximum',
                'attr' => [
                    'class' => 'form-control-modern',
                    'min' => 1,
                    'max' => 50
                ],
                'required' => true,
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr' => [
                    'class' => 'form-control-modern',
                    'min' => 0,
                    'step' => 0.01
                ],
                'required' => true,
            ])
            ->add('infos', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'attr' => [
                    'class' => 'form-control-modern',
                    'rows' => 5,
                    'placeholder' => 'Décrivez la sortie, les équipements nécessaires, le point de rendez-vous...'
                ],
                'required' => false,
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier la sortie',
                'attr' => [
                    'class' => 'form-check-input',
                    'role' => 'switch'
                ],
                'required' => false,
            ])
            ->add('moniteur', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => function (Moniteur $moniteur) {
                    return $moniteur->getUser()->getNom() . ' ' . $moniteur->getUser()->getPrenom();
                },
                'label' => 'Moniteur',
                'attr' => [
                    'class' => 'form-control-modern form-select-modern'
                ],
                'required' => false,
                'placeholder' => 'Aucun moniteur',
            ])
            ->add('niveauxAdmis', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Niveau(x) admis pour la sortie',
                'attr' => [
                    'class' => 'checkbox-group-container',
                ],
                'label_attr' => [
                    'class' => 'checkbox-group-label',
                ],
                'choice_attr' => function() {
                    return ['class' => 'form-check-input'];
                },
            ]);

        // Séparation des champs pour Users et MembreFamille
        $builder->add('participants', EntityType::class, [
            'class' => User::class,
            'choices' => $this->em->getRepository(User::class)->findAll(),
            'multiple' => true,
            'expanded' => true,
            'choice_label' => function (User $user) {
                return $user->getPrenom() . ' ' . $user->getNom();
            },
            'label' => 'Utilisateurs inscrits',
            'attr' => [
                'class' => 'participants-list-container',
            ],
            'choice_attr' => function() {
                return ['class' => 'form-check-input'];
            },
            'required' => false,
        ]);

        // Récupération des membres de famille de l'utilisateur connecté
        /** @var \App\Entity\User|null $user */
        $user = $this->security->getUser();
        $membresFamille = [];

        if ($user instanceof User && null !== $user->getFamille()) {
            $membresFamille = $user->getFamille()->getMembre()->toArray();
        }

        // Si l'utilisateur est admin, récupérer tous les membres de famille
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
            $membresFamille = $this->em->getRepository(MembreFamille::class)->findAll();
        }

        $builder->add('membresFamilleInscrits', EntityType::class, [
            'class' => MembreFamille::class,
            'choices' => $membresFamille,
            'multiple' => true,
            'expanded' => true,
            'choice_label' => function (MembreFamille $membre) {
                return $membre->getPrenom() . ' ' . $membre->getNom();
            },
            'label' => 'Membres de famille',
            'attr' => ['class' => 'participants-list-container'],
            'choice_attr' => function() {
                return ['class' => 'form-check-input'];
            },
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}