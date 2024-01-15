<?php

namespace App\Form;

use App\Entity\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
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
        ->add('pseudo', TextType::class)
        ->add('email', EmailType::class)
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
            'constraints' => [
                new Regex([
                    'pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*]).{8,}$/',
                    'message' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre et avoir une longueur d\'au moins 12 caractères.'])
                ],
        ])
        
        ->add('avatar', FileType::class, [
            'mapped' => false,
            'required' => false,
            'label' => 'Avatar (taille 5mo max)',
            'attr' => [
                'accept' => 'image/jpeg, image/png, image/gif',
                'class' => 'form-control my-2'
            ],
            'constraints' => [
                new File([
                    'maxSize' => '5M',
                    'maxSizeMessage' => 'La taille de l\'image ne doit pas dépasser 5 Mo.',
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                    'mimeTypesMessage' => 'Veuillez télécharger une image de type JPG, PNG ou GIF.',
                ]),
            ],
        ])
       
        ->add('agreeTerms', CheckboxType::class, [
            'mapped' => false,
            'label' => 'J\'accepte les conditions et termes',
            'constraints' => [
                new IsTrue([
                    'message' => 'Acceptez les termes d\'utilisations.',
                ]),
            ],
        ])
        ->add('honeyPot', HiddenType::class, [
            'label' => false,
            'mapped' => false,
            'required' => false
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
