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

    $curriculum = learning_get_course_curriculum($bdd, (int) $course['id']);
?>

<div class="learning-admin-page">
    <div class="learning-admin-hero">
        <div>
            <p class="learning-admin-kicker">Admin formation</p>
            <h1>Modifier le cours</h1>
            <p class="learning-admin-lead">Ici tu modifies les informations du cours et tu gères la liste de ses chapitres.</p>
        </div>
        <div class="learning-admin-hero-actions">
            <a href="/gestion-cours" class="learning-admin-btn secondary">Retour aux cours</a>
            <a href="/gestion-chapitre?course=<?= urlencode($course['slug']) ?>" class="learning-admin-btn primary">Ajouter un chapitre</a>
        </div>
    </div>

    <section class="learning-panel">
        <div class="learning-panel-header">
            <h2>Détails du cours</h2>
            <div class="learning-panel-header-actions">
                <button type="button" class="learning-admin-btn danger js_delete_course" data-course-id="<?= (int) $course['id'] ?>">Supprimer ce cours</button>
            </div>
        </div>

        <form id="js_course_form" class="learning-admin-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_course">
            <input type="hidden" name="course_id" value="<?= (int) $course['id'] ?>">
            <input type="hidden" name="existing_certificate_file" value="<?= htmlspecialchars($course['certificate_file'] ?? '') ?>">

            <div class="learning-grid two">
                <label class="learning-field">
                    <span>Titre du cours</span>
                    <input type="text" name="title" value="<?= htmlspecialchars($course['title']) ?>">
                </label>

                <label class="learning-field">
                    <span>Score minimum du cours</span>
                    <input type="number" name="passing_score" min="0" max="100" value="<?= (int) ($course['passing_score'] ?? 70) ?>">
                </label>
            </div>

            <label class="learning-field">
                <span>Accroche courte</span>
                <textarea name="short_description" rows="3"><?= htmlspecialchars($course['short_description'] ?? '') ?></textarea>
            </label>

            <label class="learning-field">
                <span>Description complète</span>
                <textarea name="description" rows="5"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
            </label>

            <div class="learning-grid two">
                <label class="learning-toggle">
                    <input type="checkbox" name="is_published" value="1" <?= (int) ($course['is_published'] ?? 0) === 1 ? 'checked' : '' ?>>
                    <span>Publier ce cours</span>
                </label>

                <label class="learning-toggle">
                    <input type="checkbox" name="certificate_enabled" value="1" <?= (int) ($course['certificate_enabled'] ?? 0) === 1 ? 'checked' : '' ?>>
                    <span>Activer un certificat facultatif</span>
                </label>
            </div>

            <label class="learning-field">
                <span>Fichier certificat</span>
                <input type="file" name="certificate_file" accept=".pdf,.png,.jpg,.jpeg,.webp">
                <?php if (!empty($course['certificate_file'])): ?>
                    <small class="learning-help">
                        Fichier actuel :
                        <a href="<?= ASSET ?>fichier/<?= rawurlencode($course['certificate_file']) ?>" target="_blank">
                            <?= htmlspecialchars($course['certificate_file']) ?>
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
            <h2>Chapitres du cours</h2>
            <a href="/gestion-chapitre?course=<?= urlencode($course['slug']) ?>" class="learning-admin-btn secondary">Nouveau chapitre</a>
        </div>

        <p class="learning-help">Tu peux réorganiser les chapitres par glisser-déposer. Clique sur un chapitre pour gérer ses vidéos et ses quiz.</p>

        <div class="learning-sections-sortable" id="js_sections_sortable">
            <?php if (empty($curriculum['sections'])): ?>
                <div class="learning-empty-inline">Aucun chapitre n’a encore été ajouté.</div>
            <?php else: ?>
                <?php foreach ($curriculum['sections'] as $section): ?>
                    <article class="learning-section-card" data-section-id="<?= (int) $section['id'] ?>" draggable="true">
                        <div class="learning-section-head">
                            <div class="learning-section-title">
                                <span class="learning-drag-handle"><i class="fa-solid fa-grip-vertical"></i></span>
                                <div>
                                    <h3><?= htmlspecialchars($section['title']) ?></h3>
                                    <p><?= htmlspecialchars($section['description'] ?: 'Aucune description de chapitre.') ?></p>
                                </div>
                            </div>
                            <div class="learning-panel-header-actions">
                                <a href="/gestion-chapitre?course=<?= urlencode($course['slug']) ?>&section=<?= (int) $section['id'] ?>" class="learning-admin-btn secondary">Gérer</a>
                                <button type="button" class="learning-icon-btn js_delete_section" data-section-id="<?= (int) $section['id'] ?>">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    window.learningAdminPage = {
        page: 'course-edit',
        courseId: <?= (int) $course['id'] ?>,
        courseSlug: <?= json_encode($course['slug'], JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="<?php echo ASSET; ?>js/manage_quiz.js?<?= filemtime(ROOT."asset/js/manage_quiz.js") ?>"></script>
