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
?>

<div class="learning-admin-page">
    <div class="learning-admin-hero">
        <div>
            <p class="learning-admin-kicker">Admin formation</p>
            <h1>Créer un cours</h1>
            <p class="learning-admin-lead">Commence par créer le cours. Tu pourras ensuite l’éditer dans une page dédiée et y rattacher des chapitres.</p>
        </div>
        <div class="learning-admin-hero-actions">
            <a href="/gestion-cours" class="learning-admin-btn secondary">Retour à la liste</a>
        </div>
    </div>

    <section class="learning-panel">
        <div class="learning-panel-header">
            <h2>Nouveau cours</h2>
        </div>

        <form id="js_course_form" class="learning-admin-form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save_course">
            <input type="hidden" name="course_id" value="0">
            <input type="hidden" name="existing_certificate_file" value="">

            <div class="learning-grid two">
                <label class="learning-field">
                    <span>Titre du cours</span>
                    <input type="text" name="title" value="" placeholder="Ex. Excel complet pour débutants">
                </label>

                <label class="learning-field">
                    <span>Score minimum du cours</span>
                    <input type="number" name="passing_score" min="0" max="100" value="70">
                </label>
            </div>

            <label class="learning-field">
                <span>Accroche courte</span>
                <textarea name="short_description" rows="3" placeholder="Présente rapidement la promesse du cours."></textarea>
            </label>

            <label class="learning-field">
                <span>Description complète</span>
                <textarea name="description" rows="5" placeholder="Décris le programme, le niveau et les objectifs."></textarea>
            </label>

            <div class="learning-grid two">
                <label class="learning-toggle">
                    <input type="checkbox" name="is_published" value="1">
                    <span>Publier ce cours</span>
                </label>

                <label class="learning-toggle">
                    <input type="checkbox" name="certificate_enabled" value="1">
                    <span>Activer un certificat facultatif</span>
                </label>
            </div>

            <label class="learning-field">
                <span>Fichier certificat</span>
                <input type="file" name="certificate_file" accept=".pdf,.png,.jpg,.jpeg,.webp">
            </label>

            <div class="learning-form-actions">
                <button type="submit" class="learning-admin-btn primary">Créer le cours</button>
            </div>
        </form>
    </section>
</div>

<script>
    window.learningAdminPage = {
        page: 'course-create'
    };
</script>
<script src="<?php echo ASSET; ?>js/manage_quiz.js?<?= filemtime(ROOT."asset/js/manage_quiz.js") ?>"></script>
