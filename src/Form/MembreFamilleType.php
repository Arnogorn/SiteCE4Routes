<?php

namespace App\Form;

use App\Entity\Famille;
use App\Entity\MembreFamille;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
            ->add('NoLicence')
            ->add('medecinTraitant')
            ->add('telMedecin')
            ->add('droitImage', CheckboxType::class, [
                'label' => 'J\'autorise la prise de photo et l\'éventuelle diffusion de son image?',
                'label_attr' => [
                    'style' => 'color: #333 !important'
                ]
            ])
            ->add('niveau', EntityType::class, [
                'class' => \App\Entity\Niveau::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Sélectionnez un niveau',
            ])
//            ->add('famille', EntityType::class, [
//                'class' => Famille::class,
//                'choice_label' => 'id',
//            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MembreFamille::class,
        ]);
    }
}
