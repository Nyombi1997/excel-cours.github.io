<?php
    /**
     * Helpers du module e-learning.
     * On centralise ici la préparation du schéma et les lectures utiles
     * pour éviter de disperser la logique SQL dans les vues et les endpoints AJAX.
     */

    if (!function_exists('learning_table_exists')) {
        function learning_table_exists(PDO $bdd, string $table): bool
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table_name
            ");
            $stmt->execute([
                ':table_name' => $table
            ]);

            return (int) $stmt->fetchColumn() > 0;
        }
    }

    if (!function_exists('learning_column_exists')) {
        function learning_column_exists(PDO $bdd, string $table, string $column): bool
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table_name
                AND COLUMN_NAME = :column_name
            ");
            $stmt->execute([
                ':table_name' => $table,
                ':column_name' => $column
            ]);

            return (int) $stmt->fetchColumn() > 0;
        }
    }

    if (!function_exists('learning_index_exists')) {
        function learning_index_exists(PDO $bdd, string $table, string $index): bool
        {
            $stmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table_name
                AND INDEX_NAME = :index_name
            ");
            $stmt->execute([
                ':table_name' => $table,
                ':index_name' => $index
            ]);

            return (int) $stmt->fetchColumn() > 0;
        }
    }

    if (!function_exists('learning_ensure_column')) {
        function learning_ensure_column(PDO $bdd, string $table, string $column, string $definition): void
        {
            if (!learning_column_exists($bdd, $table, $column)) {
                $bdd->exec("ALTER TABLE `$table` ADD `$column` $definition");
            }
        }
    }

    if (!function_exists('learning_ensure_index')) {
        function learning_ensure_index(PDO $bdd, string $table, string $index, string $sql): void
        {
            if (!learning_index_exists($bdd, $table, $index)) {
                $bdd->exec($sql);
            }
        }
    }

    if (!function_exists('learning_slugify')) {
        function learning_slugify(string $text): string
        {
            $separator = '-';
            $slug = strtolower(trim($text));
            $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
            $slug = preg_replace('/[^a-z0-9]+/i', $separator, $slug);
            $slug = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $slug);
            $slug = trim($slug, $separator);

            return $slug !== '' ? $slug : 'cours';
        }
    }

    if (!function_exists('learning_normalize_search_text')) {
        function learning_normalize_search_text(string $text): string
        {
            $normalized = trim(mb_strtolower($text, 'UTF-8'));
            $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $normalized);
            if ($transliterated !== false) {
                $normalized = $transliterated;
            }
            $normalized = preg_replace('/[^a-z0-9\s]+/i', ' ', $normalized);
            $normalized = preg_replace('/\s+/', ' ', (string) $normalized);

            return trim((string) $normalized);
        }
    }

    if (!function_exists('learning_extract_search_terms')) {
        function learning_extract_search_terms(string $text): array
        {
            $normalized = learning_normalize_search_text($text);
            if ($normalized === '') {
                return [];
            }

            $parts = explode(' ', $normalized);
            $terms = [];

            foreach ($parts as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $terms[] = $part;
                }
            }

            return array_values(array_unique($terms));
        }
    }

    if (!function_exists('learning_score_search_match')) {
        function learning_score_search_match(string $query, string $label, string $context = '', int $weight = 1): int
        {
            // Le score combine : correspondance exacte, début de mot, présence partielle
            // et proximité orthographique pour tolérer les fautes de frappe.
            $normalizedQuery = learning_normalize_search_text($query);
            $normalizedLabel = learning_normalize_search_text($label);
            $normalizedContext = learning_normalize_search_text($context);

            if ($normalizedQuery === '' || $normalizedLabel === '') {
                return 0;
            }

            $score = 0;
            $terms = learning_extract_search_terms($normalizedQuery);
            $labelWords = learning_extract_search_terms($normalizedLabel);
            $contextWords = learning_extract_search_terms($normalizedContext);
            $allWords = array_values(array_unique(array_merge($labelWords, $contextWords)));

            if ($normalizedLabel === $normalizedQuery) {
                $score += 240;
            } elseif (strpos($normalizedLabel, $normalizedQuery) === 0) {
                $score += 170;
            } elseif (strpos($normalizedLabel, $normalizedQuery) !== false) {
                $score += 130;
            } elseif ($normalizedContext !== '' && strpos($normalizedContext, $normalizedQuery) !== false) {
                $score += 70;
            }

            foreach ($terms as $term) {
                if ($term === '') {
                    continue;
                }

                $bestWordScore = 0;
                foreach ($allWords as $word) {
                    if ($word === '') {
                        continue;
                    }

                    if ($word === $term) {
                        $bestWordScore = max($bestWordScore, 45);
                        continue;
                    }

                    if (strpos($word, $term) === 0) {
                        $bestWordScore = max($bestWordScore, 36);
                        continue;
                    }

                    if (strpos($word, $term) !== false || strpos($term, $word) !== false) {
                        $bestWordScore = max($bestWordScore, 26);
                    }

                    $distance = levenshtein($term, $word);
                    $maxLength = max(strlen($term), strlen($word));

                    if ($maxLength > 0) {
                        $ratio = 1 - ($distance / $maxLength);

                        if ($ratio >= 0.82) {
                            $bestWordScore = max($bestWordScore, 30);
                        } elseif ($ratio >= 0.68) {
                            $bestWordScore = max($bestWordScore, 18);
                        }
                    }
                }

                $score += $bestWordScore;
            }

            similar_text($normalizedQuery, $normalizedLabel, $similarityPercent);
            $score += (int) round($similarityPercent / 2.5);

            return $score * max(1, $weight);
        }
    }

    if (!function_exists('learning_generate_course_slug')) {
        function learning_generate_course_slug(PDO $bdd, string $title, int $excludeId = 0): string
        {
            $baseSlug = learning_slugify($title);
            $slug = $baseSlug;
            $index = 1;

            do {
                $sql = "SELECT id FROM learning_courses WHERE slug = :slug";
                if ($excludeId > 0) {
                    $sql .= " AND id != :exclude_id";
                }
                $sql .= " LIMIT 1";

                $stmt = $bdd->prepare($sql);
                $params = [':slug' => $slug];

                if ($excludeId > 0) {
                    $params[':exclude_id'] = $excludeId;
                }

                $stmt->execute($params);
                $exists = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($exists) {
                    $slug = $baseSlug . '-' . $index;
                    $index++;
                }
            } while ($exists);

            return $slug;
        }
    }

    if (!function_exists('learning_get_current_user_unique_id')) {
        function learning_get_current_user_unique_id(): string
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            if (isset($_SESSION['use_cours_excel_987654321'])) {
                return (string) $_SESSION['use_cours_excel_987654321'];
            }

            if (isset($_SESSION['admin_cours_excel_987654321'])) {
                return (string) $_SESSION['admin_cours_excel_987654321'];
            }

            return '';
        }
    }

    if (!function_exists('ensure_learning_schema')) {
        function ensure_learning_schema(PDO $bdd): void
        {
            createTable('learning_courses', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'title TEXT NULL',
                'slug TEXT NULL',
                'short_description TEXT NULL',
                'description LONGTEXT NULL',
                'cover_image TEXT NULL',
                'certificate_enabled TINYINT(1) NOT NULL DEFAULT 0',
                'certificate_file TEXT NULL',
                'passing_score INT NOT NULL DEFAULT 70',
                'is_published TINYINT(1) NOT NULL DEFAULT 1',
                'position INT NOT NULL DEFAULT 0',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            createTable('learning_sections', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'course_id INT NOT NULL',
                'title TEXT NULL',
                'description LONGTEXT NULL',
                'position INT NOT NULL DEFAULT 0',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            createTable('learning_items', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'course_id INT NOT NULL',
                'section_id INT NULL',
                'item_type VARCHAR(30) NOT NULL DEFAULT "video"',
                'title TEXT NULL',
                'description LONGTEXT NULL',
                'video_file TEXT NULL',
                'attachment_file TEXT NULL',
                'duration_label VARCHAR(50) NULL',
                'passing_score INT NOT NULL DEFAULT 70',
                'is_preview TINYINT(1) NOT NULL DEFAULT 0',
                'is_required TINYINT(1) NOT NULL DEFAULT 1',
                'is_final_quiz TINYINT(1) NOT NULL DEFAULT 0',
                'position INT NOT NULL DEFAULT 0',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            createTable('learning_quiz_questions', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'item_id INT NOT NULL',
                'question_text LONGTEXT NULL',
                'explanation LONGTEXT NULL',
                'position INT NOT NULL DEFAULT 0',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            createTable('learning_quiz_answers', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'question_id INT NOT NULL',
                'answer_text LONGTEXT NULL',
                'is_correct TINYINT(1) NOT NULL DEFAULT 0',
                'position INT NOT NULL DEFAULT 0',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            createTable('learning_user_progress', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'user_unique_id VARCHAR(255) NOT NULL',
                'course_id INT NOT NULL',
                'item_id INT NOT NULL',
                'item_type VARCHAR(30) NOT NULL DEFAULT "video"',
                'is_completed TINYINT(1) NOT NULL DEFAULT 0',
                'watched_percent DECIMAL(5,2) NOT NULL DEFAULT 0',
                'last_score DECIMAL(5,2) NOT NULL DEFAULT 0',
                'best_score DECIMAL(5,2) NOT NULL DEFAULT 0',
                'completed_at DATETIME NULL',
                'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            createTable('learning_quiz_attempt_answers', [
                'id INT AUTO_INCREMENT PRIMARY KEY',
                'user_unique_id VARCHAR(255) NOT NULL',
                'course_id INT NOT NULL',
                'item_id INT NOT NULL',
                'question_id INT NOT NULL',
                'answer_id INT NOT NULL',
                'date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP'
            ]);

            learning_ensure_index(
                $bdd,
                'learning_sections',
                'idx_learning_sections_course',
                'CREATE INDEX idx_learning_sections_course ON learning_sections(course_id, position)'
            );
            learning_ensure_index(
                $bdd,
                'learning_items',
                'idx_learning_items_course',
                'CREATE INDEX idx_learning_items_course ON learning_items(course_id, position)'
            );
            learning_ensure_index(
                $bdd,
                'learning_items',
                'idx_learning_items_section',
                'CREATE INDEX idx_learning_items_section ON learning_items(section_id, position)'
            );
            learning_ensure_index(
                $bdd,
                'learning_quiz_questions',
                'idx_learning_quiz_questions_item',
                'CREATE INDEX idx_learning_quiz_questions_item ON learning_quiz_questions(item_id, position)'
            );
            learning_ensure_index(
                $bdd,
                'learning_quiz_answers',
                'idx_learning_quiz_answers_question',
                'CREATE INDEX idx_learning_quiz_answers_question ON learning_quiz_answers(question_id, position)'
            );
            learning_ensure_index(
                $bdd,
                'learning_user_progress',
                'idx_learning_user_progress_lookup',
                'CREATE UNIQUE INDEX idx_learning_user_progress_lookup ON learning_user_progress(user_unique_id, item_id)'
            );
            learning_ensure_index(
                $bdd,
                'learning_quiz_attempt_answers',
                'idx_learning_quiz_attempt_answers_lookup',
                'CREATE UNIQUE INDEX idx_learning_quiz_attempt_answers_lookup ON learning_quiz_attempt_answers(user_unique_id, item_id, question_id)'
            );

            // Compatibilité avec les anciennes installations si les tables existaient déjà
            learning_ensure_column($bdd, 'learning_courses', 'slug', 'TEXT NULL');
            learning_ensure_column($bdd, 'learning_courses', 'short_description', 'TEXT NULL');
            learning_ensure_column($bdd, 'learning_courses', 'cover_image', 'TEXT NULL');
            learning_ensure_column($bdd, 'learning_courses', 'certificate_enabled', 'TINYINT(1) NOT NULL DEFAULT 0');
            learning_ensure_column($bdd, 'learning_courses', 'certificate_file', 'TEXT NULL');
            learning_ensure_column($bdd, 'learning_courses', 'passing_score', 'INT NOT NULL DEFAULT 70');
            learning_ensure_column($bdd, 'learning_courses', 'is_published', 'TINYINT(1) NOT NULL DEFAULT 1');
            learning_ensure_column($bdd, 'learning_courses', 'position', 'INT NOT NULL DEFAULT 0');

            learning_ensure_column($bdd, 'learning_sections', 'description', 'LONGTEXT NULL');
            learning_ensure_column($bdd, 'learning_sections', 'position', 'INT NOT NULL DEFAULT 0');

            learning_ensure_column($bdd, 'learning_items', 'section_id', 'INT NULL');
            learning_ensure_column($bdd, 'learning_items', 'duration_label', 'VARCHAR(50) NULL');
            learning_ensure_column($bdd, 'learning_items', 'passing_score', 'INT NOT NULL DEFAULT 70');
            learning_ensure_column($bdd, 'learning_items', 'is_preview', 'TINYINT(1) NOT NULL DEFAULT 0');
            learning_ensure_column($bdd, 'learning_items', 'is_required', 'TINYINT(1) NOT NULL DEFAULT 1');
            learning_ensure_column($bdd, 'learning_items', 'is_final_quiz', 'TINYINT(1) NOT NULL DEFAULT 0');
            learning_ensure_column($bdd, 'learning_items', 'position', 'INT NOT NULL DEFAULT 0');

            learning_ensure_column($bdd, 'learning_quiz_questions', 'explanation', 'LONGTEXT NULL');
            learning_ensure_column($bdd, 'learning_user_progress', 'watched_percent', 'DECIMAL(5,2) NOT NULL DEFAULT 0');
            learning_ensure_column($bdd, 'learning_user_progress', 'last_score', 'DECIMAL(5,2) NOT NULL DEFAULT 0');
            learning_ensure_column($bdd, 'learning_user_progress', 'best_score', 'DECIMAL(5,2) NOT NULL DEFAULT 0');
            learning_ensure_column($bdd, 'learning_user_progress', 'completed_at', 'DATETIME NULL');
            learning_ensure_column($bdd, 'learning_user_progress', 'updated_at', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
            learning_ensure_column($bdd, 'learning_quiz_attempt_answers', 'course_id', 'INT NOT NULL');
            learning_ensure_column($bdd, 'learning_quiz_attempt_answers', 'item_id', 'INT NOT NULL');
            learning_ensure_column($bdd, 'learning_quiz_attempt_answers', 'question_id', 'INT NOT NULL');
            learning_ensure_column($bdd, 'learning_quiz_attempt_answers', 'answer_id', 'INT NOT NULL');
            learning_ensure_column($bdd, 'learning_quiz_attempt_answers', 'date_ajout', 'DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

            // Ordonner les anciens enregistrements sans position.
            $tablesToNormalise = [
                'learning_courses',
                'learning_sections',
                'learning_items',
                'learning_quiz_questions',
                'learning_quiz_answers'
            ];

            foreach ($tablesToNormalise as $tableName) {
                if (learning_table_exists($bdd, $tableName) && learning_column_exists($bdd, $tableName, 'position')) {
                    $stmt = $bdd->query("SELECT id FROM `$tableName` ORDER BY position ASC, id ASC");
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $counter = 1;

                    foreach ($rows as $row) {
                        $updateStmt = $bdd->prepare("UPDATE `$tableName` SET position = :position WHERE id = :id");
                        $updateStmt->execute([
                            ':position' => $counter,
                            ':id' => (int) $row['id']
                        ]);
                        $counter++;
                    }
                }
            }
        }
    }

    if (!function_exists('learning_get_courses')) {
        function learning_get_courses(PDO $bdd, bool $publishedOnly = false): array
        {
            $sql = "
                SELECT
                    c.*,
                    (
                        SELECT COUNT(*)
                        FROM learning_sections s
                        WHERE s.course_id = c.id
                    ) AS total_sections,
                    (
                        SELECT COUNT(*)
                        FROM learning_items i
                        WHERE i.course_id = c.id
                    ) AS total_items
                FROM learning_courses c
            ";

            if ($publishedOnly) {
                $sql .= " WHERE c.is_published = 1";
            }

            $sql .= " ORDER BY c.position ASC, c.id ASC";

            $stmt = $bdd->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    if (!function_exists('learning_get_course')) {
        function learning_get_course(PDO $bdd, $identifier = null, bool $publishedOnly = false): ?array
        {
            if ($identifier === null || $identifier === '') {
                $courses = learning_get_courses($bdd, $publishedOnly);
                return !empty($courses) ? $courses[0] : null;
            }

            $sql = "SELECT * FROM learning_courses WHERE ";
            $params = [];

            if (is_numeric($identifier)) {
                $sql .= "id = :identifier";
                $params[':identifier'] = (int) $identifier;
            } else {
                $sql .= "slug = :identifier";
                $params[':identifier'] = (string) $identifier;
            }

            if ($publishedOnly) {
                $sql .= " AND is_published = 1";
            }

            $sql .= " LIMIT 1";

            $stmt = $bdd->prepare($sql);
            $stmt->execute($params);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);

            return $course ?: null;
        }
    }

    if (!function_exists('learning_get_item_quiz_payload')) {
        function learning_get_item_quiz_payload(PDO $bdd, int $itemId): array
        {
            $questionStmt = $bdd->prepare("
                SELECT *
                FROM learning_quiz_questions
                WHERE item_id = :item_id
                ORDER BY position ASC, id ASC
            ");
            $questionStmt->execute([
                ':item_id' => $itemId
            ]);

            $questions = $questionStmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($questions as &$question) {
                $answerStmt = $bdd->prepare("
                    SELECT *
                    FROM learning_quiz_answers
                    WHERE question_id = :question_id
                    ORDER BY position ASC, id ASC
                ");
                $answerStmt->execute([
                    ':question_id' => (int) $question['id']
                ]);
                $question['answers'] = $answerStmt->fetchAll(PDO::FETCH_ASSOC);
            }
            unset($question);

            return $questions;
        }
    }

    if (!function_exists('learning_get_course_curriculum')) {
        function learning_get_course_curriculum(PDO $bdd, int $courseId): array
        {
            $sectionStmt = $bdd->prepare("
                SELECT *
                FROM learning_sections
                WHERE course_id = :course_id
                ORDER BY position ASC, id ASC
            ");
            $sectionStmt->execute([
                ':course_id' => $courseId
            ]);
            $sections = $sectionStmt->fetchAll(PDO::FETCH_ASSOC);

            $itemStmt = $bdd->prepare("
                SELECT *
                FROM learning_items
                WHERE course_id = :course_id
                ORDER BY position ASC, id ASC
            ");
            $itemStmt->execute([
                ':course_id' => $courseId
            ]);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            $itemsBySection = [];
            $finalItems = [];

            foreach ($items as $item) {
                $item['quiz_questions'] = $item['item_type'] === 'quiz'
                    ? learning_get_item_quiz_payload($bdd, (int) $item['id'])
                    : [];

                if ($item['section_id'] === null || (int) $item['is_final_quiz'] === 1) {
                    $finalItems[] = $item;
                    continue;
                }

                $itemsBySection[(int) $item['section_id']][] = $item;
            }

            foreach ($sections as &$section) {
                $section['items'] = $itemsBySection[(int) $section['id']] ?? [];
            }
            unset($section);

            return [
                'sections' => $sections,
                'final_items' => $finalItems
            ];
        }
    }

    if (!function_exists('learning_search_catalog')) {
        function learning_search_catalog(PDO $bdd, string $query, bool $publishedOnly = false, int $limit = 8): array
        {
            // On regroupe les cours et les contenus afin de pouvoir rediriger
            // soit vers la fiche du cours, soit directement vers une leçon ciblée.
            $query = trim($query);
            if ($query === '') {
                return [];
            }

            $params = [];
            $courseSql = "
                SELECT
                    c.id,
                    c.slug,
                    c.title,
                    c.short_description,
                    c.description,
                    c.is_published
                FROM learning_courses c
            ";

            if ($publishedOnly) {
                $courseSql .= " WHERE c.is_published = 1";
            }

            $courseSql .= " ORDER BY c.position ASC, c.id ASC";

            $courseStmt = $bdd->prepare($courseSql);
            $courseStmt->execute($params);
            $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

            $itemSql = "
                SELECT
                    i.id,
                    i.course_id,
                    i.item_type,
                    i.title,
                    i.description,
                    i.is_preview,
                    c.slug AS course_slug,
                    c.title AS course_title,
                    c.is_published,
                    s.title AS section_title
                FROM learning_items i
                INNER JOIN learning_courses c ON c.id = i.course_id
                LEFT JOIN learning_sections s ON s.id = i.section_id
            ";

            if ($publishedOnly) {
                $itemSql .= " WHERE c.is_published = 1";
            }

            $itemSql .= " ORDER BY i.position ASC, i.id ASC";

            $itemStmt = $bdd->prepare($itemSql);
            $itemStmt->execute($params);
            $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];

            foreach ($courses as $course) {
                $score = learning_score_search_match(
                    $query,
                    (string) ($course['title'] ?? ''),
                    trim(((string) ($course['short_description'] ?? '')) . ' ' . ((string) ($course['description'] ?? ''))),
                    3
                );

                if ($score <= 0) {
                    continue;
                }

                $results[] = [
                    'score' => $score,
                    'type' => 'course',
                    'title' => (string) $course['title'],
                    'subtitle' => (string) ($course['short_description'] ?: 'Ouvrir le cours.'),
                    'url' => '/details-cours?course=' . urlencode((string) $course['slug']),
                    'course_slug' => (string) $course['slug']
                ];
            }

            foreach ($items as $item) {
                $score = learning_score_search_match(
                    $query,
                    (string) ($item['title'] ?? ''),
                    trim(
                        ((string) ($item['description'] ?? '')) . ' ' .
                        ((string) ($item['course_title'] ?? '')) . ' ' .
                        ((string) ($item['section_title'] ?? ''))
                    ),
                    2
                );

                if ($score <= 0) {
                    continue;
                }

                $itemTypeLabel = (string) ($item['item_type'] ?? 'video') === 'quiz' ? 'Quiz' : 'Leçon';
                $sectionTitle = trim((string) ($item['section_title'] ?? ''));
                $subtitleParts = [
                    'Cours : ' . (string) $item['course_title']
                ];

                if ($sectionTitle !== '') {
                    $subtitleParts[] = 'Chapitre : ' . $sectionTitle;
                }

                $subtitleParts[] = $itemTypeLabel;

                $results[] = [
                    'score' => $score,
                    'type' => 'item',
                    'title' => (string) $item['title'],
                    'subtitle' => implode(' · ', $subtitleParts),
                    'url' => '/details-cours?course=' . urlencode((string) $item['course_slug']) . '&item=' . (int) $item['id'],
                    'course_slug' => (string) $item['course_slug']
                ];
            }

            usort($results, static function (array $left, array $right): int {
                if ($left['score'] === $right['score']) {
                    return strcmp($left['title'], $right['title']);
                }

                return $right['score'] <=> $left['score'];
            });

            $uniqueResults = [];
            $seenUrls = [];

            foreach ($results as $result) {
                if (isset($seenUrls[$result['url']])) {
                    continue;
                }

                $seenUrls[$result['url']] = true;
                unset($result['score']);
                $uniqueResults[] = $result;

                if (count($uniqueResults) >= $limit) {
                    break;
                }
            }

            return $uniqueResults;
        }
    }

    if (!function_exists('learning_get_course_progress')) {
        function learning_get_course_progress(PDO $bdd, string $userUniqueId, int $courseId): array
        {
            $default = [
                'completed_items' => 0,
                'total_items' => 0,
                'progress_percent' => 0,
                'best_final_score' => 0,
                'course_completed' => false
            ];

            if ($userUniqueId === '') {
                return $default;
            }

            $totalStmt = $bdd->prepare("
                SELECT COUNT(*) 
                FROM learning_items
                WHERE course_id = :course_id
                AND is_required = 1
            ");
            $totalStmt->execute([
                ':course_id' => $courseId
            ]);
            $totalItems = (int) $totalStmt->fetchColumn();

            $completedStmt = $bdd->prepare("
                SELECT COUNT(*)
                FROM learning_user_progress p
                INNER JOIN learning_items i ON i.id = p.item_id
                WHERE p.user_unique_id = :user_unique_id
                AND p.course_id = :course_id
                AND p.is_completed = 1
                AND i.is_required = 1
            ");
            $completedStmt->execute([
                ':user_unique_id' => $userUniqueId,
                ':course_id' => $courseId
            ]);
            $completedItems = (int) $completedStmt->fetchColumn();

            $finalStmt = $bdd->prepare("
                SELECT MAX(p.best_score)
                FROM learning_user_progress p
                INNER JOIN learning_items i ON i.id = p.item_id
                WHERE p.user_unique_id = :user_unique_id
                AND p.course_id = :course_id
                AND i.is_final_quiz = 1
            ");
            $finalStmt->execute([
                ':user_unique_id' => $userUniqueId,
                ':course_id' => $courseId
            ]);
            $bestFinalScore = (float) $finalStmt->fetchColumn();

            $progressPercent = $totalItems > 0
                ? (int) round(($completedItems / $totalItems) * 100)
                : 0;

            return [
                'completed_items' => $completedItems,
                'total_items' => $totalItems,
                'progress_percent' => $progressPercent,
                'best_final_score' => $bestFinalScore,
                'course_completed' => $totalItems > 0 && $completedItems >= $totalItems
            ];
        }
    }

    if (!function_exists('learning_get_item_progress_map')) {
        function learning_get_item_progress_map(PDO $bdd, string $userUniqueId, int $courseId): array
        {
            if ($userUniqueId === '') {
                return [];
            }

            $stmt = $bdd->prepare("
                SELECT *
                FROM learning_user_progress
                WHERE user_unique_id = :user_unique_id
                AND course_id = :course_id
            ");
            $stmt->execute([
                ':user_unique_id' => $userUniqueId,
                ':course_id' => $courseId
            ]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map = [];

            foreach ($rows as $row) {
                $map[(int) $row['item_id']] = $row;
            }

            return $map;
        }
    }

    if (!function_exists('learning_get_quiz_answer_map')) {
        function learning_get_quiz_answer_map(PDO $bdd, string $userUniqueId, int $courseId): array
        {
            if ($userUniqueId === '') {
                return [];
            }

            $stmt = $bdd->prepare("
                SELECT *
                FROM learning_quiz_attempt_answers
                WHERE user_unique_id = :user_unique_id
                AND course_id = :course_id
            ");
            $stmt->execute([
                ':user_unique_id' => $userUniqueId,
                ':course_id' => $courseId
            ]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map = [];

            foreach ($rows as $row) {
                $itemId = (int) $row['item_id'];
                $questionId = (int) $row['question_id'];
                $map[$itemId][$questionId] = (int) $row['answer_id'];
            }

            return $map;
        }
    }

    if (!function_exists('learning_upsert_item_progress')) {
        function learning_upsert_item_progress(
            PDO $bdd,
            string $userUniqueId,
            int $courseId,
            int $itemId,
            string $itemType,
            float $watchedPercent = 0,
            float $lastScore = 0,
            float $bestScore = 0,
            bool $isCompleted = false
        ): void {
            if ($userUniqueId === '') {
                return;
            }

            $stmt = $bdd->prepare("
                SELECT *
                FROM learning_user_progress
                WHERE user_unique_id = :user_unique_id
                AND item_id = :item_id
                LIMIT 1
            ");
            $stmt->execute([
                ':user_unique_id' => $userUniqueId,
                ':item_id' => $itemId
            ]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $newWatched = max((float) $existing['watched_percent'], $watchedPercent);
                $newLastScore = $lastScore > 0 ? $lastScore : (float) $existing['last_score'];
                $newBestScore = max((float) $existing['best_score'], $bestScore, $lastScore);
                $newCompleted = $isCompleted || (int) $existing['is_completed'] === 1 ? 1 : 0;

                $updateStmt = $bdd->prepare("
                    UPDATE learning_user_progress
                    SET watched_percent = :watched_percent,
                        last_score = :last_score,
                        best_score = :best_score,
                        is_completed = :is_completed,
                        completed_at = :completed_at,
                        updated_at = NOW()
                    WHERE id = :id
                ");
                $updateStmt->execute([
                    ':watched_percent' => $newWatched,
                    ':last_score' => $newLastScore,
                    ':best_score' => $newBestScore,
                    ':is_completed' => $newCompleted,
                    ':completed_at' => $newCompleted === 1
                        ? ((int) $existing['is_completed'] === 1 ? $existing['completed_at'] : date('Y-m-d H:i:s'))
                        : null,
                    ':id' => (int) $existing['id']
                ]);

                return;
            }

            $insertStmt = $bdd->prepare("
                INSERT INTO learning_user_progress (
                    user_unique_id,
                    course_id,
                    item_id,
                    item_type,
                    is_completed,
                    watched_percent,
                    last_score,
                    best_score,
                    completed_at,
                    updated_at
                ) VALUES (
                    :user_unique_id,
                    :course_id,
                    :item_id,
                    :item_type,
                    :is_completed,
                    :watched_percent,
                    :last_score,
                    :best_score,
                    :completed_at,
                    NOW()
                )
            ");
            $insertStmt->execute([
                ':user_unique_id' => $userUniqueId,
                ':course_id' => $courseId,
                ':item_id' => $itemId,
                ':item_type' => $itemType,
                ':is_completed' => $isCompleted ? 1 : 0,
                ':watched_percent' => $watchedPercent,
                ':last_score' => $lastScore,
                ':best_score' => max($bestScore, $lastScore),
                ':completed_at' => $isCompleted ? date('Y-m-d H:i:s') : null
            ]);
        }
    }

    if (!function_exists('learning_get_legacy_courses')) {
        function learning_get_legacy_courses(PDO $bdd): array
        {
            if (!learning_table_exists($bdd, 'cours')) {
                return [];
            }

            $stmt = $bdd->prepare("SELECT * FROM cours ORDER BY position ASC, id ASC");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
?>
