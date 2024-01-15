document.addEventListener("DOMContentLoaded", function () {
    const header = document.getElementById("header-home");
    const nav = document.querySelector(".nav-bar");
    const burger = document.querySelector(".burger");
    const burgerClose = document.querySelector(".burger-close");



     /*
        * COUNTER
     */
    const counters = document.querySelectorAll('.counter');
    const speed = 500; // vittesse de compte en ms 

    
    counters.forEach(counter => {
        const target = +counter.innerText;
        let currentCount = 0;

        const updateCounter = () => {
            const increment = target / speed;

            if (currentCount < target) {
                currentCount += increment;
                counter.innerText = Math.ceil(currentCount);
                setTimeout(updateCounter, 1);
            } else {
                counter.innerText = target;
            }
        };

        updateCounter();
    });

    /* 
        *Home page burger menu ----------------------------------------------------------
    */
    burger.addEventListener("click", () => {
        navStyleTop();
        nav.classList.toggle("nav-toggle");
        burger.style.opacity = 0;
        burgerClose.style.display = "block";
    });

    burgerClose.addEventListener("click", () => {
        nav.classList.remove("nav-toggle");
        burger.style.opacity = 1;
    });


    // Ajuster la position de l'overlay à la hauter du header
    function navStyleTop() {
        nav.style.top = header.clientHeight + "px";
    }

    /*
        * Smooth-scrool -------------------------------------------------
    */

     // Sélectionnez tous les liens à l'intérieur de la liste de défilement
     const scrollLinks = document.querySelectorAll('#smooth-scroll .scroll-items a');

     // Ajoutez un gestionnaire d'événements à chaque lien
     scrollLinks.forEach(link => {
         link.addEventListener('click', smoothScroll);
     });
     
 
    function smoothScroll(e) {
        e.preventDefault();

        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);

        // Obtenez la position du haut de l'élément cible
        const targetPosition = targetElement.offsetTop;

        // Définissez l'animation de défilement
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }
});

