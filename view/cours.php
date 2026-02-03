<?php
    // Démarrer la session uniquement si elle n'est pas déjà active
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    /* si l'utilisateur est connecter */
    if(!isset($_SESSION['use_cours_excel_987654321']) && !isset($_SESSION['admin_cours_excel_987654321']))
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
                <h2>Cour(s)</h2>
                <?php
                    $cours = select_bdd($bdd, "cours", $where = null, $limit = null, $offset = 0, $order = 'position', $random = false);
                    foreach($cours as $index => $cour)
                    {
                        $index = $index + 1;
                        $active = 'active';
                        $data_quiz = 'data-quiz="ok"';
                        if($index!=1)
                        {
                            $active = '';
                        }
                        if($cour['quiz']==0)
                        {
                            $data_quiz = '';
                        }
                        echo '
                            <div class="chapter js_video '.$active.'" '.$data_quiz.'>'.$index.'. '.$cour['titre'].'</div>';
                    }
                ?>
            </div>

            <div class="main">
                <div class="video-container js_vu_quiz">
                    <?php
                        foreach($cours as $index => $cour)
                        {
                            $index = $index + 1;
                            $null = 'null';
                            if($index == 1)
                            {
                                $null = '';
                            }
                            if($cour['quiz']==1)
                            {
                                echo '
                                    <!-- form quiz -->
                                    <div class="div_form_quiz js_vu_quiz '.$null.'" id="js_vu_quiz">
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
                                    </div>';
                            }
                            else
                            {
                                echo '
                                <!-- Simule une vidéo -->
                                <div class="div_video js_vu_quiz '.$null.'" id="js_vu_quiz">
                                    <video width="100%" height="100%" controls id="video">
                                    <source src="'.ASSET.'videos/'.$cour['lien_video'].'" type="video/mp4"/>
                                        Votre navigateur ne prend pas en charge les vidéos.
                                    </video>
                                </div>
                                <div class="container_vu_video '.$null.'" id="container_vu_video">
                                    <div class="tabs">
                                        <div class="tab active js_tab_cours">Cours</div>
                                        <div class="tab js_tab_fichier">Fichiers</div>
                                    </div>

                                    <div class="tab-content js_cours_content" id="content">
                                        <h3>Contenu du cours</h3>
                                        <p>'.($cour['description'] != '' ? $cour['description'] : 'Bon visionnage').'</p>
                                    </div>

                                    <div class="tab-content null js_fichier_content" id="files">
                                        '.(
                                            $cour['fichier'] != '' ?
                                            '<h3>Fichiers à télécharger</h3>
                                            <div class="div_lien_telechargement_fichier">
                                                <a href="'.ASSET.'fichier/'.$cour['fichier'].'" download="'.ASSET.'fichier/'.$cour['fichier'].'" class="lien_telechargement_fichier">'.$cour['fichier'].'</a>
                                            </div>' :
                                            '<h3>Aucun fichier pour ce cours</h3>'
                                        ).'
                                    </div>
                                </div>';
                            }
                        }
                    ?>
                </div>

                
            </div>
        </div>
        <!-- scrip cours -->
        <script src="<?php echo ASSET; ?>js/script_cours.js?<?= filemtime(ROOT."asset/js/script_cours.js") ?>"></script>

    </div>
