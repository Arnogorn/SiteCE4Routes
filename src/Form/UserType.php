<?php

namespace App\Form;

use App\Entity\Famille;
use App\Entity\Niveau;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('nom')
            ->add('prenom')
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
            ])
            ->add('adresse')
            ->add('telephone')
            ->add('photo', FileType::class, [
                'label' => 'Photo : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image(
                        maxSize: '5M',
                        maxSizeMessage: "Format trop volumineux !",
                        mimeTypesMessage: "Format non valide !"
                    )
                ]
            ])
            ->add('telPersContact')
            ->add('allergies')
            ->add('medecinTraitant')
            ->add('telMedecin')
            ->add('infos', TextareaType::class, [
                'label' => "Informations",
                'required' => false,
            ])
            ->add('niveau', EntityType::class, [
                'class' => Niveau::class,
                'label' => 'Veuillez sélectionner votre niveau',
                'choice_label' => 'libelle',
            ])
            ->add('NoLicence')
            ->add('actif', CheckboxType::class, [
                'label' => 'Actif',
            ])
            ->add('new_password', PasswordType::class, [
                'label' => 'Nouveau mot de passe :',
                'required' => false,
                'mapped' => false, // Ne pas mapper ce champ à l'entité
                'attr' => ['placeholder' => 'Uniquement si vous désirez modifier le mot de passe'],
            ])
            ->add('confirm_password', PasswordType::class, [
                'label' => 'Confirmer le mot de passe :',
                'required' => false,
                'mapped' => false, // Ne pas mapper ce champ à l'entité
                'attr' => ['placeholder' => 'Confirmer le nouveau mot de passe'],
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
