document.addEventListener("DOMContentLoaded", function () {
    const allDiscussions = document.querySelectorAll(".discussion");
    const itemsPerPage = 3; // Définir le nombre d'items par page
    const totalPages = Math.ceil(allDiscussions.length / itemsPerPage);
    let currentPage = 0;

    const pageNumbersContainer = document.querySelector(".current-info");
    let pageNumbers = []; // Variable pour stocker les éléments des numéros de page

    function showPage(pageNumber) {
        const startIndex = pageNumber * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        allDiscussions.forEach((subcategory, index) => {
            if (index >= startIndex && index < endIndex) {
                subcategory.style.display = "block";  // Affiche la div de la page actuelle
            } else {
                subcategory.style.display = "none";   // Cache les divs des autres pages
            }
        });
    }

    function updateButtons() {
        prevButton.disabled = currentPage === 0;
        nextButton.disabled = currentPage === totalPages - 1;
    }

    function setActive() {
        pageNumbers.forEach((page, index) => {
            if (currentPage === index) {
                page.classList.add("active");
            } else {
                page.classList.remove("active");
            }
        });
    }

    function generatePageNumbers() {
        // Effacer les numéros de page existants
        pageNumbersContainer.innerHTML = "";
        pageNumbers = []; // Réinitialiser la variable pageNumbers

        // Générer les numéros de page dynamiquement
        for (let i = 1; i <= totalPages; i++) {
            const pageButton = document.createElement("div");
            pageButton.classList.add("page-number");
            pageButton.textContent = i;

            // Ajouter le bouton de page à la pageNumbersContainer
            pageNumbersContainer.appendChild(pageButton);
            pageNumbers.push(pageButton); // Ajouter l'élément à la variable pageNumbers

            // Ajouter un gestionnaire d'événement au clic de la page
            pageButton.addEventListener("click", function () {
                showPage(i - 1);
                currentPage = i - 1;
                updateButtons();
                setActive();
            });

            // Ajouter la classe "active" à la première page
            if (i === 1) {
                pageButton.classList.add("active");
            }
        }
    }

    const prevButton = document.querySelector(".prev-page");
    const nextButton = document.querySelector(".next-page");

    if (prevButton || nextButton){
        
        prevButton.addEventListener("click", function () {
            if (currentPage > 0) {
                currentPage--;
                showPage(currentPage);
                updateButtons();
                setActive();
            }
        });
    
        nextButton.addEventListener("click", function () {
            if (currentPage < totalPages - 1) {
                currentPage++;
                showPage(currentPage);
                updateButtons();
                setActive();
            }
        });
        
    }

    

    // Générer les numéros de page initiaux
    generatePageNumbers();

    // Afficher la catégorie de la page actuelle au chargement initial
    showPage(currentPage);
    updateButtons();
});
