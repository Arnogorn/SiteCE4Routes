<?php

namespace App\Form;

use App\Entity\Competition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de debut',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'required' => false,
            ])
            ->add('prixTransport', TextType::class, [
                'label' => 'Prix du transport',
                'required' => false,
            ])
            ->add('prixEpreuve', TextType::class, [
                'label'=> 'Prix d\'une épreuve',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Competition::class,
        ]);
    }
}
