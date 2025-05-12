<?php

namespace App\Form;

use App\Entity\Calendrier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CalendrierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Créer un tableau pour les heures avec uniquement des créneaux à XX:30
        $heures = [];
        for ($h = 8; $h <= 20; $h++) {
            $heures["$h:30"] = "$h:30";
        }

        // Créer un tableau similaire pour les heures de fin
        $heuresFin = [];
        for ($h = 9; $h <= 21; $h++) {
            $heuresFin["$h:30"] = "$h:30";
        }

        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['rows' => 3]
            ])
            ->add('jour', ChoiceType::class, [
                'label' => 'Jour',
                'choices' => [
                    'Lundi' => 'Lundi',
                    'Mardi' => 'Mardi',
                    'Mercredi' => 'Mercredi',
                    'Jeudi' => 'Jeudi',
                    'Vendredi' => 'Vendredi',
                    'Samedi' => 'Samedi',
                    'Dimanche' => 'Dimanche',
                ]
            ])
            ->add('heureDebut', ChoiceType::class, [
                'label' => 'Heure de début',
                'choices' => $heures,
            ])
            ->add('heureFin', ChoiceType::class, [
                'label' => 'Heure de fin',
                'choices' => $heuresFin,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Calendrier::class,
        ]);
    }
}