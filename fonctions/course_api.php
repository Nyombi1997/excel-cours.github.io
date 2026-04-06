<?php
    include_once "../model/bdd.php";
    include_once "../model/select.php";
    include_once "course_bootstrap.php";

    header('Content-Type: application/json; charset=utf-8');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    ensure_learning_schema($bdd);

    function course_api_json(array $payload, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    function course_api_clean_text(string $value): string
    {
        return trim(html_entity_decode(filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS)));
    }

    function course_api_bool($value): int
    {
        return (isset($value) && (string) $value === '1') ? 1 : 0;
    }

    function course_api_move_upload(array $file, string $directory, string $prefix, string $seedTitle): string
    {
        if (!isset($file['tmp_name']) || $file['tmp_name'] === '' || !is_uploaded_file($file['tmp_name'])) {
            return '';
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = learning_slugify($seedTitle);
        $filename = $prefix . '_' . $safeName . '_' . uniqid() . ($extension !== '' ? '.' . $extension : '');
        $target = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            course_api_json([
                'result' => 'error',
                'msg' => "Le fichier n'a pas pu être envoyé."
            ], 500);
        }

        return $filename;
    }

    function course_api_delete_file_if_exists(string $path): void
    {
        if ($path !== '' && file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }

    $action = $_POST['action'] ?? '';

    $adminOnlyActions = [
        'save_course',
        'delete_course',
        'save_section',
        'delete_section',
        'save_item',
        'delete_item',
        'reorder_courses',
        'reorder_sections',
        'reorder_items',
        'reorder_questions'
    ];

    if (in_array($action, $adminOnlyActions, true) && !isset($_SESSION['admin_cours_excel_987654321'])) {
        course_api_json([
            'result' => 'error',
            'msg' => "Votre session administrateur a expiré."
        ], 403);
    }

    $courseSessionExists = isset($_SESSION['use_cours_excel_987654321']) || isset($_SESSION['admin_cours_excel_987654321']);

    if ($action === 'search_courses') {
        if (!$courseSessionExists) {
            course_api_json([
                'result' => 'error',
                'msg' => "Votre session a expirÃ©."
            ], 403);
        }

        $query = course_api_clean_text($_POST['q'] ?? '');
        if ($query === '') {
            course_api_json([
                'result' => 'ok',
                'msg' => '',
                'results' => []
            ]);
        }

        $results = learning_search_catalog(
            $bdd,
            $query,
            !isset($_SESSION['admin_cours_excel_987654321']),
            8
        );

        course_api_json([
            'result' => 'ok',
            'msg' => empty($results) ? "Aucun cours proche n'a Ã©tÃ© trouvÃ©." : '',
            'results' => $results
        ]);
    }

    if ($action === 'save_course') {
        $courseId = isset($_POST['course_id']) ? (int) $_POST['course_id'] : 0;
        $title = course_api_clean_text($_POST['title'] ?? '');
        $shortDescription = course_api_clean_text($_POST['short_description'] ?? '');
        $description = course_api_clean_text($_POST['description'] ?? '');
        $passingScore = isset($_POST['passing_score']) ? max(0, min(100, (int) $_POST['passing_score'])) : 70;
        $certificateEnabled = course_api_bool($_POST['certificate_enabled'] ?? 0);
        $isPublished = course_api_bool($_POST['is_published'] ?? 0);

        if ($title === '') {
            course_api_json([
                'result' => 'error',
                'msg' => "Le titre du cours est obligatoire."
            ], 422);
        }

        $certificateFile = $_POST['existing_certificate_file'] ?? '';
        if (isset($_FILES['certificate_file']) && !empty($_FILES['certificate_file']['name'])) {
            $certificateFile = course_api_move_upload(
                $_FILES['certificate_file'],
                "../asset/fichier/",
                "certificat",
                $title
            );
        }

        if ($courseId > 0) {
            $slug = learning_generate_course_slug($bdd, $title, $courseId);
            $stmt = $bdd->prepare("
                UPDATE learning_courses
                SET title = :title,
                    slug = :slug,
                    short_description = :short_description,
                    description = :description,
                    passing_score = :passing_score,
                    certificate_enabled = :certificate_enabled,
                    certificate_file = :certificate_file,
                    is_published = :is_published
                WHERE id = :id
            ");
            $stmt->execute([
                ':title' => $title,
                ':slug' => $slug,
                ':short_description' => $shortDescription,
                ':description' => $description,
                ':passing_score' => $passingScore,
                ':certificate_enabled' => $certificateEnabled,
                ':certificate_file' => $certificateFile,
                ':is_published' => $isPublished,
                ':id' => $courseId
            ]);

            course_api_json([
                'result' => 'ok',
                'msg' => "Le cours a bien été mis à jour.",
                'redirect' => '/edition-cours?course=' . $slug
            ]);
        }

        $position = (int) $bdd->query("SELECT COALESCE(MAX(position), 0) + 1 FROM learning_courses")->fetchColumn();
        $slug = learning_generate_course_slug($bdd, $title);

        $stmt = $bdd->prepare("
            INSERT INTO learning_courses (
                title,
                slug,
                short_description,
                description,
                passing_score,
                certificate_enabled,
                certificate_file,
                is_published,
                position
            ) VALUES (
                :title,
                :slug,
                :short_description,
                :description,
                :passing_score,
                :certificate_enabled,
                :certificate_file,
                :is_published,
                :position
            )
        ");
        $stmt->execute([
            ':title' => $title,
            ':slug' => $slug,
            ':short_description' => $shortDescription,
            ':description' => $description,
            ':passing_score' => $passingScore,
            ':certificate_enabled' => $certificateEnabled,
            ':certificate_file' => $certificateFile,
            ':is_published' => $isPublished,
            ':position' => $position
        ]);

        course_api_json([
            'result' => 'ok',
            'msg' => "Le cours a bien été créé.",
            'redirect' => '/edition-cours?course=' . $slug
        ]);
    }

    if ($action === 'delete_course') {
        $courseId = (int) ($_POST['course_id'] ?? 0);
        if ($courseId <= 0) {
            course_api_json([
                'result' => 'error',
                'msg' => "Cours introuvable."
            ], 422);
        }

        $itemIdsStmt = $bdd->prepare("SELECT id FROM learning_items WHERE course_id = :course_id");
        $itemIdsStmt->execute([':course_id' => $courseId]);
        $itemIds = $itemIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($itemIds as $itemId) {
            $questionIdsStmt = $bdd->prepare("SELECT id FROM learning_quiz_questions WHERE item_id = :item_id");
            $questionIdsStmt->execute([':item_id' => (int) $itemId]);
            $questionIds = $questionIdsStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($questionIds as $questionId) {
                $deleteAnswers = $bdd->prepare("DELETE FROM learning_quiz_answers WHERE question_id = :question_id");
                $deleteAnswers->execute([':question_id' => (int) $questionId]);
            }

            $deleteQuestions = $bdd->prepare("DELETE FROM learning_quiz_questions WHERE item_id = :item_id");
            $deleteQuestions->execute([':item_id' => (int) $itemId]);
        }

        $bdd->prepare("DELETE FROM learning_user_progress WHERE course_id = :course_id")->execute([':course_id' => $courseId]);
        $bdd->prepare("DELETE FROM learning_items WHERE course_id = :course_id")->execute([':course_id' => $courseId]);
        $bdd->prepare("DELETE FROM learning_sections WHERE course_id = :course_id")->execute([':course_id' => $courseId]);
        $bdd->prepare("DELETE FROM learning_courses WHERE id = :id")->execute([':id' => $courseId]);

        course_api_json([
            'result' => 'ok',
            'msg' => "Le cours a été supprimé.",
            'redirect' => '/gestion-cours'
        ]);
    }

    if ($action === 'save_section') {
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $title = course_api_clean_text($_POST['title'] ?? '');
        $description = course_api_clean_text($_POST['description'] ?? '');

        if ($courseId <= 0 || $title === '') {
            course_api_json([
                'result' => 'error',
                'msg' => "Le chapitre doit avoir un titre."
            ], 422);
        }

        if ($sectionId > 0) {
            $stmt = $bdd->prepare("
                UPDATE learning_sections
                SET title = :title,
                    description = :description
                WHERE id = :id
            ");
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':id' => $sectionId
            ]);

        course_api_json([
            'result' => 'ok',
            'msg' => "Le chapitre a été mis à jour.",
            'section_id' => $sectionId
        ]);
        }

        $positionStmt = $bdd->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM learning_sections WHERE course_id = :course_id");
        $positionStmt->execute([':course_id' => $courseId]);
        $position = (int) $positionStmt->fetchColumn();

        $stmt = $bdd->prepare("
            INSERT INTO learning_sections (course_id, title, description, position)
            VALUES (:course_id, :title, :description, :position)
        ");
        $stmt->execute([
            ':course_id' => $courseId,
            ':title' => $title,
            ':description' => $description,
            ':position' => $position
        ]);

        course_api_json([
            'result' => 'ok',
            'msg' => "Le chapitre a été ajouté.",
            'section_id' => (int) $bdd->lastInsertId()
        ]);
    }

    if ($action === 'delete_section') {
        $sectionId = (int) ($_POST['section_id'] ?? 0);
        if ($sectionId <= 0) {
            course_api_json([
                'result' => 'error',
                'msg' => "Chapitre introuvable."
            ], 422);
        }

        $itemIdsStmt = $bdd->prepare("SELECT id FROM learning_items WHERE section_id = :section_id");
        $itemIdsStmt->execute([':section_id' => $sectionId]);
        $itemIds = $itemIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($itemIds as $itemId) {
            $questionIdsStmt = $bdd->prepare("SELECT id FROM learning_quiz_questions WHERE item_id = :item_id");
            $questionIdsStmt->execute([':item_id' => (int) $itemId]);
            $questionIds = $questionIdsStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($questionIds as $questionId) {
                $bdd->prepare("DELETE FROM learning_quiz_answers WHERE question_id = :question_id")
                    ->execute([':question_id' => (int) $questionId]);
            }

            $bdd->prepare("DELETE FROM learning_quiz_questions WHERE item_id = :item_id")
                ->execute([':item_id' => (int) $itemId]);
        }

        $bdd->prepare("DELETE FROM learning_user_progress WHERE item_id IN (SELECT id FROM learning_items WHERE section_id = :section_id)")
            ->execute([':section_id' => $sectionId]);
        $bdd->prepare("DELETE FROM learning_items WHERE section_id = :section_id")
            ->execute([':section_id' => $sectionId]);
        $bdd->prepare("DELETE FROM learning_sections WHERE id = :id")
            ->execute([':id' => $sectionId]);

        course_api_json([
            'result' => 'ok',
            'msg' => "Le chapitre a été supprimé."
        ]);
    }

    if ($action === 'save_item') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $sectionIdRaw = $_POST['section_id'] ?? '';
        $sectionId = $sectionIdRaw === '' ? null : (int) $sectionIdRaw;
        $itemType = course_api_clean_text($_POST['item_type'] ?? 'video');
        $title = course_api_clean_text($_POST['title'] ?? '');
        $description = course_api_clean_text($_POST['description'] ?? '');
        $durationLabel = course_api_clean_text($_POST['duration_label'] ?? '');
        $passingScore = isset($_POST['passing_score']) ? max(0, min(100, (int) $_POST['passing_score'])) : 70;
        $isPreview = course_api_bool($_POST['is_preview'] ?? 0);
        $isRequired = course_api_bool($_POST['is_required'] ?? 1);
        $isFinalQuiz = course_api_bool($_POST['is_final_quiz'] ?? 0);

        if ($courseId <= 0 || $title === '') {
            course_api_json([
                'result' => 'error',
                'msg' => "Le contenu doit avoir un titre."
            ], 422);
        }

        if (!in_array($itemType, ['video', 'quiz'], true)) {
            $itemType = 'video';
        }

        $existingVideoFile = $_POST['existing_video_file'] ?? '';
        $existingAttachmentFile = $_POST['existing_attachment_file'] ?? '';

        if (isset($_FILES['video_file']) && !empty($_FILES['video_file']['name'])) {
            $oldVideoFile = $existingVideoFile;
            $existingVideoFile = course_api_move_upload(
                $_FILES['video_file'],
                "../asset/videos/",
                "video",
                $title
            );

            // Si une nouvelle vidéo remplace l'ancienne, on supprime l'ancien fichier
            // pour éviter de garder des doublons inutiles sur le serveur.
            if ($oldVideoFile !== '' && $oldVideoFile !== $existingVideoFile) {
                course_api_delete_file_if_exists("../asset/videos/" . $oldVideoFile);
            }
        }

        if (isset($_FILES['attachment_file']) && !empty($_FILES['attachment_file']['name'])) {
            $existingAttachmentFile = course_api_move_upload(
                $_FILES['attachment_file'],
                "../asset/fichier/",
                "fichier",
                $title
            );
        }

        if ($itemType === 'video' && $existingVideoFile === '') {
            course_api_json([
                'result' => 'error',
                'msg' => "Une vidéo est obligatoire pour une leçon vidéo."
            ], 422);
        }

        $position = 0;
        if ($itemId <= 0) {
            $positionStmt = $bdd->prepare("SELECT COALESCE(MAX(position), 0) + 1 FROM learning_items WHERE course_id = :course_id");
            $positionStmt->execute([':course_id' => $courseId]);
            $position = (int) $positionStmt->fetchColumn();
        }

        if ($itemId > 0) {
            $stmt = $bdd->prepare("
                UPDATE learning_items
                SET course_id = :course_id,
                    section_id = :section_id,
                    item_type = :item_type,
                    title = :title,
                    description = :description,
                    video_file = :video_file,
                    attachment_file = :attachment_file,
                    duration_label = :duration_label,
                    passing_score = :passing_score,
                    is_preview = :is_preview,
                    is_required = :is_required,
                    is_final_quiz = :is_final_quiz
                WHERE id = :id
            ");
            $stmt->execute([
                ':course_id' => $courseId,
                ':section_id' => $isFinalQuiz ? null : $sectionId,
                ':item_type' => $itemType,
                ':title' => $title,
                ':description' => $description,
                ':video_file' => $itemType === 'video' ? $existingVideoFile : null,
                ':attachment_file' => $existingAttachmentFile,
                ':duration_label' => $durationLabel,
                ':passing_score' => $passingScore,
                ':is_preview' => $isPreview,
                ':is_required' => $isRequired,
                ':is_final_quiz' => $isFinalQuiz,
                ':id' => $itemId
            ]);
        } else {
            $stmt = $bdd->prepare("
                INSERT INTO learning_items (
                    course_id,
                    section_id,
                    item_type,
                    title,
                    description,
                    video_file,
                    attachment_file,
                    duration_label,
                    passing_score,
                    is_preview,
                    is_required,
                    is_final_quiz,
                    position
                ) VALUES (
                    :course_id,
                    :section_id,
                    :item_type,
                    :title,
                    :description,
                    :video_file,
                    :attachment_file,
                    :duration_label,
                    :passing_score,
                    :is_preview,
                    :is_required,
                    :is_final_quiz,
                    :position
                )
            ");
            $stmt->execute([
                ':course_id' => $courseId,
                ':section_id' => $isFinalQuiz ? null : $sectionId,
                ':item_type' => $itemType,
                ':title' => $title,
                ':description' => $description,
                ':video_file' => $itemType === 'video' ? $existingVideoFile : null,
                ':attachment_file' => $existingAttachmentFile,
                ':duration_label' => $durationLabel,
                ':passing_score' => $passingScore,
                ':is_preview' => $isPreview,
                ':is_required' => $isRequired,
                ':is_final_quiz' => $isFinalQuiz,
                ':position' => $position
            ]);
            $itemId = (int) $bdd->lastInsertId();
        }

        if ($itemType === 'quiz') {
            $payload = $_POST['quiz_payload'] ?? '[]';
            $questions = json_decode($payload, true);

            if (!is_array($questions) || empty($questions)) {
                course_api_json([
                    'result' => 'error',
                    'msg' => "Le quiz doit contenir au moins une question."
                ], 422);
            }

            $questionIdsStmt = $bdd->prepare("SELECT id FROM learning_quiz_questions WHERE item_id = :item_id");
            $questionIdsStmt->execute([':item_id' => $itemId]);
            $questionIds = $questionIdsStmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($questionIds as $questionId) {
                $bdd->prepare("DELETE FROM learning_quiz_answers WHERE question_id = :question_id")
                    ->execute([':question_id' => (int) $questionId]);
            }
            $bdd->prepare("DELETE FROM learning_quiz_questions WHERE item_id = :item_id")
                ->execute([':item_id' => $itemId]);

            foreach ($questions as $questionIndex => $question) {
                $questionText = course_api_clean_text($question['title'] ?? '');
                $questionExplanation = course_api_clean_text($question['explanation'] ?? '');
                $answers = $question['answers'] ?? [];

                if ($questionText === '' || !is_array($answers) || count($answers) < 2) {
                    course_api_json([
                        'result' => 'error',
                        'msg' => "Chaque question doit contenir un libellé et au moins deux réponses."
                    ], 422);
                }

                $correctCount = 0;
                foreach ($answers as $answer) {
                    if (!empty($answer['is_correct'])) {
                        $correctCount++;
                    }
                }

                if ($correctCount !== 1) {
                    course_api_json([
                        'result' => 'error',
                        'msg' => "Chaque question doit avoir une seule bonne réponse."
                    ], 422);
                }

                $insertQuestion = $bdd->prepare("
                    INSERT INTO learning_quiz_questions (item_id, question_text, explanation, position)
                    VALUES (:item_id, :question_text, :explanation, :position)
                ");
                $insertQuestion->execute([
                    ':item_id' => $itemId,
                    ':question_text' => $questionText,
                    ':explanation' => $questionExplanation,
                    ':position' => $questionIndex + 1
                ]);

                $questionId = (int) $bdd->lastInsertId();

                foreach ($answers as $answerIndex => $answer) {
                    $answerText = course_api_clean_text($answer['text'] ?? '');
                    if ($answerText === '') {
                        continue;
                    }

                    $insertAnswer = $bdd->prepare("
                        INSERT INTO learning_quiz_answers (question_id, answer_text, is_correct, position)
                        VALUES (:question_id, :answer_text, :is_correct, :position)
                    ");
                    $insertAnswer->execute([
                        ':question_id' => $questionId,
                        ':answer_text' => $answerText,
                        ':is_correct' => !empty($answer['is_correct']) ? 1 : 0,
                        ':position' => $answerIndex + 1
                    ]);
                }
            }
        } else {
            $questionIdsStmt = $bdd->prepare("SELECT id FROM learning_quiz_questions WHERE item_id = :item_id");
            $questionIdsStmt->execute([':item_id' => $itemId]);
            $questionIds = $questionIdsStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($questionIds as $questionId) {
                $bdd->prepare("DELETE FROM learning_quiz_answers WHERE question_id = :question_id")
                    ->execute([':question_id' => (int) $questionId]);
            }
            $bdd->prepare("DELETE FROM learning_quiz_questions WHERE item_id = :item_id")
                ->execute([':item_id' => $itemId]);
        }

        course_api_json([
            'result' => 'ok',
            'msg' => $itemType === 'quiz'
                ? "Le quiz a bien été enregistré."
                : "La vidéo de cours a bien été enregistrée."
        ]);
    }

    if ($action === 'delete_item') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        if ($itemId <= 0) {
            course_api_json([
                'result' => 'error',
                'msg' => "Contenu introuvable."
            ], 422);
        }

        $questionIdsStmt = $bdd->prepare("SELECT id FROM learning_quiz_questions WHERE item_id = :item_id");
        $questionIdsStmt->execute([':item_id' => $itemId]);
        $questionIds = $questionIdsStmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($questionIds as $questionId) {
            $bdd->prepare("DELETE FROM learning_quiz_answers WHERE question_id = :question_id")
                ->execute([':question_id' => (int) $questionId]);
        }

        $bdd->prepare("DELETE FROM learning_quiz_questions WHERE item_id = :item_id")
            ->execute([':item_id' => $itemId]);
        $bdd->prepare("DELETE FROM learning_user_progress WHERE item_id = :item_id")
            ->execute([':item_id' => $itemId]);
        $bdd->prepare("DELETE FROM learning_items WHERE id = :id")
            ->execute([':id' => $itemId]);

        course_api_json([
            'result' => 'ok',
            'msg' => "Le contenu a été supprimé."
        ]);
    }

    if ($action === 'reorder_courses') {
        $payload = json_decode($_POST['payload'] ?? '[]', true);
        if (!is_array($payload)) {
            $payload = [];
        }

        foreach ($payload as $index => $courseId) {
            $stmt = $bdd->prepare("UPDATE learning_courses SET position = :position WHERE id = :id");
            $stmt->execute([
                ':position' => $index + 1,
                ':id' => (int) $courseId
            ]);
        }

        course_api_json([
            'result' => 'ok',
            'msg' => "L'ordre des cours a été mis à jour."
        ]);
    }

    if ($action === 'reorder_sections') {
        $payload = json_decode($_POST['payload'] ?? '[]', true);
        if (!is_array($payload)) {
            $payload = [];
        }

        foreach ($payload as $index => $sectionId) {
            $stmt = $bdd->prepare("UPDATE learning_sections SET position = :position WHERE id = :id");
            $stmt->execute([
                ':position' => $index + 1,
                ':id' => (int) $sectionId
            ]);
        }

        course_api_json([
            'result' => 'ok',
            'msg' => "L'ordre des chapitres a été mis à jour."
        ]);
    }

    if ($action === 'reorder_items') {
        $payload = json_decode($_POST['payload'] ?? '[]', true);
        if (!is_array($payload)) {
            $payload = [];
        }

        foreach ($payload as $index => $row) {
            if (is_array($row)) {
                $stmt = $bdd->prepare("
                    UPDATE learning_items
                    SET position = :position,
                        section_id = :section_id,
                        is_final_quiz = :is_final_quiz
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':position' => isset($row['position']) ? (int) $row['position'] : $index + 1,
                    ':section_id' => ($row['section_id'] === '' || $row['section_id'] === null) ? null : (int) $row['section_id'],
                    ':is_final_quiz' => ($row['section_id'] === '' || $row['section_id'] === null) ? 1 : 0,
                    ':id' => (int) $row['item_id']
                ]);
                continue;
            }

            $stmt = $bdd->prepare("UPDATE learning_items SET position = :position WHERE id = :id");
            $stmt->execute([
                ':position' => $index + 1,
                ':id' => (int) $row
            ]);
        }

        course_api_json([
            'result' => 'ok',
            'msg' => "L'ordre des leçons a été mis à jour."
        ]);
    }

    if ($action === 'complete_item') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $watchedPercent = isset($_POST['watched_percent']) ? (float) $_POST['watched_percent'] : 0;
        $userId = learning_get_current_user_unique_id();

        if ($itemId <= 0 || $userId === '') {
            course_api_json([
                'result' => 'error',
                'msg' => "Impossible d'enregistrer la progression."
            ], 422);
        }

        $stmt = $bdd->prepare("SELECT * FROM learning_items WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            course_api_json([
                'result' => 'error',
                'msg' => "Contenu introuvable."
            ], 404);
        }

        learning_upsert_item_progress(
            $bdd,
            $userId,
            (int) $item['course_id'],
            $itemId,
            (string) $item['item_type'],
            $watchedPercent,
            0,
            0,
            $watchedPercent >= 90
        );

        $progress = learning_get_course_progress($bdd, $userId, (int) $item['course_id']);

        course_api_json([
            'result' => 'ok',
            'msg' => "Progression enregistrée.",
            'progress' => $progress
        ]);
    }

    if ($action === 'submit_quiz') {
        $itemId = (int) ($_POST['item_id'] ?? 0);
        $answersPayload = json_decode($_POST['answers'] ?? '{}', true);
        $userId = learning_get_current_user_unique_id();

        if ($itemId <= 0 || $userId === '') {
            course_api_json([
                'result' => 'error',
                'msg' => "Impossible d'enregistrer votre quiz."
            ], 422);
        }

        $itemStmt = $bdd->prepare("SELECT * FROM learning_items WHERE id = :id LIMIT 1");
        $itemStmt->execute([':id' => $itemId]);
        $item = $itemStmt->fetch(PDO::FETCH_ASSOC);

        if (!$item) {
            course_api_json([
                'result' => 'error',
                'msg' => "Quiz introuvable."
            ], 404);
        }

        $questions = learning_get_item_quiz_payload($bdd, $itemId);
        if (empty($questions)) {
            course_api_json([
                'result' => 'error',
                'msg' => "Ce quiz ne contient aucune question."
            ], 422);
        }

        $goodAnswers = 0;
        $totalQuestions = count($questions);
        $details = [];

        $selectedAnswersToStore = [];

        foreach ($questions as $question) {
            $questionId = (int) $question['id'];
            $selectedAnswerId = isset($answersPayload[$questionId]) ? (int) $answersPayload[$questionId] : 0;
            $correctAnswerId = 0;
            $correctAnswerText = '';

            foreach ($question['answers'] as $answer) {
                if ((int) $answer['is_correct'] === 1) {
                    $correctAnswerId = (int) $answer['id'];
                    $correctAnswerText = $answer['answer_text'];
                    break;
                }
            }

            $isCorrect = $selectedAnswerId > 0 && $selectedAnswerId === $correctAnswerId;
            if ($isCorrect) {
                $goodAnswers++;
            }

            if ($selectedAnswerId > 0) {
                $selectedAnswersToStore[] = [
                    'question_id' => $questionId,
                    'answer_id' => $selectedAnswerId
                ];
            }

            $details[] = [
                'question_id' => $questionId,
                'question' => $question['question_text'],
                'is_correct' => $isCorrect,
                'explanation' => $question['explanation']
            ];
        }

        $score = $totalQuestions > 0 ? round(($goodAnswers / $totalQuestions) * 100, 2) : 0;
        $isCompleted = $score >= (float) $item['passing_score'];

        if ($isCompleted) {
            $deleteAttemptAnswers = $bdd->prepare("
                DELETE FROM learning_quiz_attempt_answers
                WHERE user_unique_id = :user_unique_id
                AND item_id = :item_id
            ");
            $deleteAttemptAnswers->execute([
                ':user_unique_id' => $userId,
                ':item_id' => $itemId
            ]);

            foreach ($selectedAnswersToStore as $selectedAnswer) {
                $insertAttemptAnswer = $bdd->prepare("
                    INSERT INTO learning_quiz_attempt_answers (
                        user_unique_id,
                        course_id,
                        item_id,
                        question_id,
                        answer_id,
                        date_ajout
                    ) VALUES (
                        :user_unique_id,
                        :course_id,
                        :item_id,
                        :question_id,
                        :answer_id,
                        NOW()
                    )
                ");
                $insertAttemptAnswer->execute([
                    ':user_unique_id' => $userId,
                    ':course_id' => (int) $item['course_id'],
                    ':item_id' => $itemId,
                    ':question_id' => (int) $selectedAnswer['question_id'],
                    ':answer_id' => (int) $selectedAnswer['answer_id']
                ]);
            }
        }

        learning_upsert_item_progress(
            $bdd,
            $userId,
            (int) $item['course_id'],
            $itemId,
            'quiz',
            100,
            $score,
            $score,
            $isCompleted
        );

        $progress = learning_get_course_progress($bdd, $userId, (int) $item['course_id']);

        course_api_json([
            'result' => 'ok',
            'msg' => $isCompleted
                ? "Quiz réussi. Bravo."
                : "Quiz enregistré. Le score minimum n'est pas atteint.",
            'score' => $score,
            'required_score' => (int) $item['passing_score'],
            'details' => $details,
            'progress' => $progress,
            'passed' => $isCompleted
        ]);
    }

    course_api_json([
        'result' => 'error',
        'msg' => "Action inconnue."
    ], 404);
?>
