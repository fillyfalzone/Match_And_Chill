<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('pseudo', TextType::class, [
            'constraints' => [
                // Regex pour valider le pseudo
                // new Regex([
                //     'pattern' => '^[a-zA-Z][a-zA-Z0-9._-]{2,15}$',
                //     'message' => 'Le pseudo doit contenir entre 3 et 15 caractères, commencer par une lettre et ne doit pas contenir d\'espace.',
                // ])
            ],
        ])
        ->add('email', EmailType::class, [
            'constraints' => [
                //class pour valider l'email
                new Email([
                    'message' => 'Veuillez saisir une adresse email valide.'
                ])
            ],
        ])
        ->add('plainPassword', RepeatedType::class, [
            'mapped' => false,
            'type' => PasswordType::class,
            'invalid_message' => 'Les mots de passe doivent être similaire.',
            'options' => [
                'attr' => [
                    'class' => 'password-field form-control my-2',
                    ]
                ],
            'required' => true,
            'first_options'  => ['label' => 'Mot de passe'],
            'second_options' => ['label' => 'Confirmation du mot de passe'],
            // 'constraints' => [
            //     // Regex pour valider le mot de passe
            //     new Regex([
            //         'pattern' => '^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()])[A-Za-z\d!@#$%^&*()]{14,}$',
            //         'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre, un symbone et avoir une longueur d\'au moins 14 caractères.'
            //     ])
            // ],
        ])
        
        ->add('avatar', FileType::class, [
            'mapped' => false,
            'required' => false,
            'label' => 'Avatar (taille 5mo max), fichier de type jpg, png ou gif',
            'attr' => [
                'accept' => 'image/jpeg, image/png, image/gif',
                'class' => 'form-control my-2'
            ],
            'constraints' => [
                // Contraintes sur l'avatar
                new File([
                    'maxSize' => '5M',
                    'maxSizeMessage' => 'La taille de l\'image ne doit pas dépasser 5 Mo.',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image de type JPG, PNG ou GIF.',
                ]),
            ],
        ])
       
        ->add('agreeTerms', CheckboxType::class, [
            'required' => true,
            'mapped' => false,
            'label' => 'J\'accepte les conditions et termes',
            'attr' => [
                'required' => true,
            ],
            'constraints' => [
                new IsTrue([
                    'message' => 'Acceptez les termes d\'utilisations.',
                ]),
            ],
        ])
        ->add('age', HiddenType::class, [
            'label' => false,
            'mapped' => false,
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            // Activation de la protection CSRF
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // Génération d'un identifiant unique pour le token lié à un type de formulire spécifique
            'csrf_token_id' => 'registration_item',
        ]);
    }
}
