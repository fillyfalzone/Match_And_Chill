<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'required'=> true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre pseudo'
                ]
            ])
            ->add('email', EmailType::class, [
                'required'=> true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre email'
                ],
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email {{ value }} n\'est pas valide.',
                    ]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Votre message',
                    // Vous pouvez également utiliser l'attribut HTML5 'maxlength' pour une validation côté client
                    'maxlength' => 2000
                ],
                'constraints' => [
                    new Length([
                        'max' => 2000, // Limite maximale de 500 caractères
                        'maxMessage' => 'Votre message ne doit pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('confirmation', CheckboxType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-check-input',
                    'style' => 'border: 1px solid blue; height:10px; width: 10px; padding: 10px;',
                ]
            ])
            ->add('pays', HiddenType::class);
            
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
