-- ============================================================
-- Transparency_ke — migration_v2.sql
-- Run this once against your existing government_portal database.
-- Safe to run on top of your current data — it only ADDS
-- structure, it does not delete anything.
-- Take a backup first (phpMyAdmin > Export) before running.
-- ============================================================

START TRANSACTION;

-- ------------------------------------------------------------
-- 1. INSTITUTIONS — real entities instead of free-text dept
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `institutions` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `type` ENUM('ministry','county','agency','parastatal') NOT NULL DEFAULT 'ministry',
  `region` VARCHAR(100) DEFAULT NULL,
  `verified` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_name_region` (`name`,`region`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Seed from whatever departments already exist in your data,
-- so nothing currently registered gets orphaned.
INSERT IGNORE INTO `institutions` (`name`, `type`, `region`, `verified`)
SELECT DISTINCT `department`, 'ministry', `region`, 1
FROM `government_representatives`
WHERE `department` IS NOT NULL AND `department` <> '';

-- ------------------------------------------------------------
-- 2. GOVERNMENT_REPRESENTATIVES — link to institution + approval gate
-- ------------------------------------------------------------
ALTER TABLE `government_representatives`
  ADD COLUMN IF NOT EXISTS `institution_id` INT(11) DEFAULT NULL AFTER `department`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending' AFTER `position`,
  ADD COLUMN IF NOT EXISTS `is_platform_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`;

-- Backfill institution_id for existing reps from the seeded institutions above
UPDATE `government_representatives` gr
JOIN `institutions` i ON i.name = gr.department AND (i.region = gr.region OR (i.region IS NULL AND gr.region = ''))
SET gr.institution_id = i.id
WHERE gr.institution_id IS NULL;

-- Existing reps you already trust: mark them approved so you don't lock
-- yourself out. New signups going forward default to 'pending'.
UPDATE `government_representatives` SET `status` = 'approved' WHERE `status` = 'pending';

ALTER TABLE `government_representatives`
  ADD CONSTRAINT `fk_gov_rep_institution` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`);

-- Make YOURSELF the platform admin so you can approve everyone else.
-- Change the email below to your real login email before running.
UPDATE `government_representatives` SET `is_platform_admin` = 1 WHERE `email` = 'aouko178@gmail.com';

-- ------------------------------------------------------------
-- 3. INQUIRIES — route to the right institution
-- ------------------------------------------------------------
ALTER TABLE `inquiries`
  ADD COLUMN IF NOT EXISTS `institution_id` INT(11) DEFAULT NULL AFTER `citizen_id`;

ALTER TABLE `inquiries`
  ADD CONSTRAINT `fk_inquiry_institution` FOREIGN KEY (`institution_id`) REFERENCES `institutions`(`id`);

-- ------------------------------------------------------------
-- 4. MESSAGES — real threaded conversation (replaces one-shot `replies`)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `inquiry_id` INT(11) NOT NULL,
  `sender_type` ENUM('citizen','gov_rep') NOT NULL,
  `sender_id` INT(11) DEFAULT NULL,
  `sender_name` VARCHAR(150) NOT NULL,
  `body` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_inquiry` (`inquiry_id`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_message_inquiry` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Carry your existing one-shot replies into the new thread so nothing is lost
INSERT INTO `messages` (`inquiry_id`, `sender_type`, `sender_name`, `body`, `is_read`, `created_at`)
SELECT r.`inquiry_id`, 'gov_rep', 'Government Representative', r.`reply_message`, 1, r.`replied_at`
FROM `replies` r;

-- ------------------------------------------------------------
-- 5. NOTIFICATIONS — real backend state for the bell icon
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `recipient_email` VARCHAR(255) NOT NULL,
  `type` ENUM('new_inquiry','new_reply','account_approved','account_rejected') NOT NULL,
  `reference_id` INT(11) DEFAULT NULL,
  `message` VARCHAR(255) NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_recipient` (`recipient_email`),
  KEY `idx_unread` (`recipient_email`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
