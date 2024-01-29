<?php

namespace App\Form;

use DateTime;
use App\Entity\User;
use App\Entity\Event;
use App\Entity\CategoryEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
                'label' => 'Intitulé',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'id' => 'name',
                    'placeholder' => "Nom de l'événement"
                ],
                'required' => true,
        ])
        // Ajoutez la logique pour les options des catégories
        ->add('category', EntityType::class, [
            'label' => 'Catégorie',
            'attr' => [
                'class' => 'form-select mb-3',
                'id' => 'category',
            ],
            'class' => CategoryEvent::class,
            'choice_label' => 'name',
            'required' => true,
        ])
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'description',
                'rows' => '3',
            ],
            'required' => true,
        ])
        ->add('numberOfPlaces', NumberType::class, [
            'label' => 'Nombre de places',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'numberOfPlaces',
                'min' => '0',
                'step' => '1',
            ],
            'required' => true,
        ])
        ->add('startDate', DateTimeType::class, [
            'label' => 'Date de début',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'startDate',
            ],
            'required' => true,
        ])
        ->add('endDate', DateTimeType::class, [
            'label' => 'Date de fin',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'endDate',
            ],
            'required' => true,
        ])
        ->add('adress', TextType::class, [
            'label' => 'Adresse',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'adress',
            ],
            'required' => true,
        ])
        ->add('zipCode', TextType::class, [
            'label' => 'Code postal',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'zipCode',
            ],
            'required' => true,
        ])
        ->add('city', TextType::class, [
            'label' => 'Ville',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'city',
            ],
            'required' => true,
        ])
        // Ajoutez la logique pour les options des matchs
        ->add('matchId', ChoiceType::class, [
            'label' => 'Match',
            'attr' => [
                'class' => 'form-select',
                'id' => 'matchId',
            ],
            'choices' => [
                 'match1' => '25365',
                 'match2' => '20005',
            ],
            'required' => true,
        ])
        ->add('country', TextType::class, [
            'label' => 'Pays',
            'attr' => [
                'class' => 'form-control mb-3',
                'id' => 'country',
            ],
            'required' => true,
        ])
        ->add('honeyPot', HiddenType::class, [
            'attr' => [
                'id' => 'honeyPot',
            ],
           'mapped' => false,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
