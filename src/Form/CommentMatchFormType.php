<?php

namespace App\Form;

use App\Entity\CommentMatch;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentMatchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   // Création du formulaire pour ajouter un commentaire
        $builder
        // différentes classes pour valider le formulaire
            ->add('text', TextareaType::class, [
                'contraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un commentaire',
                    ]),
                    new Length([
                        'min' => 1,
                        'minMessage' => 'Votre commentaire doit contenir au moins 1 caractère',
                        // max length allowed by Symfony for security reasons
                        'max' => 2500,
                    ]),
                ],
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                ]
            ])
            ->add('creationDate', HiddenType::class)
            ->add('matchID', HiddenType::class)
            ->add('userID', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CommentMatch::class,
            // Activation de la protection CSRF
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // Génération d'un identifiant unique pour le token lié à un type de formulire spécifique
            'csrf_token_id' => 'registration_item',
        ]);
    }
}
