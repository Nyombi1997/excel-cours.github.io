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

    $allCourses = learning_get_courses($bdd, false);
    $selectedCourse = learning_get_course($bdd, $_GET['course'] ?? null, false);

    if (!$selectedCourse && !empty($allCourses)) {
        $selectedCourse = $allCourses[0];
    }

    $selectedCourseId = $selectedCourse ? (int) $selectedCourse['id'] : 0;
    $curriculum = $selectedCourseId > 0
        ? learning_get_course_curriculum($bdd, $selectedCourseId)
        : ['sections' => [], 'final_items' => []];
?>

<div class="learning-admin-page">
    <div class="learning-admin-hero">
        <div>
            <p class="learning-admin-kicker">Gestion des cours</p>
            <h1>Construis un espace de formation dynamique</h1>
            <p class="learning-admin-lead">
                Crée plusieurs cours, organise-les par chapitres, ajoute des vidéos et des quiz,
                puis réordonne l'ensemble en glisser-déposer.
            </p>
        </div>
        <div class="learning-admin-hero-actions">
            <button type="button" class="learning-admin-btn primary" id="js_create_course">
                Nouveau cours
            </button>
            <a href="/details-cours<?= $selectedCourse ? '?course=' . urlencode($selectedCourse['slug']) : '' ?>" class="learning-admin-btn secondary">
                Voir l'espace cours
            </a>
        </div>
    </div>

    <div class="learning-admin-layout">
        <aside class="learning-admin-sidebar">
            <div class="learning-panel-header">
                <h2>Catalogue</h2>
                <span><?= count($allCourses) ?> cours</span>
            </div>

            <div class="learning-course-sortable" id="js_course_sortable">
                <?php foreach ($allCourses as $course): ?>
                    <a
                        href="/gestion-cours?course=<?= urlencode($course['slug']) ?>"
                        class="learning-course-card<?= $selectedCourseId === (int) $course['id'] ? ' active' : '' ?>"
                        data-course-id="<?= (int) $course['id'] ?>"
                        draggable="true"
                    >
                        <span class="learning-course-card__drag">
                            <i class="fa-solid fa-grip-vertical"></i>
                        </span>
                        <span class="learning-course-card__content">
                            <strong><?= htmlspecialchars($course['title'] ?: 'Cours sans titre') ?></strong>
                            <small>
                                <?= (int) $course['total_sections'] ?> chapitres · <?= (int) $course['total_items'] ?> contenus
                            </small>
                        </span>
                        <span class="learning-course-card__status <?= (int) $course['is_published'] === 1 ? 'published' : 'draft' ?>">
                            <?= (int) $course['is_published'] === 1 ? 'Publié' : 'Brouillon' ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="learning-admin-main">
            <?php if (!$selectedCourse): ?>
                <section class="learning-panel learning-empty-panel">
                    <h2>Aucun cours pour le moment</h2>
                    <p>Commence par créer un premier cours pour activer la gestion des chapitres, des vidéos et des quiz.</p>
                </section>
            <?php else: ?>
                <section class="learning-panel">
                    <div class="learning-panel-header">
                        <h2>Détails du cours</h2>
                        <div class="learning-panel-header-actions">
                            <button type="button" class="learning-admin-btn danger js_delete_course" data-course-id="<?= $selectedCourseId ?>">
                                Supprimer ce cours
                            </button>
                        </div>
                    </div>

                    <form id="js_course_form" class="learning-admin-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="save_course">
                        <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
                        <input type="hidden" name="existing_certificate_file" value="<?= htmlspecialchars($selectedCourse['certificate_file'] ?? '') ?>">

                        <div class="learning-grid two">
                            <label class="learning-field">
                                <span>Titre du cours</span>
                                <input type="text" name="title" value="<?= htmlspecialchars($selectedCourse['title']) ?>" placeholder="Ex. Excel avancé pour les chantiers">
                            </label>

                            <label class="learning-field">
                                <span>Score minimum du cours</span>
                                <input type="number" name="passing_score" min="0" max="100" value="<?= (int) ($selectedCourse['passing_score'] ?? 70) ?>">
                            </label>
                        </div>

                        <label class="learning-field">
                            <span>Accroche courte</span>
                            <textarea name="short_description" rows="3" placeholder="Présente rapidement la promesse du cours."><?= htmlspecialchars($selectedCourse['short_description'] ?? '') ?></textarea>
                        </label>

                        <label class="learning-field">
                            <span>Description complète</span>
                            <textarea name="description" rows="5" placeholder="Décris le programme, le niveau visé et les bénéfices."><?= htmlspecialchars($selectedCourse['description'] ?? '') ?></textarea>
                        </label>

                        <div class="learning-grid two">
                            <label class="learning-toggle">
                                <input type="checkbox" name="is_published" value="1" <?= (int) ($selectedCourse['is_published'] ?? 0) === 1 ? 'checked' : '' ?>>
                                <span>Publier ce cours</span>
                            </label>

                            <label class="learning-toggle">
                                <input type="checkbox" name="certificate_enabled" value="1" <?= (int) ($selectedCourse['certificate_enabled'] ?? 0) === 1 ? 'checked' : '' ?>>
                                <span>Activer un certificat facultatif</span>
                            </label>
                        </div>

                        <label class="learning-field">
                            <span>Fichier certificat</span>
                            <input type="file" name="certificate_file" accept=".pdf,.png,.jpg,.jpeg,.webp">
                            <?php if (!empty($selectedCourse['certificate_file'])): ?>
                                <small class="learning-help">
                                    Fichier actuel :
                                    <a href="<?= ASSET ?>fichier/<?= rawurlencode($selectedCourse['certificate_file']) ?>" target="_blank">
                                        <?= htmlspecialchars($selectedCourse['certificate_file']) ?>
                                    </a>
                                </small>
                            <?php endif; ?>
                        </label>

                        <div class="learning-form-actions">
                            <button type="submit" class="learning-admin-btn primary">Enregistrer le cours</button>
                        </div>
                    </form>
                </section>

                <section class="learning-panel">
                    <div class="learning-panel-header">
                        <h2>Organisation du contenu</h2>
                        <div class="learning-panel-header-actions">
                            <button type="button" class="learning-admin-btn secondary" id="js_add_section" data-course-id="<?= $selectedCourseId ?>">
                                Ajouter un chapitre
                            </button>
                            <button type="button" class="learning-admin-btn secondary" id="js_add_final_quiz" data-course-id="<?= $selectedCourseId ?>">
                                Ajouter un quiz final
                            </button>
                        </div>
                    </div>

                    <p class="learning-help">
                        Fais glisser les chapitres et les contenus pour changer leur classement. Le même ordre sera visible ensuite dans la page `cours`.
                    </p>

                    <div class="learning-sections-sortable" id="js_sections_sortable">
                        <?php foreach ($curriculum['sections'] as $section): ?>
                            <article class="learning-section-card" data-section-id="<?= (int) $section['id'] ?>" draggable="true">
                                <div class="learning-section-head">
                                    <div class="learning-section-title">
                                        <span class="learning-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                                        <div>
                                            <h3><?= htmlspecialchars($section['title']) ?></h3>
                                            <p><?= htmlspecialchars($section['description'] ?: "Aucune description de chapitre.") ?></p>
                                        </div>
                                    </div>
                                    <div class="learning-section-actions">
                                        <button type="button" class="learning-icon-btn js_edit_section"
                                            data-section-id="<?= (int) $section['id'] ?>"
                                            data-title="<?= htmlspecialchars($section['title'], ENT_QUOTES) ?>"
                                            data-description="<?= htmlspecialchars($section['description'] ?? '', ENT_QUOTES) ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="learning-icon-btn js_delete_section" data-section-id="<?= (int) $section['id'] ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="learning-section-toolbar">
                                    <button type="button" class="learning-admin-btn secondary js_open_item_modal"
                                        data-mode="create"
                                        data-item-type="video"
                                        data-course-id="<?= $selectedCourseId ?>"
                                        data-section-id="<?= (int) $section['id'] ?>">
                                        Ajouter une vidéo
                                    </button>
                                    <button type="button" class="learning-admin-btn secondary js_open_item_modal"
                                        data-mode="create"
                                        data-item-type="quiz"
                                        data-course-id="<?= $selectedCourseId ?>"
                                        data-section-id="<?= (int) $section['id'] ?>">
                                        Ajouter un quiz
                                    </button>
                                </div>

                                <div class="learning-items-sortable" data-section-id="<?= (int) $section['id'] ?>">
                                    <?php if (empty($section['items'])): ?>
                                        <div class="learning-empty-inline">
                                            Aucun contenu dans ce chapitre pour le moment.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($section['items'] as $item): ?>
                                            <?php $itemPayload = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>
                                            <div class="learning-item-card" data-item-id="<?= (int) $item['id'] ?>" draggable="true">
                                                <div class="learning-item-card__main">
                                                    <span class="learning-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                                                    <div>
                                                        <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                        <p>
                                                            <?= (string) $item['item_type'] === 'quiz' ? 'Quiz' : 'Vidéo' ?>
                                                            <?php if (!empty($item['duration_label'])): ?>
                                                                · <?= htmlspecialchars($item['duration_label']) ?>
                                                            <?php endif; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="learning-item-card__actions">
                                                    <button type="button" class="learning-icon-btn js_open_item_modal"
                                                        data-mode="edit"
                                                        data-item-type="<?= htmlspecialchars($item['item_type']) ?>"
                                                        data-course-id="<?= $selectedCourseId ?>"
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
                            </article>
                        <?php endforeach; ?>
                    </div>

                    <div class="learning-final-zone">
                        <div class="learning-final-zone__head">
                            <h3>Évaluation finale</h3>
                            <p>Tu peux laisser cette zone vide si le cours n'a pas besoin de quiz final.</p>
                        </div>

                        <div class="learning-items-sortable final" data-section-id="">
                            <?php if (empty($curriculum['final_items'])): ?>
                                <div class="learning-empty-inline">
                                    Aucun quiz final pour ce cours.
                                </div>
                            <?php else: ?>
                                <?php foreach ($curriculum['final_items'] as $item): ?>
                                    <?php $itemPayload = htmlspecialchars(json_encode($item, JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>
                                    <div class="learning-item-card final" data-item-id="<?= (int) $item['id'] ?>" draggable="true">
                                        <div class="learning-item-card__main">
                                            <span class="learning-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                                            <div>
                                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                <p>Quiz final · Score minimum : <?= (int) $item['passing_score'] ?>%</p>
                                            </div>
                                        </div>
                                        <div class="learning-item-card__actions">
                                            <button type="button" class="learning-icon-btn js_open_item_modal"
                                                data-mode="edit"
                                                data-item-type="<?= htmlspecialchars($item['item_type']) ?>"
                                                data-course-id="<?= $selectedCourseId ?>"
                                                data-section-id=""
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
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
    window.learningAdminData = {
        courseId: <?= $selectedCourseId ?>,
        courseSlug: <?= json_encode($selectedCourse['slug'] ?? '', JSON_UNESCAPED_UNICODE) ?>,
        assetBase: <?= json_encode(ASSET, JSON_UNESCAPED_UNICODE) ?>
    };
</script>

<?php if ($selectedCourse): ?>
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
                <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
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
                            <option value="">Sans chapitre / quiz final</option>
                            <?php foreach ($curriculum['sections'] as $section): ?>
                                <option value="<?= (int) $section['id'] ?>"><?= htmlspecialchars($section['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <label class="learning-field">
                    <span>Description</span>
                    <textarea name="description" id="js_item_description" rows="4" placeholder="Décris la leçon ou le quiz."></textarea>
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
                </div>

                <div id="js_quiz_fields" class="null">
                    <div class="learning-quiz-head">
                        <div>
                            <h3>Questions du quiz</h3>
                            <p>Choisis la bonne réponse, ajoute autant de choix que nécessaire et réorganise l'ensemble par glisser-déposer.</p>
                        </div>
                        <button type="button" class="learning-admin-btn secondary" id="js_add_question">
                            Ajouter une question
                        </button>
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

<script src="<?php echo ASSET; ?>js/manage_quiz.js?<?= filemtime(ROOT."asset/js/manage_quiz.js") ?>"></script>
