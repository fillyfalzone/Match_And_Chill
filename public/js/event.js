window.document.addEventListener('DOMContentLoaded', function() { 
   
   /**
        * Créer un écvènement
    */
   const teamsList = document.getElementById('event_form_teamId');
   const matchsList = document.getElementById('event_form_matchId'); 

   const startDateTimeInput = document.getElementById('event_form_startDate');
   const endDateTimeInput = document.getElementById('event_form_endDate');

   // convertir la date et l'heure en format lisible
    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }

    // Fonction utilitaire pour créer une option de sélection
    function createOption(text, value = '', dataAttributes = {}) {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = text;
        Object.keys(dataAttributes).forEach(key => {
            option.setAttribute(key, dataAttributes[key]);
        });
        return option;
    }

    // Fonction asynchrone pour charger les matchs en fonction de l'équipe sélectionnée
    async function loadMatchesForTeam(teamId) {
        try {
            // Fetch pour récupérer les matchs pour l'équipe sélectionnée
            const matchesResponse = await fetch('https://api.openligadb.de/getmatchdata/bl1/2023');
            const matchesData = await matchesResponse.json();
            if (!matchesResponse.ok) {
                throw new Error('Network response was not ok for matches data.');
            }

            const now = new Date();
            const filteredMatches = matchesData.filter(match => {
                const matchDateTime = new Date(match.matchDateTimeUTC);
                return matchDateTime > now && (match.team1.teamId == teamId || match.team2.teamId == teamId);
            });

            // Mise à jour de la liste déroulante des matchs
            matchsList.innerHTML = '';
            matchsList.appendChild(createOption('Choisir un match'));

            filteredMatches.forEach(match => {
                const dateTime = formatDateTime(match.matchDateTime);
                matchsList.appendChild(createOption(
                    `${match.team1.shortName} vs ${match.team2.shortName} - ${dateTime}`,
                    match.matchID,
                    {'data-match-start-date': new Date(match.matchDateTimeUTC).toISOString()}
                ));
            });
        } catch (error) {
            console.error('Failed to load matches:', error);
        }
    }

    // Écouteur d'événements pour la sélection d'une équipe
    teamsList.addEventListener('change', function() {
        loadMatchesForTeam(this.value);
    });


   

   
    
    // Adapter le calendrier en fonction de la date du match sélectionné
    matchsList.addEventListener('change', function() {
        // Obtenez la date et l'heure du match sélectionné
        let selectedMatchDateTime = this.options[this.selectedIndex].dataset.matchStartDate;

        // Supprimez les millisecondes et le 'Z' pour utiliser le format local
        selectedMatchDateTime = selectedMatchDateTime.substring(0, selectedMatchDateTime.lastIndexOf("."));

        // Définissez la date de début maximale pour la date de début de l'événement qui est la date de début du match
        startDateTimeInput.max = selectedMatchDateTime;

        // Définir la date minimale à aujourd'hui pour le champ startDateTimeInput
        const today = new Date();
        const minDate = today.toISOString().substring(0, 16); // Convertit la date actuelle en format YYYY-MM-DDThh:mm
        startDateTimeInput.min = minDate;

        // Convertir la chaîne de date sélectionnée en objet Date
        let minEndDate = new Date(selectedMatchDateTime);
        // Ajoutez 2 heures pour le min de endDateTimeInput
        minEndDate.setHours(minEndDate.getHours() + 2);

        // Formattez le minEndDate pour le définir comme valeur min pour endDateTimeInput
        endDateTimeInput.min = minEndDate.toISOString().substring(0, minEndDate.toISOString().lastIndexOf(":"));

        // Ajoutez 3 jours au minEndDate pour le max de endDateTimeInput
        let maxEndDate = new Date(minEndDate);
        maxEndDate.setDate(maxEndDate.getDate() + 3);

        // Formattez le maxEndDate pour le définir comme valeur max pour endDateTimeInput
        endDateTimeInput.max = maxEndDate.toISOString().substring(0, maxEndDate.toISOString().lastIndexOf(":"));
    });
    

    // const btnContainer = document.getElementById('submit-container');
    // // Input caché pour stocker l'ID du match sélectionné
    // const matchIdInput = document.getElementById('event_form_matchId');

    // //On bloque le bouton de soumission si aucun match n'est sélectionné
    // const submitBtn = document.getElementById('btn-submit');
    
    // submitBtn.addEventListener('click', function(e) {

    //     if (matchsList.value === '0') {
    //         e.preventDefault();
    //         alert('Veuillez sélectionner un match');
    //     }
    //     //on ajoute la valeur de l'ID du match sélectionné à l'input caché
    //     matchIdInput.value = this.value;
    //     console.log(matchIdInput.value);
    // })
          
        
    



    

   
    // // on cible les balises select
    // const sortByTeam = document.getElementById('sort-by-team');
    // const sortByStatus = document.getElementById('sort-by-status');

    // sortByTeam.addEventListener('change', function() {

    //     const data = {'teamId': this.value};
        
    //     fetch('/events/sorted', { 
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //         },
    //         body: JSON.stringify(data)
    //         })
    //     .then(response => response.json())
    //     .then(data => {
    //         console.log(data);
    //     })
    //     .catch(error => console.error(error));
        
    // })

    // sortByStatus.addEventListener('change', function() {
    //     const cardEvents = document.querySelectorAll('.card-event');
    //     const date = new Date();

    //     if(this.value ==='open') {
            
    //     }
    // })


    // function loadEvents(value) {
    //     const eventsContainer = document.getElementById('events-container');
    //     eventsContainer.innerHTML = '';
        
    //     eventsContainer.innerHTML = `
    //     <div class="card-event mb-4">
    //         <div class="card-body">
    //             <div class="row align-items-center">
    //                 <div class="col-auto">
    //                     <!-- Placeholder pour l'avatar -->
    //                     <img src="https://via.placeholder.com/50" alt="avatar" class="rounded-circle mb-2">
    //                     <p class="text-center mb-0">userName</p>
    //                 </div>
    //                 <div class="card-detail col">
    //                     <h5 class="card-title fs-6">EventName</h5>
    //                     <p class="card-text small mb-1">Ajouté le 11-02-2024 à 12:23</p>
    //                     <p class="card-text small mb-1">Statut : ouvert</p>
    //                     <p class="card-text small mb-1">Disponibilité : 12/25 places</p>
                    
    //                 </div>
    //             </div>
    //         </div>
    //     </div>`
    // }

})
