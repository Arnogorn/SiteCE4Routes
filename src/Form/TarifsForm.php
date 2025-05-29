<?php

namespace App\Form;

use App\Entity\Tarifs;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TarifsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Forfaits' => 'forfait',
                    'À la carte' => 'a_la_carte',
                    'Balades' => 'balades',
                    'Licences' => 'licences',
                    'Adhésions' => 'adhesions',
                ],
                'attr' => ['class' => 'form-select'],
                'help' => 'Catégorie principale du tarif'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Description détaillée du tarif...',
                    'rows' => 3,
                    'class' => 'form-control'
                ],
                'help' => 'Description affichée sous le nom'
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'attr' => [
                    'class' => 'form-control',
                    'step' => '0.01'
                ],
                'help' => 'Prix en euros (utilisez 0 pour "gratuit")'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tarifs::class,
        ]);
    }
}
