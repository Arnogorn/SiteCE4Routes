<?php

namespace App\Form;

use App\Entity\Cheval;
use App\Entity\Sexe;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChevalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom * :',
            ])
            ->add('nomPere')
            ->add('nomMere')
            ->add('sexe', EntityType::class, [
                'class' => Sexe::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisir le sexe',
            ])
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
            ])
            ->add('lieuNaissance')
            ->add('photo')
            ->add('appartientEcurie')
            ->add('infos')
            ->add('aVendre')
            ->add('vendu')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'nom',
                'required' => false,
                'placeholder' => 'Aucun propriétaire',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cheval::class,
        ]);
    }
}
