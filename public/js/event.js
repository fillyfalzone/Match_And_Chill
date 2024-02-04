window.document.addEventListener('DOMContentLoaded', function() { 
   
   /**
        * Créer un écvènement
    */
   const teamsList = document.getElementById('select-teamId');
   const matchsList = document.getElementById('select-matchId'); 

   const startDateTimeInput = document.getElementById('event_form_startDate');
   const endDateTimeInput = document.getElementById('event_form_endDate');

   // Fetch le tableau des équipes
    fetch('https://api.openligadb.de/getbltable/bl1/2023')
    .then(response => response.json())
    .then(data => {
    
        data.forEach(team => {
            const option = document.createElement('option');
            option.value = team.teamInfoId;
            option.textContent = team.shortName;
            teamsList.appendChild(option);
        })
    })
    .catch(error => console.error(error));

    // convertir la date et l'heure en format lisible
    function formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
   
   // Afficher la liste des en fonction de l'équipe
    teamsList.addEventListener('change', function() {
        const teamId = this.value;

        // Fetch les matchs à venir pour l'équipe sélectionnée
        fetch('https://api.openligadb.de/getmatchdata/bl1/2023')
        .then(response => response.json())
        .then(data => {
            const now = new Date(); // Obtenez la date et l'heure actuelles
            const filteredMatches = data.filter(match => {
                const matchDateTime = new Date(match.matchDateTimeUTC); // Convertissez la date/heure du match en objet Date
                return matchDateTime > now && // Le match n'a pas encore commencé
                    (match.team1.teamId == teamId || match.team2.teamId == teamId); // L'ID de l'équipe correspond à team1 ou team2
            });
            matchsList.innerHTML = ''; // Effacez les options précédentes

            //on ajoute une option placeholder
            const option = document.createElement('option');
            option.value = '0';
            option.innerHTML = 'Choisir un match';
            matchsList.appendChild(option);

            // Ici, vous pouvez continuer à traiter les matchs filtrés comme avant
            filteredMatches.forEach(match => {
                const dateTime = formatDateTime(match.matchDateTime);
                const option = document.createElement('option');
                //ajouter l'attribut data-match-start-date pour obtenir la date de début du match
                option.setAttribute('data-match-start-date', new Date(match.matchDateTimeUTC).toISOString());

                option.value = match.matchID; // Assurez-vous d'utiliser la bonne clé pour l'ID du match
                option.textContent = match.team1.shortName + ' vs ' + match.team2.shortName + ' - ' + dateTime;
                matchsList.appendChild(option);

            });
        }) 
        .catch(error => console.error(error));
    
    })        
   
    // Mettre à jour les champs de date et d'heure en fonction du match sélectionné
    matchsList.addEventListener('change', function() {
        let selectedMatchDateTime = this.options[this.selectedIndex].getAttribute('data-match-start-date');
        
   
        // Supprimez les millisecondes et le 'Z' pour utiliser le format local
        selectedMatchDateTime = selectedMatchDateTime.substring(0, selectedMatchDateTime.lastIndexOf("."));

        // Définissez la date de début maximale pour la date de début de l'événement
        startDateTimeInput.setAttribute('max', selectedMatchDateTime);

        // Définir la date minimale à aujourd'hui pour le champ startDateTimeInput
        const today = new Date();
        const minDate = today.toISOString().substring(0, 16); // Convertit la date actuelle en format YYYY-MM-DDThh:mm
        startDateTimeInput.setAttribute('min', minDate);

        // Convertir la chaîne de date sélectionnée en objet Date
        let minEndDate = new Date(selectedMatchDateTime);
        // Ajoutez 2 heures pour le min de endDateTimeInput
        minEndDate.setHours(minEndDate.getHours() + 2);

        // Formattez le minEndDate pour le définir comme valeur min pour endDateTimeInput
        endDateTimeInput.setAttribute('min', minEndDate.toISOString().substring(0, minEndDate.toISOString().lastIndexOf(":")));

        // Ajoutez 3 jours au minEndDate pour le max de endDateTimeInput
        let maxEndDate = new Date(minEndDate);
        maxEndDate.setDate(maxEndDate.getDate() + 3);

        // Formattez le maxEndDate pour le définir comme valeur max pour endDateTimeInput
        endDateTimeInput.setAttribute('max', maxEndDate.toISOString().substring(0, maxEndDate.toISOString().lastIndexOf(":"))); 
    });
    

    const btnContainer = document.getElementById('submit-container');
    // Input caché pour stocker l'ID du match sélectionné
    const matchIdInput = document.getElementById('event_form_matchId');

    // Afficher le bouton de soumission si l'équipe et le match sont sélectionnés
    matchsList.addEventListener('change', function() {
        if (this.value != '0' && matchsList.value != '0') {

            btnContainer.innerHTML = ''; // Effacez les boutons précédents
            //On crée un bouton de soumission
            const submitButton = document.createElement('button');
            submitButton.type = 'submit';
            submitButton.classList.add('btn', 'btn-primary');
            submitButton.textContent = `
                {% if id %}
                    Modifier un évènement
                {% else %}
                    Creer un évènement
                {% endif %}`;
            btnContainer.appendChild(submitButton);
       
            //on ajoute la valeur de l'ID du match sélectionné à l'input caché
            matchIdInput.value = this.value;

            console.log(matchIdInput.value);
          
        }
    });



    

   
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
