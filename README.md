# Module e-learning dynamique

Cette mise à jour transforme la zone `gestion-cours` en constructeur de parcours de formation avec :

- plusieurs cours ;
- chapitres ;
- leçons vidéo ;
- quiz de chapitre ;
- quiz final facultatif ;
- classement par ordre ;
- réorganisation en drag & drop ;
- certificat facultatif en fin de cours.

## Important

Je n'ai pas modifié `model/bdd.php`, comme demandé.

La structure SQL jointe `u577654037_e_construct.sql` ne contient pas les tables du module de cours utilisé par ce projet. Elle décrit une autre base avec `infos`, `messages`, `services`, etc.  
Pour rendre l'espace cours réellement dynamique avec la logique demandée, j'ai donc ajouté un schéma e-learning dédié dans le code.

## Nouvelles tables utilisées

Le code crée automatiquement les tables suivantes si elles n'existent pas :

- `learning_courses`
- `learning_sections`
- `learning_items`
- `learning_quiz_questions`
- `learning_quiz_answers`
- `learning_user_progress`
- `learning_quiz_attempt_answers`

## SQL prêt à coller dans phpMyAdmin

Si tu préfères créer les tables toi-même dans phpMyAdmin, colle directement ce script :

```sql
CREATE TABLE IF NOT EXISTS `learning_courses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` TEXT NULL,
  `slug` TEXT NULL,
  `short_description` TEXT NULL,
  `description` LONGTEXT NULL,
  `cover_image` TEXT NULL,
  `certificate_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `certificate_file` TEXT NULL,
  `passing_score` INT NOT NULL DEFAULT 70,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `position` INT NOT NULL DEFAULT 0,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_sections` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT NOT NULL,
  `title` TEXT NULL,
  `description` LONGTEXT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_learning_sections_course` (`course_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_items` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `course_id` INT NOT NULL,
  `section_id` INT NULL,
  `item_type` VARCHAR(30) NOT NULL DEFAULT 'video',
  `title` TEXT NULL,
  `description` LONGTEXT NULL,
  `video_file` TEXT NULL,
  `attachment_file` TEXT NULL,
  `duration_label` VARCHAR(50) NULL,
  `passing_score` INT NOT NULL DEFAULT 70,
  `is_preview` TINYINT(1) NOT NULL DEFAULT 0,
  `is_required` TINYINT(1) NOT NULL DEFAULT 1,
  `is_final_quiz` TINYINT(1) NOT NULL DEFAULT 0,
  `position` INT NOT NULL DEFAULT 0,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_learning_items_course` (`course_id`, `position`),
  KEY `idx_learning_items_section` (`section_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_quiz_questions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item_id` INT NOT NULL,
  `question_text` LONGTEXT NULL,
  `explanation` LONGTEXT NULL,
  `position` INT NOT NULL DEFAULT 0,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_learning_quiz_questions_item` (`item_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_quiz_answers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `question_id` INT NOT NULL,
  `answer_text` LONGTEXT NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `position` INT NOT NULL DEFAULT 0,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_learning_quiz_answers_question` (`question_id`, `position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_user_progress` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_unique_id` VARCHAR(255) NOT NULL,
  `course_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `item_type` VARCHAR(30) NOT NULL DEFAULT 'video',
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `watched_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `last_score` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `best_score` DECIMAL(5,2) NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_learning_user_progress_lookup` (`user_unique_id`, `item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `learning_quiz_attempt_answers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_unique_id` VARCHAR(255) NOT NULL,
  `course_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `question_id` INT NOT NULL,
  `answer_id` INT NOT NULL,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_learning_quiz_attempt_answers_lookup` (`user_unique_id`, `item_id`, `question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## SQL si les tables existent déjà mais qu'il manque des colonnes

Si tu as déjà commencé à tester et que certaines tables existent partiellement, tu peux aussi exécuter :

```sql
ALTER TABLE `learning_courses`
  ADD COLUMN `slug` TEXT NULL,
  ADD COLUMN `short_description` TEXT NULL,
  ADD COLUMN `description` LONGTEXT NULL,
  ADD COLUMN `cover_image` TEXT NULL,
  ADD COLUMN `certificate_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `certificate_file` TEXT NULL,
  ADD COLUMN `passing_score` INT NOT NULL DEFAULT 70,
  ADD COLUMN `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `position` INT NOT NULL DEFAULT 0,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `learning_sections`
  ADD COLUMN `description` LONGTEXT NULL,
  ADD COLUMN `position` INT NOT NULL DEFAULT 0,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `learning_items`
  ADD COLUMN `section_id` INT NULL,
  ADD COLUMN `item_type` VARCHAR(30) NOT NULL DEFAULT 'video',
  ADD COLUMN `description` LONGTEXT NULL,
  ADD COLUMN `video_file` TEXT NULL,
  ADD COLUMN `attachment_file` TEXT NULL,
  ADD COLUMN `duration_label` VARCHAR(50) NULL,
  ADD COLUMN `passing_score` INT NOT NULL DEFAULT 70,
  ADD COLUMN `is_preview` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `is_required` TINYINT(1) NOT NULL DEFAULT 1,
  ADD COLUMN `is_final_quiz` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `position` INT NOT NULL DEFAULT 0,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `learning_quiz_questions`
  ADD COLUMN `explanation` LONGTEXT NULL,
  ADD COLUMN `position` INT NOT NULL DEFAULT 0,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `learning_quiz_answers`
  ADD COLUMN `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `position` INT NOT NULL DEFAULT 0,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `learning_user_progress`
  ADD COLUMN `item_type` VARCHAR(30) NOT NULL DEFAULT 'video',
  ADD COLUMN `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN `watched_percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
  ADD COLUMN `last_score` DECIMAL(5,2) NOT NULL DEFAULT 0,
  ADD COLUMN `best_score` DECIMAL(5,2) NOT NULL DEFAULT 0,
  ADD COLUMN `completed_at` DATETIME NULL,
  ADD COLUMN `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `learning_quiz_attempt_answers`
  ADD COLUMN `course_id` INT NOT NULL,
  ADD COLUMN `item_id` INT NOT NULL,
  ADD COLUMN `question_id` INT NOT NULL,
  ADD COLUMN `answer_id` INT NOT NULL,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
```

## Colonnes principales ajoutées ou attendues

### `learning_courses`

- `title`
- `slug`
- `short_description`
- `description`
- `cover_image`
- `certificate_enabled`
- `certificate_file`
- `passing_score`
- `is_published`
- `position`
- `date_ajout`

### `learning_sections`

- `course_id`
- `title`
- `description`
- `position`
- `date_ajout`

### `learning_items`

- `course_id`
- `section_id`
- `item_type`
- `title`
- `description`
- `video_file`
- `attachment_file`
- `duration_label`
- `passing_score`
- `is_preview`
- `is_required`
- `is_final_quiz`
- `position`
- `date_ajout`

### `learning_quiz_questions`

- `item_id`
- `question_text`
- `explanation`
- `position`
- `date_ajout`

### `learning_quiz_answers`

- `question_id`
- `answer_text`
- `is_correct`
- `position`
- `date_ajout`

### `learning_user_progress`

- `user_unique_id`
- `course_id`
- `item_id`
- `item_type`
- `is_completed`
- `watched_percent`
- `last_score`
- `best_score`
- `completed_at`
- `updated_at`

### `learning_quiz_attempt_answers`

- `user_unique_id`
- `course_id`
- `item_id`
- `question_id`
- `answer_id`
- `date_ajout`

## Notes de compatibilité

- L'ancienne table `cours` n'a pas été supprimée.
- La page `cours` garde un affichage de secours basé sur `cours` si aucun enregistrement n'existe encore dans `learning_courses`.
- Le classement visible côté élève suit l'ordre défini dans l'admin.

## À vérifier sur ton hébergement

- extension PDO MySQL activée ;
- droits d'écriture sur :
  - `asset/videos/`
  - `asset/fichier/`
- taille maximale d'upload PHP suffisante pour les vidéos :
  - `upload_max_filesize`
  - `post_max_size`
  - `max_execution_time`

## Remarque importante

Je n'ai volontairement supprimé aucune table ni colonne existante de ton projet.  
Si tu veux plus tard fusionner complètement l'ancienne table `cours` avec ce nouveau modèle, on pourra faire une migration propre plutôt qu'un remplacement brutal.
