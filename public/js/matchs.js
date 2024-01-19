document.addEventListener("DOMContentLoaded", function () {
    /*
        * ------------------ Filtre des match (On recupère les match en fonction de la date) ---------------------- 
    */

    // -------------------------- Filter date ----------- 

    // Sélectionnez l'élément de liste déroulante par son ID
    const select = document.getElementById("filter-date");

    // Fonction pour obtenir la date actuelle au format "YYYY-MM-DD" en utilisant la bibliothèque dayjs
    function getCurrentDate() {
        return dayjs().format('YYYY-MM-DD');
    }

    // Fonction pour mettre à jour la liste déroulante
    function updateDropdown() {
        // Effacer les options existantes dans la liste déroulante
        select.innerHTML = "";

        // Obtenir la date actuelle
        let currentDate = getCurrentDate();

        // Vérifier si une date stockée dans le cookie est valide et l'utiliser comme date actuelle si elle l'est
        let storedDate = getCookie("currentDate");
        if (storedDate && dayjs(storedDate).isValid()) {
            currentDate = storedDate;
        }

        // Boucler sur les 8 jours avant et après le jour actuel pour ajouter des options à la liste déroulante
        for (let i = -8; i <= 8; i++) {
            // Obtenir la date pour chaque jour dans la boucle
            let date = dayjs().add(i, 'day');
            // Formater la date au format "YYYY-MM-DD"
            let formattedDate = date.format('YYYY-MM-DD');
            // Obtenir le texte de l'option en fonction du décalage par rapport à aujourd'hui
            let optionText = getOptionText(i, date, currentDate);

            // Créer un élément d'option
            let option = document.createElement("option");
            option.value = formattedDate;
            option.text = optionText;

            // Sélectionner l'option si elle correspond à la date actuelle
            if (formattedDate === currentDate) {
                option.selected = true;
            }

            // Ajouter l'option à la liste déroulante
            select.add(option);
        }

        // Stocker la date actuelle dans le cookie
        setCookie("currentDate", currentDate);
    }

    // Fonction pour obtenir le texte de l'option en fonction du décalage par rapport à aujourd'hui
    function getOptionText(offset, date, currentDate) {
        // Si le jour est aujourd'hui, hier, ou demain, utiliser des libellés spéciaux
        if (offset === 0) {
            return "Aujourd'hui";
        } else if (offset === -1) {
            return "Hier";
        } else if (offset === 1) {
            return "Demain";
        } else {
            // Pour les autres jours, utiliser le format "Jeu DD/MM" par exemple
            return date.format('ddd DD/MM');
        }
    }

    // Fonction pour définir un cookie
    function setCookie(name, value) {
        document.cookie = `${name}=${value};path=/`;
    }

    // Fonction pour obtenir la valeur d'un cookie
    function getCookie(name) {
        const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
        if (match) return match[2];
    }


    // Appeler la fonction pour mettre à jour la liste déroulante au chargement de la page
    if (select) {
        updateDropdown();
    }

    /*
        * Consommer l'API avec la méthode fect
    */

    function getMatchsByDate(selectedDate) {

         // File d'ariane dynamique (On attribut la valeur de la liste déroulante)
        let secondAriane = document.getElementById("here-ariane");
        secondAriane.innerHTML = "";
        secondAriane.innerHTML = selectedDate;


        // Construire l'URL de l'API en utilisant la date
        const leagueSeason = "2023";
        const leagueShortcut = "bl1"; // bundesliga 1
    
        fetch(`https://api.openligadb.de/getmatchdata/${leagueShortcut}/${leagueSeason}`)
            .then(response => response.json()) // Analyser la réponse JSON
            .then(data => {
                // Filtrer les matchs pour ceux dont la date correspond à la date sélectionnée
                const matchesForDate = data.filter(match => {
                    const matchDate = match.matchDateTime.split('T')[0];
                    return matchDate === selectedDate;
                });
    
                // Appeler la fonction pour afficher les détails des matchs
                displayMatchs(matchesForDate);
            })
            .catch(error => {
                console.error('Erreur de requête :', error);
            });
    }

    function displayMatchs(matches) {
        // Récupérer le conteneur des matchs
        let matchContainer = document.getElementById("matchs-container"); 
    
        // Vider le conteneur
        matchContainer.innerHTML = "";
    
        // Itérer sur les matchs et afficher les détails
        matches.forEach(match => {
            // Construire le contenu HTML pour afficher les détails du match
            let matchDetails = document.createElement('tr');
            matchDetails.classList.add('match-details');

            // Déterminer le statut du match 
            // Déterminer le statut en fonction des conditions
            let status = "";
            if (match.matchIsFinished) {
                status = "Terminé";
            } else if (!match.matchIsFinished && isMatchInFuture(match.matchDateTime)) {
                status = "Prévu";
            } else if (!match.matchIsFinished && isMatchInProgress(match.matchDateTime)) {
                status = "En cours";
            }
             // Vérifier si match.matchResults[1] est défini avant d'accéder à ses propriétés
            let team1Points = match.matchResults[1] ? match.matchResults[1].pointsTeam1 : "N/A";
            let team2Points = match.matchResults[1] ? match.matchResults[1].pointsTeam2 : "N/A";

           
            matchDetails.innerHTML = `
            <td class="match-infos">
                <div class="match-id">${match.matchID}</div>
                <div class="favorite-match">
                    <div class="status">${status}</div>
                </div>
                <div id="versus-${match.matchID}" class="versus">
                    <p class="match-time">${match.matchDateTime.split('T')[1]}</p>
                    <div class="dom">
                        <div class="team-id">${match.team1.teamID}</div>
                        <p class="team-name1">${match.team1.teamName}  <span class="goal-number">${team1Points}</span></p>
                    </div>
                    
                    <div class="ext">
                        <div class="team-id">${match.team2.teamID}</div>
                        <p class="team-name2">${match.team2.teamName}  <span class="goal-number">${team2Points}</span></p>
                    </div>
                </div>
                <div class="options">
                    <div class="match-events">
                        <iconify-icon title="évènement" class="event-icon" icon="streamline:party-popper" style="color: #161b35;" width="15" height="15"></iconify-icon>
                        <sup class="number-events">25</sup>
                    </div>

                    <div class="comments-match">
                        <span>
                            <iconify-icon title="commentaires" class="comment-icon" icon="bx:comment" style="color: #161b35;" width="15" height="15" onclick="window.location.href='#match-detail'"></iconify-icon>
                            <sup class="number-comments">25</sup>
                        </span>
                    </div>
                </div>
            </td>
            `;                                      

            // Ajouter le contenu HTML au conteneur
            matchContainer.appendChild(matchDetails);


            // Ajouter un gestionnaire d'événements clic à la ligne
            let versus = document.getElementById(`versus-${match.matchID}`)

            versus.addEventListener('click', function() {
                redirectToMatchDetail(match.matchID);
            });
        })
    }

    // Fonction pour rediriger vers les details du match --------------------------------
    function redirectToMatchDetail(matchID) {
        // Naviguer vers les détails du match
        window.location.href = 'matchsList/match/' + matchID;
    } 

  
    // Modifier le status du match 
    function isMatchInFuture(matchDateTime) {
        // Comparer la date actuelle avec la date du match pour déterminer s'il est prévu pour le futur
        const currentDateTime = new Date();
        const matchDate = new Date(matchDateTime);
        return currentDateTime < matchDate;
    }
    
    function isMatchInProgress(matchDateTime) {
        // Comparer l'heure actuelle avec l'heure du match pour déterminer s'il est en cours
        const currentDateTime = new Date();
        const matchDate = new Date(matchDateTime);
        return currentDateTime >= matchDate;
    }

    if (select) {
        // Avoir les matchs du jours au chargement de la page
        getMatchsByDate(select.value);

        // Ajoutez un gestionnaire d'événements à votre liste déroulante
        select.addEventListener("change", function() {
            // Récupérez la nouvelle date sélectionnée
            let selectedDate = this.value;
        
            // Appelez la fonction pour récupérer les données en fonction de la nouvelle date
            getMatchsByDate(selectedDate);
        });
    }
    
    
    /*
        * Macth detail card content  
    */

    const cardDiscussion = document.getElementById("card-discussion");
    const cardEvent = document.getElementById("card-event");

    




    const commentIcons = document.querySelectorAll(".comment-icon");
    const eventIcons = document.querySelectorAll(".event-icon");
    const numberEvents = document.querySelectorAll(".number-events");
    const numberComments = document.querySelectorAll(".number-comments");

    // Modification de l'état des icônes de commentaires
    commentIcons.forEach(commentIcon => {
        commentIcon.addEventListener('click', () => {
            const isCommentFilled = commentIcon.getAttribute('icon') === 'bxs:comment';

            // Bascule entre l'état initial et l'état modifié
            if (isCommentFilled) {
                commentIcon.setAttribute('icon', 'bx:comment');
                commentIcon.style.color = '#161b35';
            } else {
                commentIcon.setAttribute('icon', 'bxs:comment');
                commentIcon.style.color = '#9c001a';
            }
        });
    });

    // Modification de l'état des icônes d'événements
    eventIcons.forEach(eventIcon => {
        eventIcon.addEventListener('click', () => {
            const isEventFilled = eventIcon.getAttribute('icon') === 'streamline:party-popper-solid';

            // Bascule entre l'état initial et l'état modifié
            if (isEventFilled) {
                eventIcon.setAttribute('icon', 'streamline:party-popper');
                eventIcon.style.color = '#161b35';
            } else {
                eventIcon.setAttribute('icon', 'streamline:party-popper-solid');
                eventIcon.style.color = '#9c001a';
            }
        });
    });


});

