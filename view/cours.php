<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /* si l'utilisateur est connecter */
    if(!isset($_SESSION['use_cours_excel_987654321']))
    {
        header("location: connexion");
    }
?>
    <div class="corps_cours">
        <div class="entete_cours">
            <h1>Cours Excel</h1>
        </div>

        <div class="container">
            <div class="sidebar">
                <h2>Chapitres</h2>
                <div class="chapter js_video active">1. Introduction</div>
                <div class="chapter js_video">2. Niveau debutant</div>
                <div class="chapter js_video">3. Niveau intermédiaire</div>
                <div class="chapter js_video">4. Niveau expert</div>
                <div class="chapter js_video" data-quiz="ok">5. Quiz Final</div>
            </div>

            <div class="main">
                <div class="video-container js_vu_quiz">
                    <!-- form quiz -->
                    <div class="div_form_quiz js_vu_quiz">
                        <div class="titre_quiz">
                            Quiz
                        </div>
                        <div class="form_quiz">
                            <div class="form">
                                <div class="details_form">
                                    <input type="radio" name="question" id="question_1">
                                    <label for="question_1">Question 1</label>
                                </div>
                                <div class="details_form">
                                    <input type="radio" name="question" id="question_2">
                                    <label for="question_2">Question 2</label>
                                </div>
                                <div class="details_form">
                                    <input type="radio" name="question" id="question_3">
                                    <label for="question_3">Question 3</label>
                                </div>
                                <div class="details_form">
                                    <input type="radio" name="question" id="question_4">
                                    <label for="question_4">Question 4</label>
                                </div>
                                <div class="details_form">
                                    <input type="radio" name="question" id="question_5">
                                    <label for="question_5">Question 5</label>
                                </div>
                                <div class="details_form">
                                    <button>Valider</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Simule une vidéo -->
                    <div class="div_video js_vu_quiz">
                        <video width="100%" height="100%" controls id="video">
                        <source src="video.mp4" type="video/mp4"/>
                            Votre navigateur ne prend pas en charge les vidéos.
                        </video>
                    </div>
                </div>

                <div class="tabs">
                    <div class="tab active" onclick="showTab('content')">Cours</div>
                    <div class="tab" onclick="showTab('files')">Fichiers</div>
                </div>

                <div class="tab-content" id="content">
                    <h3>Contenu du cours</h3>
                    <p>Bienvenue dans ce module. Nous allons apprendre les bases de EXCEL, pour mieux gérer votre budget.</p>
                </div>

                <div class="tab-content" id="files" style="display:none;">
                    <h3>Fichiers à télécharger</h3>
                    <div class="div_lien_telechargement_fichier">
                        <a href="cours_excel.zip" download="cours-excel.zip" class="lien_telechargement_fichier">cours-excel.zip</a>
                    </div>
                    <div class="div_lien_telechargement_fichier">
                        <a href="Classeur1.xlsx" download="exercices-excel.xlsx" class="lien_telechargement_fichier">exercices-excel.xlsx</a>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function showTab(id) {
                document.querySelectorAll('.tab-content').forEach(div => div.style.display = 'none');
                document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
                document.getElementById(id).style.display = 'block';
                event.target.classList.add('active');
            }
            /* mettre video en pause */
            let
            video = document.getElementById("video");
            /* changer de chapitre */
            document.querySelectorAll(".js_video").forEach(function(element){
                element.addEventListener("click",function(){
                    /* enlever la mise en valeur des autres chapitres */
                    document.querySelectorAll(".js_video").forEach(function(element_){
                        element_.classList.remove('active');
                    })
                    element.classList.add('active');
                    video.pause();
                    video.currentTime = 0;
                    /* si on veut faire le quiz */
                    if(element.getAttribute("data-quiz"))
                    {
                        /* vu quiz */
                        document.querySelectorAll(".js_vu_quiz").forEach(function(element_){
                            element_.classList.add("active");
                        });
                    }
                    else
                    {
                        /* vu quiz */
                        document.querySelectorAll(".js_vu_quiz").forEach(function(element_){
                            element_.classList.remove("active");
                        });
                        video.play();
                    }
                })
            })
        </script>
    </div>
