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
    $selectedCourse = learning_get_course($bdd, $_GET['course'] ?? null, !isset($_SESSION['admin_cours_excel_987654321']));

    if (!$selectedCourse) {
        header("location: /cours");
        exit;
    }

    $courseId = (int) $selectedCourse['id'];
    $curriculum = learning_get_course_curriculum($bdd, $courseId);
    $progress = learning_get_course_progress($bdd, $userUniqueId, $courseId);
    $itemProgressMap = learning_get_item_progress_map($bdd, $userUniqueId, $courseId);
    $quizAnswerMap = learning_get_quiz_answer_map($bdd, $userUniqueId, $courseId);

    $flatItems = [];
    foreach ($curriculum['sections'] as $section) {
        foreach ($section['items'] as $item) {
            $flatItems[] = $item;
        }
    }
    foreach ($curriculum['final_items'] as $item) {
        $flatItems[] = $item;
    }

    $activeItemId = isset($_GET['item']) ? (int) $_GET['item'] : 0;
    if ($activeItemId <= 0 && !empty($flatItems)) {
        $activeItemId = (int) $flatItems[0]['id'];
    }
?>

<div class="learning-student-page">
    <section class="learning-student-hero">
        <div class="learning-student-hero__copy">
            <p class="learning-admin-kicker">Détail du cours</p>
            <h1><?= htmlspecialchars($selectedCourse['title']) ?></h1>
            <p><?= htmlspecialchars($selectedCourse['short_description'] ?: "Apprends à ton rythme avec un parcours structuré par chapitres, vidéos et évaluations.") ?></p>
        </div>

        <div class="learning-student-hero__meta">
            <div class="learning-progress-card">
                <span>Progression</span>
                <strong><?= (int) $progress['progress_percent'] ?>%</strong>
                <div class="learning-progress-bar">
                    <div class="learning-progress-bar__fill" style="width: <?= (int) $progress['progress_percent'] ?>%;"></div>
                </div>
                <small><?= (int) $progress['completed_items'] ?> contenus terminés sur <?= (int) $progress['total_items'] ?></small>
            </div>

            <div class="learning-progress-card">
                <span>Certificat</span>
                <strong><?= (int) ($selectedCourse['certificate_enabled'] ?? 0) === 1 ? 'Activé' : 'Non prévu' ?></strong>
                <?php if ((int) ($selectedCourse['certificate_enabled'] ?? 0) === 1 && !empty($selectedCourse['certificate_file']) && $progress['course_completed']): ?>
                    <a href="<?= ASSET ?>fichier/<?= rawurlencode($selectedCourse['certificate_file']) ?>" class="learning-inline-link" target="_blank">
                        Télécharger le certificat
                    </a>
                <?php else: ?>
                    <small>
                        <?= (int) ($selectedCourse['certificate_enabled'] ?? 0) === 1
                            ? "Le certificat sera disponible une fois le cours terminé."
                            : "Ce cours ne propose pas de certificat." ?>
                    </small>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="learning-back-row">
        <div class="learning-back-row__actions">
            <a href="/cours" class="learning-admin-btn secondary">Retour aux cartes des cours</a>
            <button type="button" class="learning-admin-btn secondary learning-program-toggle" id="js_open_program_menu">
                Voir le programme
            </button>
        </div>
    </section>

    <section class="learning-course-layout">
        <div class="learning-program-overlay" id="js_program_overlay"></div>
        <aside class="learning-course-sidebar" id="js_program_sidebar">
            <div class="learning-program-mobile-head">
                <strong>Programme du cours</strong>
                <button type="button" class="learning-icon-btn" id="js_close_program_menu">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            <div class="learning-panel-header">
                <h2>Programme</h2>
                <span><?= count($flatItems) ?> contenus</span>
            </div>

            <?php foreach ($curriculum['sections'] as $sectionIndex => $section): ?>
                <div class="learning-student-section">
                    <div class="learning-student-section__title">
                        Chapitre <?= $sectionIndex + 1 ?> · <?= htmlspecialchars($section['title']) ?>
                    </div>

                    <?php foreach ($section['items'] as $item): ?>
                        <?php
                            $itemId = (int) $item['id'];
                            $progressRow = $itemProgressMap[$itemId] ?? null;
                            $isDone = $progressRow && (int) $progressRow['is_completed'] === 1;
                        ?>
                        <button
                            type="button"
                            class="learning-outline-item js_learning_item<?= $itemId === $activeItemId ? ' active' : '' ?><?= $isDone ? ' done' : '' ?>"
                            data-item-id="<?= $itemId ?>"
                            data-item-type="<?= htmlspecialchars($item['item_type']) ?>"
                        >
                            <span>
                                <?= htmlspecialchars($item['title']) ?>
                                <?php if ((int) $item['is_preview'] === 1): ?>
                                    <small>Aperçu</small>
                                <?php endif; ?>
                            </span>
                            <strong>
                                <?= $item['item_type'] === 'quiz' ? 'Quiz' : 'Vidéo' ?>
                                <?php if ($isDone): ?>
                                    <i class="fa-solid fa-circle-check learning-outline-check"></i>
                                <?php endif; ?>
                            </strong>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($curriculum['final_items'])): ?>
                <div class="learning-student-section final">
                    <div class="learning-student-section__title">Évaluation finale</div>
                    <?php foreach ($curriculum['final_items'] as $item): ?>
                        <?php
                            $itemId = (int) $item['id'];
                            $progressRow = $itemProgressMap[$itemId] ?? null;
                            $isDone = $progressRow && (int) $progressRow['is_completed'] === 1;
                        ?>
                        <button
                            type="button"
                            class="learning-outline-item js_learning_item<?= $itemId === $activeItemId ? ' active' : '' ?><?= $isDone ? ' done' : '' ?>"
                            data-item-id="<?= $itemId ?>"
                            data-item-type="<?= htmlspecialchars($item['item_type']) ?>"
                        >
                            <span><?= htmlspecialchars($item['title']) ?></span>
                            <strong>
                                Final
                                <?php if ($isDone): ?>
                                    <i class="fa-solid fa-circle-check learning-outline-check"></i>
                                <?php endif; ?>
                            </strong>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </aside>

        <div class="learning-course-main">
            <?php foreach ($flatItems as $item): ?>
                <?php
                    $itemId = (int) $item['id'];
                    $progressRow = $itemProgressMap[$itemId] ?? null;
                    $isDone = $progressRow && (int) $progressRow['is_completed'] === 1;
                ?>
                <article class="learning-item-panel js_learning_panel<?= $itemId === $activeItemId ? ' active' : '' ?>" data-item-id="<?= $itemId ?>">
                    <div class="learning-item-panel__head">
                        <div>
                            <span class="learning-pill"><?= $item['item_type'] === 'quiz' ? ((int) $item['is_final_quiz'] === 1 ? 'Quiz final' : 'Quiz') : 'Vidéo' ?></span>
                            <h2><?= htmlspecialchars($item['title']) ?></h2>
                        </div>
                        <div class="learning-item-panel__meta">
                            <?php if (!empty($item['duration_label'])): ?>
                                <span><?= htmlspecialchars($item['duration_label']) ?></span>
                            <?php endif; ?>
                            <?php if ($isDone): ?>
                                <span class="learning-status-chip done">Terminé</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($item['item_type'] === 'video'): ?>
                        <div class="learning-video-shell">
                            <video
                                width="100%"
                                controls
                                class="js_learning_video"
                                data-item-id="<?= $itemId ?>"
                            >
                                <source src="<?= ASSET ?>videos/<?= rawurlencode($item['video_file']) ?>" type="video/mp4">
                                Votre navigateur ne prend pas en charge les vidéos.
                            </video>
                        </div>

                        <div class="learning-item-panel__body">
                            <?php if ($progressRow && (float) ($progressRow['watched_percent'] ?? 0) >= 90): ?>
                                <div class="learning-info-banner done">
                                    Vidéo déjà visualisée à au moins 90 %. Elle compte dans votre progression.
                                    Vous pouvez la revoir quand vous voulez.
                                </div>
                            <?php else: ?>
                                <div class="learning-info-banner">
                                    Cette vidéo comptera dans votre progression une fois visionnée à 90 % minimum.
                                </div>
                            <?php endif; ?>

                            <div class="learning-rich-text">
                                <?= nl2br(htmlspecialchars($item['description'] ?: 'Bon visionnage.')) ?>
                            </div>

                            <?php if (!empty($item['attachment_file'])): ?>
                                <a class="learning-download-link" href="<?= ASSET ?>fichier/<?= rawurlencode($item['attachment_file']) ?>" target="_blank">
                                    Télécharger le fichier joint
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <form class="learning-quiz-form js_learning_quiz_form" data-item-id="<?= $itemId ?>">
                            <input type="hidden" name="item_id" value="<?= $itemId ?>">

                            <?php foreach ($item['quiz_questions'] as $questionIndex => $question): ?>
                                <div class="learning-question-card">
                                    <div class="learning-question-card__title">
                                        Question <?= $questionIndex + 1 ?> · <?= htmlspecialchars($question['question_text']) ?>
                                    </div>

                                    <div class="learning-answer-list">
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <?php
                                                $selectedAnswerId = isset($quizAnswerMap[$itemId][(int) $question['id']])
                                                    ? (int) $quizAnswerMap[$itemId][(int) $question['id']]
                                                    : 0;
                                            ?>
                                            <label class="learning-answer-option">
                                                <input
                                                    type="radio"
                                                    name="question_<?= (int) $question['id'] ?>"
                                                    value="<?= (int) $answer['id'] ?>"
                                                    <?= $selectedAnswerId === (int) $answer['id'] ? 'checked' : '' ?>
                                                >
                                                <span><?= htmlspecialchars($answer['answer_text']) ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="learning-form-actions">
                                <button type="submit" class="learning-admin-btn primary">
                                    Valider ce quiz
                                </button>
                            </div>
                        </form>

                        <div class="learning-quiz-result js_learning_quiz_result<?= $progressRow ? '' : ' null' ?>">
                            <?php if ($progressRow): ?>
                                <strong>
                                    <?= (int) ($progressRow['is_completed'] ?? 0) === 1 ? 'Quiz déjà réussi.' : 'Quiz déjà tenté.' ?>
                                </strong>
                                <span>Meilleur score : <?= (float) ($progressRow['best_score'] ?? 0) ?>%</span>
                                <span>
                                    <?= (int) ($progressRow['is_completed'] ?? 0) === 1
                                        ? 'Ce quiz compte déjà dans votre progression.'
                                        : 'Ce quiz ne comptera dans votre progression qu’une fois réussi.' ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<script>
    window.learningStudentData = {
        courseId: <?= $courseId ?>,
        courseSlug: <?= json_encode($selectedCourse['slug'], JSON_UNESCAPED_UNICODE) ?>
    };
</script>
<script src="<?php echo ASSET; ?>js/script_cours.js?<?= filemtime(ROOT."asset/js/script_cours.js") ?>"></script>
