<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['admin_cours_excel_987654321'])) {
        header("location: connexion");
        exit;
    }

    include_once ROOT . "fonctions/course_bootstrap.php";
    ensure_learning_schema($bdd);

    $course = learning_get_course($bdd, $_GET['course'] ?? null, false);
    if (!$course) {
        header("location: /gestion-cours");
        exit;
    }

    $sectionId = isset($_GET['section']) ? (int) $_GET['section'] : 0;
    $section = null;
    $items = [];

    if ($sectionId > 0) {
        $curriculum = learning_get_course_curriculum($bdd, (int) $course['id']);
        foreach ($curriculum['sections'] as $sectionRow) {
            if ((int) $sectionRow['id'] === $sectionId) {
                $section = $sectionRow;
                $items = $sectionRow['items'];
                break;
            }
        }
    }
?>

<div class="learning-admin-page">
    <div class="learning-admin-hero">
        <div>
            <p class="learning-admin-kicker">Admin formation</p>
            <h1><?= $section ? 'Gérer un chapitre' : 'Créer un chapitre' ?></h1>
            <p class="learning-admin-lead">
                <?= $section
                    ? "Depuis cette page, tu modifies le chapitre et tu ranges ses vidéos et ses quiz par glisser-déposer."
                    : "Crée d'abord le chapitre. Une fois enregistré, tu pourras y ajouter des vidéos de cours et des quiz." ?>
            </p>
        </div>
        <div class="learning-admin-hero-actions">
            <a href="/edition-cours?course=<?= urlencode($course['slug']) ?>" class="learning-admin-btn secondary">Retour au cours</a>
        </div>
    </div>

    <section class="learning-panel">
        <div class="learning-panel-header">
            <h2><?= $section ? 'Informations du chapitre' : 'Nouveau chapitre' ?></h2>
        </div>

        <form id="js_section_form" class="learning-admin-form">
            <input type="hidden" name="action" value="save_section">
            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
            <input type="hidden" name="section_id" value="<?= $section ? (int) $section['id'] : 0 ?>">

            <label class="learning-field">
                <span>Titre du chapitre</span>
                <input type="text" name="title" value="<?= htmlspecialchars($section['title'] ?? '') ?>" placeholder="Ex. Chapitre 1 : Les bases">
            </label>

            <label class="learning-field">
                <span>Description</span>
                <textarea name="description" rows="4" placeholder="Décris ce que l’élève va apprendre dans ce chapitre."><?= htmlspecialchars($section['description'] ?? '') ?></textarea>
            </label>

            <div class="learning-form-actions">
                <button type="submit" class="learning-admin-btn primary">
                    <?= $section ? 'Enregistrer le chapitre' : 'Créer le chapitre' ?>
                </button>
            </div>
        </form>
    </section>

    <?php if ($section): ?>
        <section class="learning-panel">
            <div class="learning-panel-header">
                <h2>Contenus du chapitre</h2>
                <div class="learning-panel-header-actions">
                    <button type="button" class="learning-admin-btn secondary js_open_item_modal"
                        data-mode="create"
                        data-item-type="video"
                        data-course-id="<?= (int) $course['id'] ?>"
                        data-section-id="<?= (int) $section['id'] ?>">
                        Ajouter une vidéo
                    </button>
                    <button type="button" class="learning-admin-btn secondary js_open_item_modal"
                        data-mode="create"
                        data-item-type="quiz"
                        data-course-id="<?= (int) $course['id'] ?>"
                        data-section-id="<?= (int) $section['id'] ?>">
                        Ajouter un quiz
                    </button>
                </div>
            </div>

            <p class="learning-help">Ici tu peux mélanger vidéos et quiz dans le même chapitre, puis les classer comme tu veux.</p>

            <div class="learning-items-sortable" data-section-id="<?= (int) $section['id'] ?>" id="js_section_items_sortable">
                <?php if (empty($items)): ?>
                    <div class="learning-empty-inline">Aucun contenu dans ce chapitre pour le moment.</div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <?php $itemPayload = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>
                        <div class="learning-item-card" data-item-id="<?= (int) $item['id'] ?>" draggable="true">
                            <div class="learning-item-card__main">
                                <span class="learning-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                                <div>
                                    <strong><?= htmlspecialchars($item['title']) ?></strong>
                                    <p><?= $item['item_type'] === 'quiz' ? 'Quiz' : 'Vidéo de cours' ?></p>
                                </div>
                            </div>
                            <div class="learning-item-card__actions">
                                <button type="button" class="learning-icon-btn js_open_item_modal"
                                    data-mode="edit"
                                    data-item-type="<?= htmlspecialchars($item['item_type']) ?>"
                                    data-course-id="<?= (int) $course['id'] ?>"
                                    data-section-id="<?= (int) $section['id'] ?>"
                                    data-item-payload="<?= $itemPayload ?>">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" class="learning-icon-btn js_delete_item" data-item-id="<?= (int) $item['id'] ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <div class="container_popup learning-modal-wrapper" id="js_item_modal_wrapper">
            <div class="background" id="js_item_modal_close"></div>
            <div class="learning-modal-card" id="js_item_modal">
                <div class="learning-modal-head">
                    <h2 id="js_item_modal_title">Ajouter un contenu</h2>
                    <button type="button" class="learning-icon-btn" id="js_close_item_modal">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>

                <form id="js_item_form" class="learning-admin-form" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="save_item">
                    <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
                    <input type="hidden" name="item_id" id="js_item_id" value="0">
                    <input type="hidden" name="item_type" id="js_item_type" value="video">
                    <input type="hidden" name="is_final_quiz" id="js_item_is_final_quiz" value="0">
                    <input type="hidden" name="existing_video_file" id="js_existing_video_file" value="">
                    <input type="hidden" name="existing_attachment_file" id="js_existing_attachment_file" value="">
                    <input type="hidden" name="quiz_payload" id="js_quiz_payload" value="[]">

                    <div class="learning-grid two">
                        <label class="learning-field">
                            <span>Titre</span>
                            <input type="text" name="title" id="js_item_title" placeholder="Titre du contenu">
                        </label>

                        <label class="learning-field">
                            <span>Chapitre</span>
                            <select name="section_id" id="js_item_section">
                                <option value="<?= (int) $section['id'] ?>"><?= htmlspecialchars($section['title']) ?></option>
                            </select>
                        </label>
                    </div>

                    <label class="learning-field">
                        <span>Description</span>
                        <textarea name="description" id="js_item_description" rows="4"></textarea>
                    </label>

                    <div class="learning-grid two">
                        <label class="learning-field">
                            <span>Durée affichée</span>
                            <input type="text" name="duration_label" id="js_item_duration_label" placeholder="Ex. 12 min">
                        </label>

                        <label class="learning-field" id="js_item_passing_score_wrap">
                            <span>Score minimum du quiz</span>
                            <input type="number" name="passing_score" id="js_item_passing_score" min="0" max="100" value="70">
                        </label>
                    </div>

                    <div class="learning-grid two">
                        <label class="learning-toggle">
                            <input type="checkbox" name="is_preview" value="1" id="js_item_is_preview">
                            <span>Contenu visible en aperçu</span>
                        </label>

                        <label class="learning-toggle">
                            <input type="checkbox" name="is_required" value="1" id="js_item_is_required" checked>
                            <span>Compter dans la progression</span>
                        </label>
                    </div>

                    <div id="js_video_fields">
                        <div class="learning-grid two">
                            <label class="learning-field">
                                <span>Fichier vidéo</span>
                                <input type="file" name="video_file" id="js_item_video_file" accept="video/*">
                                <small class="learning-help" id="js_video_current_file"></small>
                            </label>

                            <label class="learning-field">
                                <span>Fichier à joindre</span>
                                <input type="file" name="attachment_file" id="js_item_attachment_file" accept="*">
                                <small class="learning-help" id="js_attachment_current_file"></small>
                            </label>
                        </div>

                        <div class="learning-video-preview-wrap null" id="js_admin_video_preview_wrap">
                            <span class="learning-field-title">Aperçu de la vidéo</span>
                            <video controls id="js_admin_video_preview" class="learning-admin-video-preview"></video>
                        </div>
                    </div>

                    <div id="js_quiz_fields" class="null">
                        <div class="learning-quiz-head">
                            <div>
                                <h3>Questions du quiz</h3>
                                <p>Ajoute autant de questions et de réponses que nécessaire, puis coche la bonne réponse.</p>
                            </div>
                            <button type="button" class="learning-admin-btn secondary" id="js_add_question">Ajouter une question</button>
                        </div>

                        <div id="js_questions_builder" class="learning-questions-builder"></div>
                    </div>

                    <div class="learning-form-actions">
                        <button type="submit" class="learning-admin-btn primary" id="js_item_submit">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    window.learningAdminPage = {
        page: 'section-manage',
        courseId: <?= (int) $course['id'] ?>,
        courseSlug: <?= json_encode($course['slug'], JSON_UNESCAPED_UNICODE) ?>,
        sectionId: <?= $section ? (int) $section['id'] : 0 ?>,
        sectionTitle: <?= json_encode($section['title'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        assetBase: <?= json_encode(ASSET, JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="<?php echo ASSET; ?>js/manage_quiz.js?<?= filemtime(ROOT."asset/js/manage_quiz.js") ?>"></script>
