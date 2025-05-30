<?php


namespace App\Form;

use App\Entity\Cheval;
use App\Entity\Sexe;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Validator\Constraints\Image;

class ChevalType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom * : ',
                'attr' => [
                    'placeholder' => 'Entrez le nom du cheval',
                    'class' => 'form-control'
                    ],
                'label_attr' => [
                    'class' => 'form-label'
                ]
            ])
            ->add('nomPere', TextType::class, [
                'label' => 'Nom du Père : ',
                'required' => false,
            ])
            ->add('nomMere', TextType::class, [
                'label' => 'Nom de la Mère : ',
                'required' => false,
            ])
            ->add('sexe', EntityType::class, [
                'class' => Sexe::class,
                'choice_label' => 'libelle',
                'placeholder' => 'Choisir le sexe',
                'label' => 'Sexe * : ',
            ])
            ->add('dateNaissance', null, [
                'widget' => 'single_text',
                'label' => 'Date de naissance :',
                'required' => false,
            ])
            ->add('lieuNaissance', TextType::class, [
                'label' => 'Lieu de naissance :',
                'required' => false,
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Seuls les formats JPG, PNG et GIF sont acceptés.',
                        'maxSizeMessage' => 'La taille du fichier ne doit pas dépasser 5Mo.',

                    ])
                ]])
            ->add('infos', TextareaType::class, [
                'label' => 'Informations : ',
                'required' => false,
            ]);

        // Ajout des champs réservés aux administrateurs
        if (in_array('ROLE_ADMIN', $this->security->getUser()->getRoles())) {
            $builder
                ->add('appartientEcurie', CheckboxType::class, [
                    'label' => 'Cheval de club : ',
                    'required' => false,
                ])
                ->add('aVendre', CheckboxType::class, [
                    'label' => 'Le cheval est-il à vendre? ',
                    'required' => false,
                ])
                ->add('vendu', CheckboxType::class, [
                    'label' => 'Le cheval a t\'il été vendu? ',
                    'required' => false,
                ])
                ->add('user', EntityType::class, [

                    'class' => User::class,
                    'label' => 'Propriétaire : ',
                    'choice_label' => 'nom',
                    'required' => false,
                    'placeholder' => 'Aucun propriétaire',
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Cheval::class,
            'is_admin' => false,
        ]);
    }
}
