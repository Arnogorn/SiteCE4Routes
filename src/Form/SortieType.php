<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Moniteur;
use App\Entity\Niveau;
use App\Entity\Sortie;
use App\Entity\TypeSortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('typeSortie', EntityType::class, [
                'class' => TypeSortie::class,
                'choice_label' => 'libelle',
                'label' => 'Type de sortie : ',
            ])
            ->add('date', null, [
                'widget' => 'single_text',
                'label' => 'Date de la sortie',
            ])
            ->add('duree', TextType::class, [
                'label' => 'Durée de la sortie (en minutes)',
            ])

            ->add('nbInscriptionMax', TextType::class, [
                'label' => 'Nombre d\'inscriptions maximum',
            ])
            ->add('prix', TextType::class, [
                'label' => 'Prix',
            ])
            ->add('infos', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'required' => false,
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier la sortie',
                'required' => false,
            ])
            ->add('moniteur', EntityType::class, [
                'class' => Moniteur::class,
                'choice_label' => function (Moniteur $moniteur) {
                    return $moniteur->getUser()->getNom() . ' ' . $moniteur->getUser()->getPrenom();
                },
                'label' => 'Moniteur',
            ])
            ->add('niveauxAdmis', EntityType::class, [
                'class' => Niveau::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'label' => 'Niveau(x) admis pour la sortie ',
            ])
            ->add('participants', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'expanded' => true, // ou false pour une liste déroulante
                'choice_label' => 'nom', // ou 'email', ou une méthode comme getFullName()
                'label' => 'Rajouter des participants',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
