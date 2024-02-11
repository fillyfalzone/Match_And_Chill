document.addEventListener("DOMContentLoaded", function () {
    const slide = document.getElementById("slide");
    const profile = document.getElementById("profil");

    if(slide && profile) {
        slide.addEventListener("click", function () {
       
            // La méthode toggle renvoie `true` si la classe a été ajoutée, et `false` si elle a été retirée.
            const isOpened = slide.classList.toggle("slide-open");
            const isClosed = !isOpened; 
    
            if (isOpened) {
                profile.style.display = "block"; 
                slide.style.left = "170px";
            } else {
                profile.style.display = "none"; 
                slide.style.left = "0px";
            }
    
        });
    }

    
});
window.addEventListener("resize", function () {
    const slide = document.getElementById("slide"); // Assurez-vous que 'slide' est bien défini ici
    const profile = document.getElementById("profil");
    // Si la taille de la fenêtre est inférieure à 800px
    if (window.innerWidth < 800) {
        // On cache le menu
        slide.classList.remove("slide-open");
        slide.style.left = "0px";
        profile.style.display = "none"; 
    } else {
        profile.style.display = "block"; 
    }
});


