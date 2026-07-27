-- ResumeSync Database Creation Script
-- Run this script to create a fresh database

-- Create database
CREATE DATABASE IF NOT EXISTS `resumesync_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `resumesync_db`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Users table
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address_line1` varchar(255) DEFAULT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `professional_title` varchar(255) DEFAULT NULL,
  `professional_summary` text DEFAULT NULL,
  `profile_photo_url` varchar(500) DEFAULT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  `resume_tips` tinyint(1) DEFAULT 1,
  `application_updates` tinyint(1) DEFAULT 1,
  `profile_visibility` enum('public','private','contacts') DEFAULT 'private',
  `dark_mode` tinyint(1) DEFAULT 0,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `account_status` enum('active','suspended','deleted') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_account_status` (`account_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Per-user AI request quotas
CREATE TABLE `ai_rate_limits` (
  `user_id` int(11) NOT NULL,
  `action_name` varchar(64) NOT NULL,
  `minute_window_start` int unsigned NOT NULL,
  `minute_count` int unsigned NOT NULL DEFAULT 0,
  `daily_window_start` int unsigned NOT NULL,
  `daily_count` int unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`, `action_name`),
  CONSTRAINT `ai_rate_limits_user_fk`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Authentication abuse controls
CREATE TABLE `auth_rate_limits` (
  `action_name` varchar(32) NOT NULL,
  `limit_key` char(64) NOT NULL,
  `window_start` int unsigned NOT NULL,
  `attempt_count` int unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`action_name`, `limit_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Templates table
CREATE TABLE `templates` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(100) NOT NULL,
  `template_display_name` varchar(255) NOT NULL,
  `template_category` varchar(50) DEFAULT 'professional',
  `description` text DEFAULT NULL,
  `preview_url` varchar(500) DEFAULT NULL,
  `template_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_config`)),
  `is_active` tinyint(1) DEFAULT 1,
  `is_premium` tinyint(1) DEFAULT 0,
  `usage_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`template_id`),
  UNIQUE KEY `template_name` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default templates
INSERT INTO `templates` (`template_name`, `template_display_name`, `template_category`, `description`, `preview_url`, `template_config`, `is_active`) VALUES
('modern', 'Simple', 'professional', 'Clean, minimalist design with modern typography', 'templates/modern.html', '{"layout":"two-column","font":"Arial","color_scheme":"blue-accent"}', 1),
('professional', 'Professional', 'professional', 'Sophisticated serif design with boxed header', 'templates/professional.html', '{"layout":"single-column","font":"Georgia","color_scheme":"black-white"}', 1),
('academic-standard', 'Academic', 'professional', 'Traditional academic CV format', 'templates/academic-standard.html', '{"layout":"single-column","font":"Times New Roman","color_scheme":"black-white"}', 1);

-- Resumes table
CREATE TABLE `resumes` (
  `resume_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT 1,
  `resume_title` varchar(255) NOT NULL,
  `template_name` varchar(100) DEFAULT 'classic',
  `personal_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`personal_details`)),
  `summary_text` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `share_token` varchar(64) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `view_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `version` int(11) DEFAULT 1,
  `file_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `certifications` longtext DEFAULT NULL,
  `languages` longtext DEFAULT NULL,
  `skills` longtext DEFAULT NULL,
  `experience` longtext DEFAULT NULL,
  `education` longtext DEFAULT NULL,
  PRIMARY KEY (`resume_id`),
  UNIQUE KEY `share_token` (`share_token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_template_id` (`template_id`),
  CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `resumes_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `templates` (`template_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ATS Scores table
CREATE TABLE `ats_scores` (
  `score_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `resume_id` int(11) NOT NULL,
  `resume_text` longtext DEFAULT NULL,
  `overall_score` int(11) NOT NULL CHECK (`overall_score` between 0 and 100),
  `job_description` text DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `keywords_score` int(11) DEFAULT NULL,
  `formatting_score` int(11) DEFAULT NULL,
  `experience_score` int(11) DEFAULT NULL,
  `education_score` int(11) DEFAULT NULL,
  `analysis_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`analysis_results`)),
  `recommendations` text DEFAULT NULL,
  `matched_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`matched_keywords`)),
  `missing_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`missing_keywords`)),
  `improvements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`improvements`)),
  `strengths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`strengths`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`score_id`),
  KEY `idx_resume_id` (`resume_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `ats_scores_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE,
  CONSTRAINT `ats_scores_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Job Applications table
CREATE TABLE `job_applications` (
  `application_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `resume_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_location` varchar(255) DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Internship','Freelance') DEFAULT 'Full-time',
  `application_date` date NOT NULL,
  `status` enum('Applied','In Review','Interview Scheduled','Interview Completed','Offer Received','Accepted','Rejected','Withdrawn') DEFAULT 'Applied',
  `application_url` varchar(500) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`application_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `resume_id` (`resume_id`),
  CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Application Timeline table
CREATE TABLE `application_timeline` (
  `timeline_id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` int(11) NOT NULL,
  `event_type` enum('application_submitted','status_changed','interview_scheduled','interview_completed','offer_received','follow_up','note_added','other') NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`timeline_id`),
  KEY `idx_application_id` (`application_id`),
  CONSTRAINT `application_timeline_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Education table
CREATE TABLE `user_education` (
  `education_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `institution_name` varchar(255) NOT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `field_of_study` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `current_student` tinyint(1) DEFAULT 0,
  `gpa` varchar(10) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`education_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `user_education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Experience table
CREATE TABLE `user_experience` (
  `experience_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `current_position` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`experience_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `user_experience_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Skills table
CREATE TABLE `user_skills` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `skill_category` varchar(100) DEFAULT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`skill_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE `sessions` (
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_data`)),
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`session_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs table
CREATE TABLE `activity_logs` (
  `log_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`log_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Resume triggers
DELIMITER $$
CREATE TRIGGER `after_resume_insert` AFTER INSERT ON `resumes` FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, activity_type, activity_description)
    VALUES (NEW.user_id, 'resume_created', CONCAT('Resume created: ', NEW.resume_title));
END$$

CREATE TRIGGER `before_resume_delete` BEFORE DELETE ON `resumes` FOR EACH ROW
BEGIN
    INSERT INTO activity_logs (user_id, activity_type, activity_description)
    VALUES (OLD.user_id, 'resume_deleted', CONCAT('Resume deleted: ', OLD.resume_title));
END$$
DELIMITER ;
