<?php

namespace App\Form;

use App\Entity\Inscription;
use App\Entity\Paiement;
use App\Entity\Sortie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du participant',
            ])
            ->add('sortie', EntityType::class, [
                'class' => Sortie::class,
                'choice_label' => 'nom',
                'label' => 'Sortie',
            ])
            ->add('paiement', EntityType::class, [
                'class' => Paiement::class,
                'choice_label' => 'id',
                'label' => 'Paiement associé',
            ])
            ->add('inscritPar', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email', // ou autre champ utile
                'label' => 'Inscrit par',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inscription::class,
        ]);
    }
}