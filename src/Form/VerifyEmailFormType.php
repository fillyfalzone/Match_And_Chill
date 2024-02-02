<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class VerifyEmailFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email address',
                'constraints' => [
                    new Email([
                        'message' => 'Please enter a valid email address.',
                    ]),
                ],
                'attr' => [
                    'autofocus' => true,
                    'placeholder' => 'Enter your email address',
                    'required' => 'required',
                    'class' => 'form-control mt-2',
                ],
            ]);
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
