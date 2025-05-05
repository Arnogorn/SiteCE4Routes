<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Moniteur;
use App\Entity\Niveau;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('dateLimiteInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionMax')
            ->add('prix')
            ->add('infos')
            ->add('isPublished')
            ->add('moniteur', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => 'id',
            ])
            ->add('niveauxAdmis', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
