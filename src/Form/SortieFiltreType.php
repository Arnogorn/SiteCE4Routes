<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Niveau;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class SortieFiltreType extends AbstractType
{
    public function __construct(
        private Security $security
    ) {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('titre', TextType::class, [
                'required' => false,
                'label' => 'Titre contient',
                'attr' => [
                    'class' => 'form-control-modern form-select-modern'
                ],
            ])
            ->add('dateMin', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date à partir de',
                'attr' => [
                    'class' => 'form-control-modern form-select-modern'
                ],
            ])
            ->add('dateMax', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'label' => 'Date jusqu\'à',
                'attr' => [
        'class' => 'form-control-modern form-select-modern'
    ],
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'required' => false,
                'label' => 'Niveau requis',
                'choice_label' => 'libelle',
                'placeholder' => 'Tous les niveaux',
                'attr' => [
                    'class' => 'form-control-modern form-select-modern'
                ],
            ])



            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher'

            ])
        ;
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('etat', EntityType::class, [
                'class' => Etat::class,
                'required' => false,
                'label' => 'État',
                'choice_label' => 'libelle',
                'placeholder' => 'Tous les états',
                'attr' => [
                    'class' => 'form-control-modern form-select-modern'
                ],

            ]);
    }}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}