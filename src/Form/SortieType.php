<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Moniteur;
use App\Entity\Niveau;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

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
            ->add('dateLimiteInscription', DateTimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionMax')
            ->add('prix')
            ->add('infos')
            ->add('isPublished')
            ->add('moniteur', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => function (Moniteur $moniteur) {
                    return $moniteur->getUser()->getNom() . ' ' . $moniteur->getUser()->getPrenom();
                }
            ])
            ->add('niveauxAdmis', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'multiple' => true,
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle',
            ])
            ->add('participants', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'expanded' => true, // ou false pour une liste déroulante
                'choice_label' => 'nom', // ou 'email', ou une méthode comme getFullName()
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
