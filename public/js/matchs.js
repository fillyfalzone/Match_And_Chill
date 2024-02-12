document.addEventListener("DOMContentLoaded", function () {
    // On recupère le bouton logout
    const logOut = document.getElementById("logout");
    // Sélectionnez l'élément de liste déroulante par son ID
    var select = document.getElementById("filter-date");

       // Charger les sessions de l'utilisateur
       handleLoginSuccess();

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

    // Fonction pour obtenir les favoris depuis le stockage local
    function getFavoritesFromLocal() {
        // Utilisation de localStorage au lieu de sessionStorage
        let favoritesRaw = localStorage.getItem('favorites');

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

        let favorites = getFavoritesFromLocal();

        // Si les favoris ne sont pas en session, les charger depuis le serveur
        if (!favorites) {
            loadFavorites();
            
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
            let team1Points = match.matchResults[1] ? match.matchResults[1].pointsTeam1 : "";
            let team2Points = match.matchResults[1] ? match.matchResults[1].pointsTeam2 : "";

            // Ajouter la logique pour définir l'icône de favori en fonction de l'état stocké
            let isFavorite = favorites.includes(match.matchID.toString());
        
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
                        <p class="team-name1">${match.team1.shortName}  <span class="goal-number">${team1Points}</span></p>
                    </div>
                    
                    <div class="ext">
                        <div class="team-id">${match.team2.teamID}</div>
                        <p class="team-name2">${match.team2.shortName}  <span class="goal-number">${team2Points}</span></p>
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
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        let isFavorite = favorites.includes(idValue);

        // Préparer les données à envoyer
        const data = {
            status: !isFavorite, // Notez l'inversion ici pour refléter l'action souhaitée
            id: idValue
        };

        // Début de la requête fetch pour envoyer des données au back-end
        fetch('/matchList/favorite', {
            method: 'POST', 
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok for favorites.');
            }
            return response.json(); // Assurez-vous que le serveur renvoie une réponse JSON
        })
        .then(data => {
            // Inverser l'état de isFavorite suite à la réponse
            isFavorite = !isFavorite;
            updateLocalFavorites(idValue, isFavorite);

            // Mise à jour de l'icône et de la couleur basées sur le nouvel état
            let newIcon = isFavorite ? 'material-symbols-light:star' : 'material-symbols-light:star-outline';
            let newColor = isFavorite ? '#9c001a' : '#161B35';
            starElement.setAttribute('icon', newIcon);
            starElement.style.color = newColor;

        })
        .catch((error) => {
            console.error('Error:', error);
            alert("Une erreur est survenue lors de la mise à jour de vos favoris. Ou vous n'êtes pas connecté.");
        });
    }
    // Fonction pour mettre à jour les favoris dans le stockage local
    function updateLocalFavorites(matchId, isFavorite) {
    // Utilisation de localStorage au lieu de sessionStorage
    let favorites = JSON.parse(localStorage.getItem('favorites')) || [];

    if (isFavorite) {
        if (!favorites.includes(matchId)) {
            favorites.push(matchId);
        }
    } else {
        favorites = favorites.filter(id => id !== matchId);
    }

    localStorage.setItem('favorites', JSON.stringify(favorites)); // Utilisation de localStorage au lieu de sessionStorage
    }

    // Fonction pour charger les favoris depuis le serveur et les stocker localement
    function loadFavorites() {
      
        fetch('/matchList/favorite/getmatchs')
            .then(response => {
                if (!response.ok) {
                    throw new Error('La réponse du réseau n\'est pas correcte pour les favoris.');
                } else {
                    return response.json();
                }
            })
            .then(data => {
                localStorage.setItem('favorites', JSON.stringify(data.favoriteMatchIds)); // Utilisation de localStorage au lieu de sessionStorage
               
                if (select) {
                    // Récupérez la nouvelle date sélectionnée
                    let selectedDate = select.value;
                    // Appelez la fonction
                getMatchsByDate(selectedDate);
                }
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

    /**
        *  
    */


    // / Fonction pour déconnecter l'utilisateur
    function logoutUser() {
        // Suppression de l'élément favorites du localStorage
        // localStorage.removeItem('favorites');
    }

    // Fonction pour gérer la connexion réussie de l'utilisateur
    function handleLoginSuccess() {
        // Définition d'un indicateur indiquant que l'utilisateur est connecté
        localStorage.setItem('isLoggedIn', 'true');
        if (localStorage.getItem('favorites')) {
            loadFavorites();
        }
    }


    /*
        * Macth detail card content  
    */

    // Recupération des cards de discussions et évènement du DOM
    const cardDiscussion = document.getElementById("card-discussion");
    const cardEvent = document.getElementById("card-event");

    /*
        * Modal édition des commentaires
    */
    // Ouvrir le modal d'édition des commentaires
    let editButtons = document.querySelectorAll('.edit');

    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {

            let commentId = this.getAttribute('data-comment-id');
            let commentText = this.getAttribute('data-comment-text');

            document.getElementById('commentId').value = commentId;
            document.getElementById('commentText').value = commentText;

            let modal = new bootstrap.Modal(document.getElementById('editCommentModal'));
            modal.show();
        });
    });

    // Soumettre le formulaire d'édition des commentaires et requête fetch pour envoyer les données au back-end
    const editCommentForm = document.getElementById('editCommentForm');

    if (editCommentForm) {
        
        editCommentForm.addEventListener('submit', function() {
            
            let commentId = document.getElementById('commentId').value;
            let commentText = document.getElementById('commentText').value;
            let tokenEdit = document.getElementById('token-edit-comment').value;
            const matchId = window.location.pathname.split("/")[3];
    
            
            // Préparer les données à envoyer
            const data = {
                commentText: commentText,
                tokenEdit: tokenEdit
            };
    
            // Envoyer la requête AJAX
            fetch(`/matchsList/match/${matchId}/edit/${commentId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            // la réponse
            .then(response => response.json())
            .then(data => {
                // Traiter la réponse
                // console.log(data);
            })
            .catch(error => {
                // Gérer les erreurs.
                console.error(error);
            });
        
            // Fermer la modal
            let modal = bootstrap.Modal.getInstance(document.getElementById('editCommentModal'));
            modal.hide();
            // recharger la page
            window.location.reload();
        });
    }
});

