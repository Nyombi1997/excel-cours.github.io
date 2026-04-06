<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['use_cours_excel_987654321']) && !isset($_SESSION['admin_cours_excel_987654321'])) {
        header("location: connexion");
        exit;
    }

    include_once ROOT . "fonctions/course_bootstrap.php";
    ensure_learning_schema($bdd);

    $userUniqueId = learning_get_current_user_unique_id();
    $catalogCourses = learning_get_courses($bdd, !isset($_SESSION['admin_cours_excel_987654321']));
    $useLearningCatalog = !empty($catalogCourses);
?>

<?php if ($useLearningCatalog): ?>
    <div class="learning-student-page">
        <section class="learning-student-hero">
            <div class="learning-student-hero__copy">
                <p class="learning-admin-kicker">Catalogue des cours</p>
                <h1>Choisissez un cours</h1>
                <p>Accédez d’abord aux cartes des cours, puis ouvrez le cours qui vous intéresse pour voir sa progression, ses vidéos et ses quiz.</p>
            </div>
        </section>

        <section class="learning-search-shell">
            <div class="learning-search-box">
                <label for="course_search_input" class="learning-search-label">Rechercher un cours</label>
                <div class="learning-search-input-wrap">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input
                        type="search"
                        id="course_search_input"
                        class="learning-search-input"
                        placeholder="Ex. tableau croisé, formules Excel, quiz..."
                        autocomplete="off"
                    >
                </div>
                <!-- <p class="learning-search-help">
                    Les suggestions apparaissent pendant la saisie, même si la recherche contient une faute ou un mot incomplet.
                </p> -->
                <div class="learning-search-results null" id="course_search_results"></div>
            </div>
        </section>

        <section class="learning-catalog-strip learning-catalog-grid" id="course_catalog_cards">
            <?php foreach ($catalogCourses as $course): ?>
                <?php $courseProgress = learning_get_course_progress($bdd, $userUniqueId, (int) $course['id']); ?>
                <a href="/details-cours?course=<?= urlencode($course['slug']) ?>" class="learning-catalog-card">
                    <div class="learning-catalog-card__top">
                        <span class="learning-catalog-badge">
                            <?= (int) ($course['is_published'] ?? 0) === 1 ? 'Disponible' : 'Brouillon' ?>
                        </span>
                        <span class="learning-catalog-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>

                    <div class="learning-catalog-card__body">
                        <strong><?= htmlspecialchars($course['title']) ?></strong>
                        <p><?= htmlspecialchars($course['short_description'] ?: 'Découvrir le parcours complet.') ?></p>
                    </div>

                    <div class="learning-catalog-card__footer">
                        <div class="learning-catalog-progress">
                            <span>Progression</span>
                            <strong><?= (int) $courseProgress['progress_percent'] ?>%</strong>
                        </div>
                        <div class="learning-catalog-progressbar">
                            <div class="learning-catalog-progressbar__fill" style="width: <?= (int) $courseProgress['progress_percent'] ?>%;"></div>
                        </div>
                        <small><?= (int) $courseProgress['completed_items'] ?> étape(s) validée(s) sur <?= (int) $courseProgress['total_items'] ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
        </section>
    </div>
    <script src="<?php echo ASSET; ?>js/course_search.js?<?= filemtime(ROOT."asset/js/course_search.js") ?>"></script>
<?php else: ?>
    <?php $cours = learning_get_legacy_courses($bdd); ?>
    <div class="corps_cours">
        <div class="entete_cours">
            <h1>Cours Excel</h1>
        </div>

        <div class="container">
            <div class="sidebar">
                <h2>Cour(s)</h2>
                <?php foreach ($cours as $index => $cour): ?>
                    <?php
                        $displayIndex = $index + 1;
                        $active = $displayIndex === 1 ? 'active' : '';
                        $dataQuiz = (isset($cour['quiz']) && (int) $cour['quiz'] === 1) ? 'data-quiz="ok"' : '';
                    ?>
                    <div class="chapter js_video <?= $active ?>" <?= $dataQuiz ?>>
                        <?= $displayIndex ?>. <?= htmlspecialchars($cour['titre']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="main">
                <div class="video-container js_vu_quiz">
                    <?php foreach ($cours as $index => $cour): ?>
                        <?php
                            $displayIndex = $index + 1;
                            $hiddenClass = $displayIndex === 1 ? '' : 'null';
                        ?>
                        <?php if (isset($cour['quiz']) && (int) $cour['quiz'] === 1): ?>
                            <div class="div_form_quiz js_vu_quiz <?= $hiddenClass ?>" id="js_vu_quiz">
                                <div class="titre_quiz">Quiz</div>
                                <div class="form_quiz">
                                    <div class="form">
                                        <div class="details_form">
                                            <input type="radio" name="question_legacy_<?= $displayIndex ?>" id="question_legacy_<?= $displayIndex ?>">
                                            <label for="question_legacy_<?= $displayIndex ?>">Question de démonstration</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="div_video js_vu_quiz <?= $hiddenClass ?>" id="js_vu_quiz">
                                <video width="100%" height="100%" controls id="video">
                                    <source src="<?= ASSET ?>videos/<?= rawurlencode($cour['lien_video']) ?>" type="video/mp4">
                                    Votre navigateur ne prend pas en charge les vidéos.
                                </video>
                            </div>
                            <div class="container_vu_video <?= $hiddenClass ?>" id="container_vu_video">
                                <div class="tabs">
                                    <div class="tab active js_tab_cours">Cours</div>
                                    <div class="tab js_tab_fichier">Fichiers</div>
                                </div>

                                <div class="tab-content js_cours_content" id="content">
                                    <h3>Contenu du cours</h3>
                                    <p><?= htmlspecialchars($cour['description'] !== '' ? $cour['description'] : 'Bon visionnage') ?></p>
                                </div>

                                <div class="tab-content null js_fichier_content" id="files">
                                    <?php if (!empty($cour['fichier'])): ?>
                                        <h3>Fichiers à télécharger</h3>
                                        <div class="div_lien_telechargement_fichier">
                                            <a href="<?= ASSET ?>fichier/<?= rawurlencode($cour['fichier']) ?>" download class="lien_telechargement_fichier">
                                                <?= htmlspecialchars($cour['fichier']) ?>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <h3>Aucun fichier pour ce cours</h3>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo ASSET; ?>js/script_cours.js?<?= filemtime(ROOT."asset/js/script_cours.js") ?>"></script>
<?php endif; ?>
