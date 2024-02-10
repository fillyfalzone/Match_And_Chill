window.document.addEventListener('DOMContentLoaded', function() { 
   
   /**
        * Créer un évènement
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

    if (teamsList || matchsList) {
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
    }

 

    /** 
         * Trie des événements 
    */
    // Sélecteurs pour trier les événements
    const eventsContainer = document.getElementById('events-container');
    const sortByTeam = document.getElementById('sort-by-team');
    const sortByStatus = document.getElementById('sort-by-status'); // Correction du camelCase

    // Fonction pour trier et récupérer les événements
    function fetchSortedEvents() {
        const teamId = sortByTeam.value;
        const status = sortByStatus.value;

        fetch(`/events?teamId=${teamId}&status=${status}`)
            .then(response => response.text())
            .then(html => {
                
                eventsContainer.innerHTML = '';
                eventsContainer.innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    // Attacher l'événement de changement aux sélecteurs
    sortByTeam.addEventListener('change', fetchSortedEvents);
    sortByStatus.addEventListener('change', fetchSortedEvents);


})
