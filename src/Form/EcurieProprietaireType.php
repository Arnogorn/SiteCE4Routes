<?php

namespace App\Form;

use App\Entity\EcurieProprietaire;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EcurieProprietaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('prestation', TextType::class, [
                'label' => 'Prestation',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('prix', TextType::class, [
                'label' => 'Prix',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EcurieProprietaire::class,
        ]);
    }
}
