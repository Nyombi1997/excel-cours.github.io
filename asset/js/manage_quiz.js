/* afficher l'ajout du quiz */
let 
add_video = document.getElementById("add_video"),
container_popup = document.getElementById("container_popup"),
video_contents = document.getElementById("video_contents"),
quiz_contents = document.getElementById("quiz_contents"),
background = document.getElementById("background"),
enregistrer_video = document.getElementById("enregistrer_video"),
titre_video = document.getElementById("titre_video"),
description_video = document.getElementById("description_video");

add_video.addEventListener("click",function(){
    container_popup.classList.add("active");
    video_contents.classList.add("active");
    quiz_contents.classList.remove("active");
    titre_video.scrollIntoView({
        behavior: 'smooth',
        block: 'center',
        inline: 'nearest'
    });
})
/* masquer le popup */
background.addEventListener("click",function(){
    container_popup.classList.remove("active");
    video_contents.classList.remove("active");
    quiz_contents.classList.remove("active");
})
/* si on enregistre la video de cours */
enregistrer_video.addEventListener("click",function(){
    if(titre_video.value.replace(/ +/g,"")=="")
    {
            Swal.fire({
                icon: "error",
                title: "Ecrivez un titre",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: '#4caf50'
            })
        titre_video.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
        });
    }
    else
    {
        uploadVideo();
    }
})

function uploadVideo() {
    const fileInput = document.getElementById("video");
    const percentText = document.getElementById("progress");
    const fichier_video = document.getElementById("fichier_video");

    if (!fileInput.files.length) {
            Swal.fire({
            icon: "error",
            title: "Choisissez une video",
            text: "",
            confirmButtonText: "OK",
            confirmButtonColor: site_color,
            timer: 1000
        })
        return;
    }

    const formData = new FormData();
    formData.append("titre", titre_video.value.trim());
    formData.append("video", fileInput.files[0]);
    formData.append("description", description_video.value.trim());
    
    if (fichier_video.files.length) {
        formData.append("fichier", fichier_video.files[0]);
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/fonctions/upload.php", true);

    // 🔄 progression
    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            percentText.classList.remove("null");
            const percent = Math.round((e.loaded / e.total) * 100);
            percentText.textContent = percent + "%";
        }
    };

    xhr.onload = function () {
        if (xhr.status === 200) {
            percentText.textContent = "Upload terminé ✅";
            Swal.fire({
                icon: "success",
                title: "La video a été ajouté avec succès !",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: site_color,
                timer: 1000,
                iconColor: site_color
            }).then(() => {
                window.location = "/cours";
            });
            container_popup.classList.remove("active");
            video_contents.classList.remove("active");
            quiz_contents.classList.remove("active");
            percentText.textContent = "";
            percentText.classList.remove("null");
            fileInput.value = '';
            titre_video.value = '';
            description_video.value = '';
            fichier_video.value = '';
        }
        else if (xhr.status === 500) {
                Swal.fire({
                    icon: "error",
                    title: "La video utilise trop de bande passante",
                    text: "",
                    confirmButtonText: "OK",
                    confirmButtonColor: site_color,
                    timer: 1000
                })
            percentText.textContent = "Erreur ❌";
        }
        else if (xhr.status === 400) {
                Swal.fire({
                icon: "error",
                title: "Aucun fichier charger",
                text: "",
                confirmButtonText: "OK",
                confirmButtonColor: site_color,
                timer: 1000
            })
            percentText.textContent = "Erreur ❌";
        } else {
            percentText.textContent = "Erreur ❌";
        }
    };

    xhr.send(formData);
}