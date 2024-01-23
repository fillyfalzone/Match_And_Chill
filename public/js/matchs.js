document.addEventListener("DOMContentLoaded", function () {
    // On recupère le bouton logout
    const logOut = document.getElementById("logout");
    // Sélectionnez l'élément de liste déroulante par son ID
    const select = document.getElementById("filter-date");

    /*
        * ------------------ Filtre des match (On recupère les match en fonction de la date) ---------------------- 
    */

    // -------------------------- Filter date ----------- 

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


        // Construire l'URL de l'API en utilisant l'année de la saison
        const leagueSeason = "2023";
        const leagueShortcut = "bl1"; // le nom de la league bundesliga 1
    
        // Début de la requête fetch à l'API pour obtenir les données des matchs
        fetch(`https://api.openligadb.de/getmatchdata/${leagueShortcut}/${leagueSeason}`)
        .then(response => response.json()) // Étape 1 : Réception de la réponse et conversion de la réponse JSON en objet JavaScript
        .then(data => {
            // Étape 2 : Traitement des données
            // Filtrer les matchs pour ceux dont la date correspond à la date sélectionnée
            const matchsForDate = data.filter(match => {
                const matchDate = match.matchDateTime.split('T')[0]; // Extraction de la date du match
                return matchDate === selectedDate; // Comparaison avec la date sélectionnée
            });

            // Étape 3 : Utilisation des données filtrées
            // Appeler la fonction pour afficher les détails des matchs filtrés
            displayMatchs(matchsForDate);
        })
        .catch(error => {
            // Étape 4 : Gestion des erreurs
            // Affichage d'une erreur en cas de problème avec la requête fetch
            console.error('Erreur de requête :', error);
        });

    }

    function getFavoritesFromSession() {
        let favoritesRaw = sessionStorage.getItem('favorites');
    
        if (favoritesRaw) {
            try {
                return JSON.parse(favoritesRaw);
            } catch (e) {
                console.error('Erreur lors du parsing des favoris:', e);
            }
        }
        return [];
    }

    function displayMatchs(matchs) {

        let favorites = getFavoritesFromSession();

        // Si les favoris ne sont pas en session, les charger depuis le serveur
        if (!favorites) {
            loadFavorites();
            // return;
        }

        // Récupérer le conteneur des matchs
        let matchContainer = document.getElementById("matchs-container"); 
    
        // Vider le conteneur
        matchContainer.innerHTML = "";
    
        // Itérer sur les matchs et afficher les détails
        matchs.forEach(match => {
            // Construire le contenu HTML pour afficher les détails du match
            let matchDetails = document.createElement('tr');
            matchDetails.classList.add('match-details');

            
            // Déterminer le statut en fonction des conditions
            let status = getStatus(match);
           
             // Vérifier si match.matchResults[1] est défini avant d'accéder à ses propriétés
            let team1Points = match.matchResults[1] ? match.matchResults[1].pointsTeam1 : "N/A";
            let team2Points = match.matchResults[1] ? match.matchResults[1].pointsTeam2 : "N/A";

            // Ajouter la logique pour définir l'icône de favori en fonction de l'état stocké
            let isFavorite = favorites.includes(match.matchID);
            let starIcon = isFavorite ? 'material-symbols-light:star' : 'material-symbols-light:star-outline';
            let starColor = isFavorite ? '#9c001a' : '#161B35';

            matchDetails.innerHTML = `
            <td class="match-infos">
                <div class="match-id">${match.matchID}</div>
                <div class="block-left">
                    <div class="status">${status}</div>
                    <iconify-icon icon="${starIcon}" style="color: ${starColor};" width="25" height="25" class="star" id="star-${match.matchID}"></iconify-icon>
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


             // Ajouter un gestionnaire d'événements pour le clic sur le match
            addMatchClickHandler(match);
        })

        // Ajouter des gestionnaires d'événements pour les étoiles après la mise à jour du DOM
        addStarClickHandlers(matchs); 
    }

    // ----------------------------------------------------------------------------------------------------------------
    /*
        * Gestion du click sur les étoiles des matchs favoris
    */

    // Fonction pour ajouter des gestionnaires d'événements pour les étoiles
    function addStarClickHandlers(matchs) {
        matchs.forEach(match => {
            let star = document.getElementById(`star-${match.matchID}`);
            star.addEventListener('click', function() {
                favoriteMatch(this, match.matchID);
            });
        });
    }

    // Fonction pour ajouter un gestionnaire d'événements pour le clic sur le match
    function addMatchClickHandler(match) {
        let versus = document.getElementById(`versus-${match.matchID}`);
        versus.addEventListener('click', function() {
            redirectToMatchDetail(match.matchID);
        });
    }
    // Fonction pour obtenir le statut du match
    function getStatus(match) {
        if (match.matchIsFinished) {
            return "Terminé";
        } else if (!match.matchIsFinished && isMatchInFuture(match.matchDateTime)) {
            return "Prévu";
        } else if (!match.matchIsFinished && isMatchInProgress(match.matchDateTime)) {
            return "En cours";
        }
        return "Non démarré";
    }

     // Fonction pour rediriger vers les details du match --------------------------------
     function redirectToMatchDetail(matchID) {
        // Naviguer vers les détails du match
        window.location.href = 'matchsList/match/' + matchID;
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


    // Fonction appelée lorsqu'un utilisateur clique sur une étoile pour ajouter/supprimer un favori
    function favoriteMatch(starElement, idValue) {
    
       
        let favorites = JSON.parse(sessionStorage.getItem('favorites'));
        const isFavorite = favorites ? favorites.includes(idValue) : false;

        // Préparer les données à envoyer
        const data = {
            status: isFavorite,
            id: idValue
        };

        // Début de la requête fetch pour envoyer des données au back-end
        fetch('/matchList/favorite', {
            method: 'POST', // Spécifier la méthode HTTP comme POST pour l'envoi des données
            headers: {
                'Content-Type': 'application/json', // Définir le type de contenu pour indiquer que le corps de la requête est au format JSON
            },
            body: JSON.stringify(data) // Convertir les données en chaîne JSON et les placer dans le corps de la requête
        })
        .then(response => {
            // Vérifier la réponse du serveur
            if (!response.ok && response.status === 401) {
                // Gérer le cas spécifique où l'utilisateur n'est pas connecté
                alert("Veuillez vous connecter ou vous inscrire pour effectuer cette action.");
                return;
            }
            // Convertir la réponse en JSON si la requête a réussi
            return response.json();
        })
        .then(data => {
            // Traitement des données reçues du serveur
            // Mettre à jour les données locales ou de session en fonction de la réponse
            updateLocalFavorites(idValue, isFavorite);
             // Mise à jour de l'icône
             starElement.setAttribute('icon', isFavorite ? 'material-symbols-light:star' : 'material-symbols-light:star-outline');
             starElement.style.color = isFavorite ? '#9c001a' : '#161B35';
            // Debug : Affichage des données reçues pour vérification
            console.log('Success:', data);
        })
        .catch((error) => {
            // Gestion des erreurs lors de la requête fetch
            console.error('Error:', error);

            alert("Veuillez vous connecter ou vous inscrire pour ajouter des matchs à vos favoris.");
        });

    }

    // Mettre à jours les matchs favorit de l'utilisateut stocké dans le localStorage
    function updateLocalFavorites(matchId, isFavorite) {
        // recupérer dans le local storage les favoris ou un tableau vite si l'utilisateur n'est pas connecté 
        let favorites = JSON.parse(sessionStorage.getItem('favorites')) || [];
        
        if (isFavorite) {
            if (!favorites.includes(matchId)) {
                favorites.push(matchId);
            }
        } else {
            favorites = favorites.filter(id => id !== matchId);
        }
    
        sessionStorage.setItem('favorites', JSON.stringify(favorites)); // Mettre à jour le stockage de session
    }

    // Recevoir les favoris du serveur uniquement si l'utilisateur est connecté 
    function loadFavorites() {
        fetch('/matchList/favorite/getmatchs')
            .then(response => response.json())
            .then(data => {
                sessionStorage.setItem('favorites', JSON.stringify(data.favoriteMatchIds));
                let selectedDate = select.value;
                getMatchsByDate(selectedDate);
            })
            .catch(error => console.error('Erreur lors du chargement des favoris:', error));
    }
    

    // ---------------------------------------------------------------------------------------------------------------- 

    /**
        * Déconnection et suppression de la session
    */
    if (logOut) {
        logOut.addEventListener('click', function() {
            logoutUser();
        });
    }

    function logoutUser() {
        // Effacer les favoris de la session
        sessionStorage.removeItem('favorites');
        
        // Supprimer le drapeau de connexion
        localStorage.removeItem('isLoggedIn');
    }

    // Definir le Drapeau lors de la connection, pour verifier si un utilisateur est connecté ou non
    function handleLoginSuccess() {
        // Définir un drapeau indiquant que l'utilisateur est connecté
        localStorage.setItem('isLoggedIn', 'true');
    }

    /*
        * Macth detail card content  
    */

    // Recupération des card de discussions et évènement du DOM
    const cardDiscussion = document.getElementById("card-discussion");
    const cardEvent = document.getElementById("card-event");

    // // Recupération des icons des commentaires et évènements 
    // const commentIcons = document.querySelectorAll(".comment-icon");
    // const eventIcons = document.querySelectorAll(".event-icon");


    // const numberEvents = document.querySelectorAll(".number-events");
    // const numberComments = document.querySelectorAll(".number-comments");


    // // Modification de l'état des icônes de commentaires
    // commentIcons.forEach(commentIcon => {
    //     commentIcon.addEventListener('click', () => {
    //         const isCommentFilled = commentIcon.getAttribute('icon') === 'bxs:comment';

    //         // Bascule entre l'état initial et l'état modifié
    //         if (isCommentFilled) {
    //             commentIcon.setAttribute('icon', 'bx:comment');
    //             commentIcon.style.color = '#161b35';
    //         } else {
    //             commentIcon.setAttribute('icon', 'bxs:comment');
    //             commentIcon.style.color = '#9c001a';
    //         }
    //     });
    // });

    // // Modification de l'état des icônes d'événements
    // eventIcons.forEach(eventIcon => {
    //     eventIcon.addEventListener('click', () => {
    //         // Recupérer l'attrbut 'icon' de l'élément 
    //         const isEventFilled = eventIcon.getAttribute('icon') === 'streamline:party-popper-solid';

    //         // Bascule entre l'état initial et l'état modifié lorsque l'utilisateur selectionne ou non un évènement 
    //         if (isEventFilled) {
    //             eventIcon.setAttribute('icon', 'streamline:party-popper');
    //             eventIcon.style.color = '#161b35';
    //         } else {
    //             eventIcon.setAttribute('icon', 'streamline:party-popper-solid');
    //             eventIcon.style.color = '#9c001a';
    //         }
    //     });
    // });


});

