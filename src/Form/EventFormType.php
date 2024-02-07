<?php

namespace App\Form;

use App\Entity\Event;
use App\Entity\CategoryEvent;
use App\HttpClient\OpenLigaDBClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventFormType extends AbstractType
{   
    private OpenLigaDBClient $openLigaDBClient;

    public function __construct(OpenLigaDBClient $openLigaDBClient)
    {
        $this->openLigaDBClient = $openLigaDBClient;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {   
        
        // Récupération des équipes et des matchs
        $table = $this->openLigaDBClient->getTable();
        $teams = [];
        foreach ($table as $team) {
            $teams[$team['shortName']] = $team['teamInfoId'];
        }
      
        // Ajoute un écouteur d'événement PRE_SUBMIT au constructeur de formulaire.
        // Cet écouteur sera déclenché juste avant que les données soumises soient
        // appliquées au formulaire. C'est utile pour modifier dynamiquement le formulaire
        // en fonction des données soumises.
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            // Récupère les données soumises au formulaire.
            $data = $event->getData();
            // Récupère l'objet formulaire actuel.
            $form = $event->getForm();

            // Vérifie si l'ID de l'équipe ('teamId') est fourni dans les données soumises.
            if (!empty($data['teamId'])) {
                // Stocke l'ID de l'équipe soumis.
                $teamId = $data['teamId'];
                // Utilise le client OpenLigaDB pour récupérer les matchs associés à l'ID de l'équipe.
                $matches = $this->openLigaDBClient->getMatchByTeamId($teamId);
                // Prépare un tableau pour stocker les identifiants des matchs.
                $matchesId = [];
                // Boucle à travers chaque match récupéré.
                foreach ($matches as $match) {
                    // Associe l'ID du match à lui-même dans le tableau $matchesId.
                    // Ceci est fait pour préparer les options du champ de sélection 'matchId'
                    // où la clé et la valeur sont les mêmes (l'ID du match).
                    $matchesId[$match['matchID']] = $match['matchID'];
                }
                
                // Vérifie si le champ 'matchId' existe déjà dans le formulaire.
                if ($form->has('matchId')) {
                    // Si oui, retire ce champ pour le réajouter avec les nouvelles options.
                    $form->remove('matchId');
                }
                
                // Réajoute le champ 'matchId' au formulaire avec les nouvelles options.
                // `array_flip` est utilisé pour que les valeurs des matchs deviennent les clés,
                // et les clés (qui sont les IDs des matchs) deviennent les valeurs dans les options du champ,
                // ceci est une exigence pour le type de champ ChoiceType dans Symfony.
                $form->add('matchId', ChoiceType::class, [
                    'choices' => array_flip($matchesId),
                ]);
            }
        })
        // Ajute de l'input pour le nom de l'événement
        ->add('name', TextType::class, [ // Utilisation de la classe TextType pour créer un champ de type texte
                'label' => 'Intitulé',
                'attr' => [
                    'class' => 'form-control mb-3',
                    'id' => 'name',
                    'placeholder' => "Nom de l'événement"
                ],
                'required' => true,
        ])
        // Ajoutez de l'input pour la catégorie de l'événement
        ->add('category', EntityType::class, [ // Utilisation de la classe EntityType pour créer un champ de type liste déroulante
            'placeholder' => 'Choisissez une catégorie',
            'attr' => [
                'class' => 'form-select mb-3',
                'id' => 'category',
            ],
            'class' => CategoryEvent::class, // Utilisation de la classe CategoryEvent pour créer un champ de type liste déroulante
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
                'min' => '1',
                'max' => '10000',
                'step' => '1',
            ],
            'required' => true,
        ])
        ->add('startDate', DateTimeType::class, [
            'label' => 'Date de début',
            'widget' => 'single_text',
            'attr' => [
                'class' => 'form-control mb-3',
                'max' => '2024-12-31T23:59',
                'min' => '2024-02-01T10:00',
            ],
            'required' => true,
        ])
        ->add('endDate', DateTimeType::class, [
            'label' => 'Date de fin',
            'widget' => 'single_text',
            'attr' => [
                'class' => 'form-control mb-3',
                'max' => '2024-12-31T23:59',
                'min' => '2024-02-01T10:00',
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
        // Ajoutez la logique pour les options des équipes et des matchs
        ->add('matchId', ChoiceType::class, [
            'label' => 'Match ',
            
            'attr' => [
                'class' => 'form-select mb-3',
            ],
            'required' => true,
        ])
        ->add('teamId', ChoiceType::class, [
            'placeholder' => 'Choisissez une équipe',
            'attr' => [
                'class' => 'form-select mb-3',
            ],'mapped' => false,
            'choices' => $teams,
        ]);
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            // Activation de la protection CSRF
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            // Génération d'un identifiant unique pour le token lié à un type de formulire spécifique
            'csrf_token_id' => 'registration_item',
        ]);
    }
}
