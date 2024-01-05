document.addEventListener("DOMContentLoaded", function () {
    const header = document.getElementById("header-home");
    const nav = document.querySelector(".nav-bar");
    const burger = document.querySelector(".burger");
    const burgerClose = document.querySelector(".burger-close");

    /* 
        *Home page burger menu ----------------------------------------------------------
    */
    burger.addEventListener("click", () => {
        navStyleTop();
        nav.classList.toggle("nav-toggle");
        burger.style.display = "none";
        burgerClose.style.display = "block";
    });

    burgerClose.addEventListener("click", () => {
        nav.classList.remove("nav-toggle");
        burger.style.display = "flex";
    });


    // Ajuster la position de l'overlay à la hauter du header
    function navStyleTop() {
        nav.style.top = header.clientHeight + "px";
    }

    /* -------------------Home page burger menu END ------------------------------- */

    // Sélectionnez tous les éléments de classe counter-item
    const counterItems = document.querySelectorAll('.counter-items');

    // Parcourez chaque élément et définissez le compteur
    counterItems.forEach(function (item) {
        const targetValue = parseInt(item.querySelector('span').innerText, 10);

        let counter = 0;
        const interval = 10; // Réglez l'intervalle de mise à jour du compteur (en millisecondes)

        const updateCounter = setInterval(function () {
            item.querySelector('span').innerText = counter;

            // Calculez la bordure en pourcentage
            const borderPercentage = (counter / targetValue) * 100;
            // Changez le fond avec conic-gradient
            item.style.backgroundImage = `conic-gradient(from ${90 + borderPercentage}deg, transparent 0%, transparent ${borderPercentage}%, #3498db ${borderPercentage}%, #3498db 100%)`;

            if (counter >= targetValue) {
                clearInterval(updateCounter);
            } else {
                counter++;
            }
        }, interval);
    });
});

