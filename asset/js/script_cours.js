/* fonction changer de chapitre */
function changeChapter(element,index)
{
    element.addEventListener("click",function(){
        /* enlever la mise en valeur des autres chapitres */
        document.querySelectorAll(".js_video").forEach(function(element_){
            element_.classList.remove('active');
        })
        /* afficher le contenue de la page */
        document.querySelectorAll(".js_tab_cours").forEach(function(element_,index_){
            if(index == index_)
            {
                element_.classList.add("active");
            }
        })
        /* afficher le contenue de la page */
        document.querySelectorAll(".js_cours_content").forEach(function(element_,index_){
            if(index == index_)
            {
                element_.classList.remove("null");
            }
        })
        /* si c'est pas quiz */
        if(element.getAttribute("data-quiz") === null)
        {
            /* afficher le bon choix */
            document.querySelectorAll("#js_vu_quiz").forEach(function(element_,index_){
                if(index != index_)
                {
                    element_.classList.add("null");
                }
                else
                {
                    element_.classList.remove("null");
                }
            })
            /* afficher le bon container */
            document.querySelectorAll("#container_vu_video").forEach(function(element_,index_){
                if(index != index_)
                {
                    element_.classList.add("null");
                }
                else
                {
                    element_.classList.remove("null");
                }
            })
            /* trouver la vidéo et annuler */
            document.querySelectorAll("#video").forEach(function(element_,index_){
                if(index != index_)
                {
                    element_.pause();
                    element_.currentTime = 0;
                }
                else
                {
                    element_.play();
                    /* savoir le nombre de temps fait en lecture de la vidéo */
                    let maxTimeViewed = 0;

                    element_.addEventListener("timeupdate", () => {
                        maxTimeViewed = Math.max(maxTimeViewed, element_.currentTime);

                        const pourcentage = (maxTimeViewed / element_.duration) * 100;

                        if (pourcentage >= 90) {
                            //console.log("✅ Vidéo suivie à 90% minimum");
                        }
                    });
                }
            })
        }
        /* si c'est un quiz */
        else
        {

        }
        element.classList.add('active');
    })
}
/* changer de chapitre */
document.querySelectorAll(".js_video").forEach(function(element,index){
    changeChapter(element,index);
})

/* afficher les fichiers du cours */
document.querySelectorAll(".js_tab_fichier").forEach(function(element,index){
    element.addEventListener("click",function(){
        /* fermer tous les autres */
        document.querySelectorAll(".tab").forEach(function(element_,index_){
            element_.classList.remove("active");
        })
        /* fermer tous les autres */
        document.querySelectorAll(".tab-content").forEach(function(element_,index_){
            element_.classList.add("null");
        })
        /* afficher le bon element */
        document.querySelectorAll(".js_fichier_content").forEach(function(element_,index_){
            if(index == index_)
            {
                element_.classList.remove("null");
            }
        })
        element.classList.add("active");
    })             
})

/* afficher les elements du cours */
document.querySelectorAll(".js_tab_cours").forEach(function(element,index){
    element.addEventListener("click",function(){
        /* fermer tous les autres */
        document.querySelectorAll(".tab").forEach(function(element_,index_){
            element_.classList.remove("active");
        })
        /* fermer tous les autres */
        document.querySelectorAll(".tab-content").forEach(function(element_,index_){
            element_.classList.add("null");
        })
        /* afficher le bon element */
        document.querySelectorAll(".js_cours_content").forEach(function(element_,index_){
            if(index == index_)
            {
                element_.classList.remove("null");
            }
        })
        element.classList.add("active");
    })             
})

/* lire l'évolution des lectures de videos */
document.querySelectorAll("#video").forEach(function(element,index){
    element.addEventListener("timeupdate", () => {
        let atteint100 = false;

        if (!element.duration) return;

        const pourcentage = (element.currentTime / element.duration) * 100;
        //progression.textContent = pourcentage.toFixed(2) + "%";
        let nombre = parseInt(index)+1;
        if (pourcentage == 100 && !atteint100) {
            atteint100 = true;
            //console.log("🎉 100% de la vidéo atteints cool"+index);
            if(nombre < document.querySelectorAll("#video").length)
            {
                document.querySelectorAll(".js_video").forEach(function(element_,index_){
                    if(nombre == index_)
                    {
                        element_.click();
                    }
                })
            }
            // ici tu peux envoyer une requête AJAX / fetch vers ton serveur
        }
    });
})