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

## Recherche des cours

Aucune modification SQL n'est nécessaire pour la barre de recherche des cours.

Tu n'as rien à coller dans phpMyAdmin pour cette fonctionnalité.

## Espace profil utilisateur

La nouvelle page `compte` permet maintenant à l'utilisateur de modifier :

- son nom d'utilisateur ;
- son adresse e-mail ;
- son mot de passe ;
- sa photo de profil recadrée avant envoi.

## Point important sur la base SQL jointe

Le dump SQL joint ne contient pas la table `utilisateur`, alors que ton projet l'utilise déjà pour :

- `user_name`
- `email`
- `mdp`
- `unique_id`
- `profile`
- `slug`
- `admin`
- `date_ajout`

Si cette table existe déjà sur ton hébergement avec ces colonnes, tu n'as rien à faire.

Si elle existe mais qu'il manque certaines colonnes, tu peux coller ceci dans phpMyAdmin :

```sql
ALTER TABLE `utilisateur`
  ADD COLUMN `profile` TEXT NULL AFTER `unique_id`,
  ADD COLUMN `admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `profile`,
  ADD COLUMN `slug` TEXT NULL AFTER `admin`,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `slug`;
```

Si la table `utilisateur` n'existe pas encore dans ta vraie base, tu peux la créer avec ce script :

```sql
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_name` TEXT NOT NULL,
  `email` TEXT NOT NULL,
  `mdp` TEXT NOT NULL,
  `unique_id` VARCHAR(255) NOT NULL,
  `profile` TEXT NULL,
  `admin` TINYINT(1) NOT NULL DEFAULT 0,
  `slug` TEXT NULL,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Comportement retenu pour le profil

- le `slug` existant est conservé si l'utilisateur change seulement son nom d'utilisateur ;
- un `slug` est généré uniquement s'il manque encore sur le compte ;
- l'admin ou un visiteur sur une page profil publique voient la page en lecture seule ;
- la mise à jour du mot de passe reste optionnelle tant que les trois champs sécurité sont laissés vides.
- 
## Module contact et messagerie

La page `contact` a Ã©tÃ© ajoutÃ©e avec cette logique :

- elle n'est accessible qu'aux utilisateurs connectÃ©s ;
- elle permet :
  - d'appeler le numÃ©ro `+243 813 689 713` ;
  - d'ouvrir WhatsApp sur ce numÃ©ro ;
  - d'ouvrir la conversation WhatsApp de ce numÃ©ro ;
  - de laisser un message directement sur le site ;
- aprÃ¨s envoi, le formulaire se vide automatiquement ;
- les messages sont consultables depuis des pages sÃ©parÃ©es :
  - `mes-messages` et `ma-conversation` cÃ´tÃ© compte ;
  - `messages-admin` et `conversation-admin` cÃ´tÃ© administration.

## Important pour la base SQL du module contact

Le dump SQL joint ne contient pas encore les tables nÃ©cessaires Ã  cette fonctionnalitÃ©.

Je n'ai pas modifiÃ© `model/bdd.php` et je n'ai pas supposÃ© l'existence de tables/colonnes qui n'existent pas encore dans ton dump.  
Pour que le module fonctionne, il faut donc ajouter ces tables dans ta base.

## SQL Ã  coller directement dans phpMyAdmin pour le contact

```sql
CREATE TABLE IF NOT EXISTS `contact_conversations` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_unique_id` VARCHAR(255) NOT NULL,
  `user_name` TEXT NULL,
  `user_email` TEXT NULL,
  `provenance` TEXT NULL,
  `last_message_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_sender_type` VARCHAR(30) NOT NULL DEFAULT 'user',
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_contact_conversation_user` (`user_unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `conversation_id` INT NOT NULL,
  `sender_unique_id` VARCHAR(255) NOT NULL,
  `sender_type` VARCHAR(30) NOT NULL DEFAULT 'user',
  `sender_name` TEXT NULL,
  `sender_email` TEXT NULL,
  `provenance_page` VARCHAR(255) NULL,
  `provenance_label` TEXT NULL,
  `provenance_ip` VARCHAR(100) NULL,
  `provenance_user_agent` TEXT NULL,
  `message` LONGTEXT NOT NULL,
  `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_messages_conversation` (`conversation_id`, `date_ajout`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## Si les tables existent dÃ©jÃ  partiellement

Tu peux adapter avec ces `ALTER TABLE` si besoin :

```sql
ALTER TABLE `contact_conversations`
  ADD COLUMN `user_unique_id` VARCHAR(255) NOT NULL,
  ADD COLUMN `user_name` TEXT NULL,
  ADD COLUMN `user_email` TEXT NULL,
  ADD COLUMN `provenance` TEXT NULL,
  ADD COLUMN `last_message_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN `last_sender_type` VARCHAR(30) NOT NULL DEFAULT 'user',
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `contact_messages`
  ADD COLUMN `conversation_id` INT NOT NULL,
  ADD COLUMN `sender_unique_id` VARCHAR(255) NOT NULL,
  ADD COLUMN `sender_type` VARCHAR(30) NOT NULL DEFAULT 'user',
  ADD COLUMN `sender_name` TEXT NULL,
  ADD COLUMN `sender_email` TEXT NULL,
  ADD COLUMN `provenance_page` VARCHAR(255) NULL,
  ADD COLUMN `provenance_label` TEXT NULL,
  ADD COLUMN `provenance_ip` VARCHAR(100) NULL,
  ADD COLUMN `provenance_user_agent` TEXT NULL,
  ADD COLUMN `message` LONGTEXT NOT NULL,
  ADD COLUMN `date_ajout` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
```

## Colonnes enregistrÃ©es pour la provenance

Pour chaque message, le code enregistre :

- le compte Ã©metteur (`sender_unique_id`, `sender_name`, `sender_email`) ;
- le type de session (`sender_type`) ;
- la page source (`provenance_page`) ;
- le libellÃ© de provenance (`provenance_label`) ;
- l'adresse IP (`provenance_ip`) ;
- le `user_agent` (`provenance_user_agent`) ;
- la date d'envoi (`date_ajout`).
