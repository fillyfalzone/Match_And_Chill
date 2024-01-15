document.addEventListener("DOMContentLoaded", function () {
    // Sélection des éléments du DOM
    const header = document.getElementById("header-home");
    const nav = document.querySelector(".nav-bar");
    const burger = document.querySelector(".burger");
    const burgerClose = document.querySelector(".burger-close");
    var sections = document.querySelectorAll('.fade-in-section');
    const links = document.querySelectorAll('.nav-link');
    const icons = document.querySelectorAll('.iconify');

    /*
     * COUNTER
     */
    const counters = document.querySelectorAll('.counter');
    const speed = 500; // Vitesse de comptage en ms 

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
     * Home page burger menu ----------------------------------------------------------
     */
    burger.addEventListener("click", () => {
        navStyleTop();
        nav.classList.toggle("nav-toggle");
        burger.style.opacity = 0;
        burgerClose.style.display = "block";
    });

    // Ajout de l'écouteur d'événement au clic pour le bouton burgerClose
    burgerClose.addEventListener("click", () => {
        nav.classList.remove("nav-toggle");
        burger.style.opacity = 1;
        burgerClose.style.display = "none"; // Masque le bouton burgerClose
    });

    // Ajuster la position de l'overlay à la hauteur du header
    function navStyleTop() {
        nav.style.top = header.clientHeight + "px";
    }

    /*
     * Smooth-scroll -------------------------------------------------
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

        // Obtient la position du haut de l'élément cible
        const targetPosition = targetElement.offsetTop;

        // Définit l'animation de défilement
        window.scrollTo({
            top: targetPosition,
            behavior: 'smooth'
        });
    }

    /*
     * Scroll Hijacking ---------------------------------------------- 
     */

    // Variable pour suivre l'index de la section actuelle
    let currentSection = 0;
    // Nombre total de sections dans la page
    const totalSections = sections.length;
    // Variable pour empêcher le déclenchement d'animations multiples pendant le défilement continu
    let isScrolling = false;

    // Ajoute un écouteur d'événement pour le défilement de la souris
    document.addEventListener('wheel', handleScroll);

    // Fonction pour gérer le défilement
    function handleScroll(event) {
        // Vérifie si l'animation de défilement est déjà en cours
        if (!isScrolling) {
            // Définit isScrolling sur true pour empêcher les animations multiples
            isScrolling = true;

            // Détermine la direction du défilement et ajuste l'index de la section actuelle
            if (event.deltaY > 0 && currentSection < totalSections - 1) {
                currentSection++;
            } else if (event.deltaY < 0 && currentSection > 0) {
                currentSection--;
            }

            // Appelle la fonction pour effectuer l'animation de défilement
            scrollToSection(currentSection);
        }
    }

    // Ajout d'un gestionnaire d'événement scroll pour mettre à jour le lien actif lors du défilement
    window.addEventListener('scroll', function () {
        updateActiveLink();
    });

    // Ajout d'un écouteur d'événement au clic sur les liens dans la balise 'aside'
    links.forEach((link, index) => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Empêche le comportement par défaut du lien
            setActiveLink(index);
            scrollToSection(index);
        });
    });

    // Fonction pour effectuer une animation de défilement fluide vers une section donnée
    function scrollToSection(index) {
        // Calcule la position de défilement cible pour la section spécifiée
        const targetScrollTop = sections[index].offsetTop;

        // Appelle la fonction pour animer le défilement
        animateScroll(document.documentElement, targetScrollTop, 600, function () {
            // Réinitialise isScrolling à false une fois l'animation terminée
            isScrolling = false;
        });
    }

    // Fonction pour animer le défilement
    function animateScroll(element, targetScrollTop, duration, callback) {
        // Récupère la position de défilement actuelle
        const startScrollTop = element.scrollTop;
        // Récupère le temps de début de l'animation
        const startTime = performance.now();

        // Fonction récursive pour mettre à jour la progression de l'animation
        function updateScrollProgress() {
            // Récupère le temps actuel
            const currentTime = performance.now();
            // Calcule la progression de l'animation
            const progress = Math.min((currentTime - startTime) / duration, 1);
            // Met à jour la position de défilement en fonction de la progression
            element.scrollTop = startScrollTop + progress * (targetScrollTop - startScrollTop);

            // Vérifie si l'animation est terminée
            if (progress < 1) {
                // Demande une nouvelle animation de trame
                requestAnimationFrame(updateScrollProgress);
            } else {
                // Si la fonction de rappel est définie, l'appelle une fois l'animation terminée
                if (callback) {
                    callback();
                }
            }
        }

        // Démarre l'animation en appelant la fonction récursive
        requestAnimationFrame(updateScrollProgress);
    }

    // Fonction pour définir le lien actif
    function setActiveLink(index) {
        links.forEach((link, i) => {
            if (i === index) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
        // Active également les icônes
        icons.forEach((icon, i) => {
            if (i === index) {
                icon.classList.add('active');
            } else {
                icon.classList.remove('active');
            }
        });
    }

    // Fonction pour mettre à jour le lien actif en fonction de la section actuellement visible à l'écran
    function updateActiveLink() {
        sections.forEach((section, index) => {
            const rect = section.getBoundingClientRect();
            const link = links[index];
            const icon = icons[index];

            // Vérifie si la section est visible dans la fenêtre
            if (rect.top <= window.innerHeight / 2 && rect.bottom >= window.innerHeight / 2) {
                setActiveLink(index);
            }
        });
    }

    /*
     * EASY LOADING DES SECTIONS
     */

    var fadeIn = function () {
        sections.forEach(function (section) {
            var rect = section.getBoundingClientRect();
            if (rect.top < window.innerHeight && rect.bottom >= 0) {
                section.classList.add('fade-in');
            }
        });
    };

    // Écoutez l'événement de défilement, de redimensionnement et d'orientation
    window.addEventListener('scroll', fadeIn);
    window.addEventListener('resize', fadeIn);
    window.addEventListener('orientationchange', fadeIn);

    // Appliquez le fondu aux sections initialement visibles
    fadeIn();

    // Ajouter une vérification pour désactiver le scroll hijacking sur les appareils tactiles et les petits écrans
    if (window.innerWidth <= 1030) {
        disableScrollHijacking();
    }

    // Fonction pour détecter si l'appareil est tactile
    function isTouchDevice() {
        return 'ontouchstart' in window || navigator.maxTouchPoints;
    }

    // Fonction pour désactiver le scroll hijacking
    function disableScrollHijacking() {
        // Supprimer l'écouteur d'événement de la souris pour le scroll hijacking
        document.removeEventListener('wheel', handleScroll);
        // Activer le défilement natif
        document.body.style.overflow = 'auto';
    }
    // Ajoutez un gestionnaire d'événements pour l'événement resize
    window.addEventListener('resize', function () {
        // Vérifiez la largeur de la fenêtre et désactivez le scroll hijacking si elle est inférieure à 1030
        if (window.innerWidth <= 1030) {
            disableScrollHijacking();
        } else {
            // Réactivez le scroll hijacking si la largeur de la fenêtre est supérieure à 1030
            document.addEventListener('wheel', handleScroll);
            document.body.style.overflow = 'hidden';
        }
    
    
        // Appelez la fonction pour mettre à jour les liens actifs lors du redimensionnement
        updateActiveLink();
    });
});

