<?php
    include_once ROOT . "fonctions/course_bootstrap.php";
    ensure_learning_schema($bdd);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $homeCourses = learning_get_courses($bdd, false);
    $homeCourses = array_slice($homeCourses, 0, 6);
    $isConnectedHome = isset($_SESSION['use_cours_excel_987654321']) || isset($_SESSION['admin_cours_excel_987654321']);
    $homeUserUniqueId = $isConnectedHome ? learning_get_current_user_unique_id() : '';
?>

<section class="hero hero-absoluhub">
    <div class="hero-absoluhub__bg"></div>
    <div class="hero-absoluhub__content">
        <span class="hero-badge">Plateforme digitale AbsoluHub</span>
        <h1>Technologie, apprentissage et clart&eacute; dans une exp&eacute;rience premium.</h1>
        <p>
            AbsoluHub vous aide &agrave; structurer vos comp&eacute;tences, piloter vos d&eacute;cisions et progresser
            avec des parcours de formation modernes, fluides et professionnels.
        </p>

        <div class="hero-absoluhub__actions">
            <a href="/cours" class="hero-primary-cta">Explorer les cours</a>
            <a href="/contact" class="hero-secondary-cta">Parler &agrave; AbsoluHub</a>
        </div>

        <div class="hero-absoluhub__stats">
            <div class="hero-stat-card">
                <strong>Exp&eacute;rience</strong>
                <span>Un design net, immersif et orient&eacute; performance.</span>
            </div>
            <div class="hero-stat-card">
                <strong>Clart&eacute;</strong>
                <span>Des contenus structur&eacute;s pour apprendre sans friction.</span>
            </div>
            <div class="hero-stat-card">
                <strong>Confiance</strong>
                <span>Une interface coh&eacute;rente avec l'identit&eacute; de la marque.</span>
            </div>
        </div>
    </div>

    <div class="hero-absoluhub__visual">
        <div class="hero-device-card">
            <span class="hero-device-card__label">Cours disponibles</span>
            <strong>D&eacute;couvrez les parcours actuellement propos&eacute;s sur AbsoluHub.</strong>

            <?php if (!empty($homeCourses)) { ?>
                <div class="hero-device-card__courses">
                    <?php foreach ($homeCourses as $course) { ?>
                        <?php
                            $courseUrl = '/details-cours?course=' . urlencode((string) $course['slug']);
                            $courseAccessUrl = $isConnectedHome ? $courseUrl : '/connexion';
                        ?>
                        <a href="<?= $courseAccessUrl ?>" class="hero-course-card">
                            <span><?= (int) ($course['is_published'] ?? 0) === 1 ? 'Disponible' : 'Brouillon' ?></span>
                            <strong><?= htmlspecialchars((string) $course['title']) ?></strong>
                            <small>
                                <?=
                                    !empty($course['short_description'])
                                        ? htmlspecialchars((string) $course['short_description'])
                                        : 'Découvrir ce parcours.'
                                ?>
                            </small>
                        </a>
                    <?php } ?>
                </div>
                <?php if (!$isConnectedHome) { ?>
                    <div class="hero-device-card__empty hero-device-card__hint">
                        <strong>Connexion requise pour suivre un cours.</strong>
                        <p>Le catalogue reste visible, mais l'acc&egrave;s au suivi du cours demande une session active.</p>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="hero-device-card__empty">
                    <strong>Aucun cours disponible.</strong>
                    <p>Les prochaines formations appara&icirc;tront ici d&egrave;s leur publication.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<section class="home-course-preview">
    <div class="home-course-preview__head">
        <div>
            <span class="section-kicker">Cours disponibles</span>
            <h2>Retrouvez directement les formations disponibles sur la plateforme.</h2>
            <p>
                <?php if ($isConnectedHome) { ?>
                    Votre progression s'affiche sur chaque carte pour reprendre rapidement l&agrave; o&ugrave; vous vous &ecirc;tes arr&ecirc;t&eacute;.
                <?php } else { ?>
                    Le catalogue est visible sans connexion. Pour ouvrir un cours, vous serez redirig&eacute; vers la page de connexion.
                <?php } ?>
            </p>
        </div>
        <a href="<?= $isConnectedHome ? '/cours' : '/connexion' ?>" class="hero-secondary-cta home-course-preview__cta">
            <?= $isConnectedHome ? 'Voir tous les cours' : 'Se connecter pour apprendre' ?>
        </a>
    </div>

    <div class="learning-catalog-strip learning-catalog-grid home-course-preview__grid">
        <?php if (!empty($homeCourses)) { ?>
            <?php foreach ($homeCourses as $course) { ?>
                <?php
                    $courseUrl = '/details-cours?course=' . urlencode((string) $course['slug']);
                    $courseAccessUrl = $isConnectedHome ? $courseUrl : '/connexion';
                    $courseProgress = $isConnectedHome
                        ? learning_get_course_progress($bdd, $homeUserUniqueId, (int) $course['id'])
                        : [
                            'completed_items' => 0,
                            'total_items' => (int) ($course['total_items'] ?? 0),
                            'progress_percent' => 0
                        ];
                ?>
                <a href="<?= $courseAccessUrl ?>" class="learning-catalog-card home-course-card">
                    <div class="learning-catalog-card__top">
                        <span class="learning-catalog-badge">
                            <?= (int) ($course['is_published'] ?? 0) === 1 ? 'Disponible' : 'Brouillon' ?>
                        </span>
                        <span class="learning-catalog-arrow">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>

                    <div class="learning-catalog-card__body">
                        <strong><?= htmlspecialchars((string) $course['title']) ?></strong>
                        <p>
                            <?= htmlspecialchars((string) ($course['short_description'] ?: 'Découvrir le parcours complet.')) ?>
                        </p>
                    </div>

                    <div class="learning-catalog-card__footer">
                        <?php if ($isConnectedHome) { ?>
                            <div class="learning-catalog-progress">
                                <span>Progression</span>
                                <strong><?= (int) $courseProgress['progress_percent'] ?>%</strong>
                            </div>
                            <div class="learning-catalog-progressbar">
                                <div class="learning-catalog-progressbar__fill" style="width: <?= (int) $courseProgress['progress_percent'] ?>%;"></div>
                            </div>
                            <small><?= (int) $courseProgress['completed_items'] ?> &eacute;tape(s) valid&eacute;e(s) sur <?= (int) $courseProgress['total_items'] ?></small>
                        <?php } else { ?>
                            <div class="home-course-card__visitor">
                                <span>Connexion requise</span>
                                <strong>Connectez-vous pour suivre votre progression.</strong>
                            </div>
                            <small><?= (int) ($course['total_items'] ?? 0) ?> contenu(s) disponible(s) dans ce parcours.</small>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>
        <?php } else { ?>
            <div class="hero-device-card__empty home-course-preview__empty">
                <strong>Aucun cours disponible.</strong>
                <p>Les nouvelles formations appara&icirc;tront ici d&egrave;s leur publication.</p>
            </div>
        <?php } ?>
    </div>
</section>
<?php
    include_once VIEW . "/composant/footer.php";
?>
