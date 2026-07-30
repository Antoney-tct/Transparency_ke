-- ============================================================
-- Transparency_ke — migration_v3.sql
-- Adds real, server-persisted profile fields for citizens and
-- government reps, replacing the localStorage-only profile pages.
-- Run once, after migration_v2.sql. Back up first.
-- ============================================================

START TRANSACTION;

ALTER TABLE `citizens`
  ADD COLUMN IF NOT EXISTS `phone_alt` VARCHAR(20) DEFAULT NULL AFTER `phone`,
  ADD COLUMN IF NOT EXISTS `county` VARCHAR(100) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `gender` VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `dob` DATE DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `headline` VARCHAR(150) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `about` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `skills` TEXT DEFAULT NULL COMMENT 'JSON array of strings',
  ADD COLUMN IF NOT EXISTS `avatar_path` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `profile_completed` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `government_representatives`
  ADD COLUMN IF NOT EXISTS `phone` VARCHAR(20) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `since_year` VARCHAR(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `about` TEXT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `avatar_path` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `profile_completed` TINYINT(1) NOT NULL DEFAULT 0;

-- Don't force existing users through onboarding retroactively —
-- only new signups from here on start at profile_completed = 0.
UPDATE `citizens` SET `profile_completed` = 1;
UPDATE `government_representatives` SET `profile_completed` = 1;

COMMIT;
