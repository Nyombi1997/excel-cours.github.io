<?php
    include_once FONCTION . 'profile_context.php';
    include_once ROOT . 'fonctions/course_bootstrap.php';

    profile_start_session_if_needed();
    ensure_learning_schema($bdd);

    $user = profile_get_viewed_user($bdd);
    if (!$user) {
        header("location: /connexion");
        exit;
    }

    $can_edit_profile = profile_can_edit_user($user);
    $profile_image = profile_get_avatar_url($user);
    $member_since = !empty($user['date_ajout']) ? date_fr_short($user['date_ajout']) : 'Non renseignée';

    $startedCourses = [];
    if (!empty($user['unique_id'])) {
        $startedStmt = $bdd->prepare("
            SELECT DISTINCT c.*
            FROM learning_user_progress p
            INNER JOIN learning_courses c ON c.id = p.course_id
            WHERE p.user_unique_id = :user_unique_id
            ORDER BY c.position ASC, c.id ASC
        ");
        $startedStmt->execute([
            ':user_unique_id' => (string) $user['unique_id']
        ]);
        $startedCourses = $startedStmt->fetchAll(PDO::FETCH_ASSOC);
    }
?>

<div class="container_profile_cours_excel profile-page-reset">
    <aside class="sidebar">
        <div class="card profile-sidebar-card">
            <span class="profile-sidebar-kicker">Espace compte</span>
            <h2 class="profile-sidebar-title">Mon profil</h2>
            <p class="profile-sidebar-text">
                Retrouvez votre profil et la progression de vos cours déjà commencés.
            </p>
        </div>

        <div class="card profile-sidebar-card">
            <span style="font-weight: bold;">Cours commencés</span>
            <?php if (!empty($startedCourses)) { ?>
                <div class="profile-course-progress-list">
                    <?php foreach ($startedCourses as $course) { ?>
                        <?php
                            $courseProgress = learning_get_course_progress($bdd, (string) $user['unique_id'], (int) $course['id']);
                        ?>
                        <a href="/details-cours?course=<?= urlencode((string) $course['slug']) ?>" class="profile-course-progress-item">
                            <div class="profile-course-progress-head">
                                <strong><?= htmlspecialchars((string) $course['title']) ?></strong>
                                <span><?= (int) $courseProgress['progress_percent'] ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= (int) $courseProgress['progress_percent'] ?>%;"></div>
                            </div>
                            <small><?= (int) $courseProgress['completed_items'] ?> étape(s) validée(s) sur <?= (int) $courseProgress['total_items'] ?></small>
                        </a>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="profile-empty-box">
                    Aucun cours commencé pour le moment.
                </div>
            <?php } ?>
        </div>

        <div class="card profile-sidebar-card">
            <span style="font-weight: bold;">Résumé</span>
            <div class="profile-sidebar-list">
                <div class="profile-sidebar-item">
                    <span>Membre depuis</span>
                    <strong><?= htmlspecialchars($member_since) ?></strong>
                </div>
                <div class="profile-sidebar-item">
                    <span>Adresse e-mail</span>
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                </div>
            </div>
        </div>

        <?php if ($can_edit_profile) { ?>
            <div class="card profile-sidebar-card">
                <span style="font-weight: bold;">Messagerie</span>
                <div class="profile-sidebar-list">
                    <a href="/contact" class="account-secondary-btn">Laisser un message</a>
                    <a href="/mes-messages" class="account-secondary-btn">Voir mes conversations</a>
                </div>
            </div>
        <?php } ?>
    </aside>

    <main class="main-content">
        <div class="card profile-intro-card">
            <div class="profile-intro-grid">
                <div class="profile-pic-container profile-pic-container-large">
                    <img src="<?= $profile_image ?>" class="profile-pic" alt="Photo de profil">
                </div>

                <div class="profile-intro-content">
                    <span class="profile-inline-badge"><?= $can_edit_profile ? 'Mon compte' : 'Profil visible' ?></span>
                    <h1 class="profile-intro-title"><?= htmlspecialchars($user['user_name']) ?></h1>
                    <p class="profile-intro-text">
                        Une page claire pour consulter votre profil et reprendre vos formations là où vous vous êtes arrêté.
                    </p>

                    <div class="profile-summary-grid">
                        <div class="profile-summary-item">
                            <span>Nom d'utilisateur</span>
                            <strong><?= htmlspecialchars($user['user_name']) ?></strong>
                        </div>
                        <div class="profile-summary-item">
                            <span>Adresse e-mail</span>
                            <strong><?= htmlspecialchars($user['email']) ?></strong>
                        </div>
                        <div class="profile-summary-item">
                            <span>Date d'inscription</span>
                            <strong><?= htmlspecialchars($member_since) ?></strong>
                        </div>
                    </div>

                    <?php if ($can_edit_profile) { ?>
                        <div class="profile-main-actions">
                            <a href="/modifier-compte" class="account-primary-btn">Modifier mes informations</a>
                            <a href="/mes-messages" class="account-secondary-btn">Mes messages</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </main>
</div>
