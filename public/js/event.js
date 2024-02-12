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
            // attente d'une promesse pour récupérer les données
            const matchesData = await matchesResponse.json();
            if (!matchesResponse.ok) {
                throw new Error('la requête n\'a pas abouti, veuillez réessayer plus tard');
            }

            const now = new Date();
            // Filtrer les matchs à venir pour l'équipe sélectionnée
            const filteredMatches = matchesData.filter(match => {
                const matchDateTime = new Date(match.matchDateTimeUTC);
                return matchDateTime > now && (match.team1.teamId == teamId || match.team2.teamId == teamId);
            });

            // Mise à jour de la liste déroulante des matchs
            matchsList.innerHTML = '';
            matchsList.appendChild(createOption('Choisir un match'));

            // Ajouter des options pour chaque match filtré
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

        fetch(`/events/sorted/${teamId}/${status}`)
        .then(response => response.json())
        .then(eventsSort => {
            eventsContainer.innerHTML = ''; // Nettoie le conteneur
            
            const eventsHtml = eventsSort.map(event => {
                // Gestion de l'affichage du statut et de la disponibilité
                let statusHtml = event.usersParticipate.length === event.numberOfPlaces ?
                    `<p class="card-text small mb-1">Statut : <span class="bg-danger fw-bold py-1 px-2 text-light rounded">complet</span></p>` :
                    `<p class="card-text small mb-1">
                        Statut : <span class="bg-success fw-bold py-1 px-2 text-light rounded">ouvert</span>
                    </p>
                    <p class="card-text small mb-1">
                        Disponibilité : ${event.usersParticipate.length} / ${event.numberOfPlaces}
                    </p>`;

                // Génère le HTML pour chaque événement
                return `
                    <div class="card-event mb-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="/uploads/avatars/${event.user.avatar}" alt="avatar" class="rounded-circle mb-2" width="50" height="50">
                                    <p class="text-center mb-0">${event.user.pseudo}</p>
                                </div>
                                <div class="card-detail col">
                                    <h5 class="card-title fs-6"><a href="/events/show/${event.id}">${event.name}</a></h5>
                                    <p class="card-text small mb-1">Débute le ${new Date(event.startDate).toLocaleDateString()}</p>
                                    ${statusHtml}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }).join(''); // Joint tous les éléments HTML en une seule chaîne

            eventsContainer.innerHTML = eventsHtml; // Injecte le HTML généré dans le conteneur
        })
        .catch(error => {
            console.error('Error:', error);
        });

    }

    // Afficher les évènements au chargement de la page
    fetchSortedEvents();

    // Attacher l'événement de changement aux sélecteurs
    if (sortByTeam && sortByStatus) {
        sortByTeam.addEventListener('change', fetchSortedEvents);
        sortByStatus.addEventListener('change', fetchSortedEvents);
    }

    /** 
         * Géo localisation avec geo.api.gouv.fr
    */
    const address = document.getElementById('address').innerText;
    const city = document.getElementById('city').innerText;
    const zipCode = document.getElementById('zip-code').innerText;
    const fullAddress = `${address}, ${zipCode} ${city}`;

    console.log(fullAddress);
    
    function geocodeAddressGouvFr(address) {
        var url = `https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(address)}`;
    
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.features && data.features.length > 0) {
                    var latitude = data.features[0].geometry.coordinates[1];
                    var longitude = data.features[0].geometry.coordinates[0];
                    console.log('Latitude:', latitude, 'Longitude:', longitude);
                    // Ici, vous pouvez utiliser ces coordonnées pour initialiser votre carte Leaflet

                    // Créer une carte Leaflet 
                    var map = L.map('map').setView([latitude, longitude], 13);

                    // Ajouter des tuiles OpenStreetMap à la carte
                    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                    }).addTo(map);

                    // Ajouter un marqueur à la carte 
                    let marker = L.marker([latitude, longitude]).addTo(map);
                    marker.bindPopup(address).openPopup();

                } else {
                    console.log('Geocoding failed: No results found');
                }
            })
            .catch(error => console.log('Error:', error));
    }
    
    //appel de la fonction
    geocodeAddressGouvFr(fullAddress);



})
