<?php

namespace App\Form;

use App\Entity\Cheval;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChevalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('nomPere')
            ->add('nomMere')
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
                'choice_label' => 'id',
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
