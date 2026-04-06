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
?>

<div class="learning-admin-page">
    <div class="learning-admin-hero">
        <div>
            <p class="learning-admin-kicker">Admin formation</p>
            <h1>Liste des cours</h1>
            <p class="learning-admin-lead">Crée tes cours ici, puis organise chaque cours et chaque chapitre dans des pages dédiées.</p>
        </div>
        <div class="learning-admin-hero-actions">
            <a href="/creation-cours" class="learning-admin-btn primary">Créer un cours</a>
        </div>
    </div>

    <section class="learning-panel">
        <div class="learning-panel-header">
            <h2>Catalogue des cours</h2>
            <span><?= count($allCourses) ?> cours</span>
        </div>

        <p class="learning-help">Tu peux réorganiser les cours par glisser-déposer. Ensuite, clique sur un cours pour l’éditer.</p>

        <div class="learning-course-sortable" id="js_course_sortable">
            <?php if (empty($allCourses)): ?>
                <div class="learning-empty-inline">Aucun cours n’a encore été créé.</div>
            <?php else: ?>
                <?php foreach ($allCourses as $course): ?>
                    <div class="learning-course-card" data-course-id="<?= (int) $course['id'] ?>" draggable="true">
                        <span class="learning-course-card__drag"><i class="fa-solid fa-grip-vertical"></i></span>
                        <span class="learning-course-card__content">
                            <strong><?= htmlspecialchars($course['title'] ?: 'Cours sans titre') ?></strong>
                            <small><?= (int) $course['total_sections'] ?> chapitres · <?= (int) $course['total_items'] ?> contenus</small>
                        </span>
                        <span class="learning-course-card__status <?= (int) $course['is_published'] === 1 ? 'published' : 'draft' ?>">
                            <?= (int) $course['is_published'] === 1 ? 'Publié' : 'Brouillon' ?>
                        </span>
                        <a href="/edition-cours?course=<?= urlencode($course['slug']) ?>" class="learning-admin-btn secondary">Ouvrir</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
    window.learningAdminPage = {
        page: 'courses-list'
    };
</script>
<script src="<?php echo ASSET; ?>js/manage_quiz.js?<?= filemtime(ROOT."asset/js/manage_quiz.js") ?>"></script>
