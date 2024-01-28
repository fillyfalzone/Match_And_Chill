document.addEventListener("DOMContentLoaded", function () { 
    // Modification du contenu du card body
    const discussion = document.getElementById("card-discussion");
    const classement = document.getElementById("card-classement");
    const events = document.getElementById("card-events");

    // les btn de la match-nav

    const btnDiscussion = document.getElementById("btn-discussion");
    const btnClassement = document.getElementById("btn-classement");
    const btnEvents = document.getElementById("btn-events");

    // afficher les discussions
    btnDiscussion.addEventListener("click", function () {
        discussion.classList.remove("display-none");
        classement.classList.add("display-none");
        events.classList.add("display-none");
    });

    // afficher les classements
    btnClassement.addEventListener("click", function () {
        discussion.classList.add("display-none");
        classement.classList.remove("display-none");
        events.classList.add("display-none");
    });

    // afficher les events
    btnEvents.addEventListener("click", function () {
        discussion.classList.add("display-none");
        classement.classList.add("display-none");
        events.classList.remove("display-none");
    });
    
    


 
})