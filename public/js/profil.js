document.addEventListener("DOMContentLoaded", function () {

    getMatchsByDate();

    function getMatchsByDate() {

    
        // Début de la requête fetch à l'API pour obtenir les données des matchs
        fetch(`/mymatchs/data`)
        .then(response => response.json()) // Étape 1 : Réception de la réponse et conversion de la réponse JSON en objet JavaScript
        .then(data => {
            
            displayMatchs(data);
        })
        .catch(error => {
            // Étape 4 : Gestion des erreurs
            // Affichage d'une erreur en cas de problème avec la requête fetch
            console.error('Erreur de requête :', error);
        });

    }

    function getFavoritesFromLocal() {
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
        if (favorites) {
            loadFavorites();
            
        }

        // Récupérer le conteneur des matchs
        let myMatchContainer = document.getElementById("myMatchs-container"); 
    
        // Vider le conteneur
        myMatchContainer.innerHTML = "";
    
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
            myMatchContainer.appendChild(matchDetails);


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

    // Mettre à jours les matchs favorit de l'utilisateut stocké dans le localStorage
    function updateLocalFavorites(matchId, isFavorite) {
        // recupérer dans le local storage les favoris ou un tableau vite si l'utilisateur n'est pas connecté 
        let favorites = JSON.parse(localStorage.getItem('favorites')) || [];
        
        if (isFavorite) {
            if (!favorites.includes(matchId)) {
                favorites.push(matchId);
            }
        } else {
            favorites = favorites.filter(id => id !== matchId);
        }
    
        localStorage.setItem('favorites', JSON.stringify(favorites)); // Mettre à jour le stockage de session
    }

    // Recevoir les favoris du serveur uniquement si l'utilisateur est connecté 
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
                localStorage.setItem('favorites', JSON.stringify(data.favoriteMatchIds));
                let selectedDate = select.value;
                getMatchsByDate(selectedDate);
            })
            .catch(error => console.error('Erreur lors du chargement des favoris:', error));
    }
    

});