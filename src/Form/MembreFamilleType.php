<?php

namespace App\Form;

use App\Entity\Famille;
use App\Entity\MembreFamille;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MembreFamilleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
            ])
            ->add('allergies')
            ->add('medecinTraitant')
            ->add('telMedecin')
            ->add('droitImage')
            ->add('niveau', EntityType::class, [
                'class' => \App\Entity\Niveau::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez un niveau',
            ])
            ->add('famille', EntityType::class, [
                'class' => Famille::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembreFamille::class,
        ]);
    }
}
