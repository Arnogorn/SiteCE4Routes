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

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'required' => false,
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Rechercher par titre']
            ])
            ->add('dateMin', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date minimum',
            ])
            ->add('dateMax', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
                'label' => 'Date maximum',
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'required' => false,
                'placeholder' => 'Tous les niveaux',
                'label' => 'Niveau'
            ])
            ->add('tri', ChoiceType::class, [
                'choices' => [
                    'Date (plus récentes)' => 'desc',
                    'Date (plus anciennes)' => 'asc',
                ],
                'required' => false,
                'placeholder' => 'Trier par',
                'label' => 'Tri'
            ])
            ->add('rechercher', SubmitType::class, [
                'attr' => ['class' => 'btn btn-primary']
            ])
        ;

        // Ajouter le champ état seulement pour les administrateurs
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $builder->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle',
                'required' => false,
                'placeholder' => 'Tous les états',
                'label' => 'État'
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}