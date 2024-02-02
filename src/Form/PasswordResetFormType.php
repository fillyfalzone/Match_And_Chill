<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class PasswordResetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Activation de la protection CSRF
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // Génération d'un identifiant unique pour le token lié à un type de formulire spécifique
            'csrf_token_id' => 'registration_item',
        ]);
    }
}
