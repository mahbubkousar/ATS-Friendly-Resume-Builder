-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 08, 2025 at 05:01 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `resumesync_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CleanExpiredSessions` ()   BEGIN
    DELETE FROM sessions WHERE expires_at < NOW();
    SELECT ROW_COUNT() AS deleted_sessions;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateResumeWithSections` (IN `p_user_id` INT, IN `p_resume_title` VARCHAR(255), IN `p_template_name` VARCHAR(100), OUT `p_resume_id` INT)   BEGIN
    -- Insert resume
    INSERT INTO resumes (user_id, resume_title, template_name, status)
    VALUES (p_user_id, p_resume_title, p_template_name, 'draft');

    SET p_resume_id = LAST_INSERT_ID();

    -- Create default sections
    INSERT INTO resume_sections (resume_id, section_type, section_title, display_order)
    VALUES
        (p_resume_id, 'experience', 'Work Experience', 1),
        (p_resume_id, 'education', 'Education', 2),
        (p_resume_id, 'skills', 'Skills', 3);

    -- Update template usage count
    UPDATE templates
    SET usage_count = usage_count + 1
    WHERE template_name = p_template_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetResumeDetails` (IN `p_resume_id` INT)   BEGIN
    -- Resume basic info
    SELECT
        r.*,
        u.full_name,
        u.email,
        u.phone,
        t.template_display_name
    FROM resumes r
    JOIN users u ON r.user_id = u.user_id
    LEFT JOIN templates t ON r.template_id = t.template_id
    WHERE r.resume_id = p_resume_id;

    -- Resume sections
    SELECT * FROM resume_sections
    WHERE resume_id = p_resume_id AND is_visible = TRUE
    ORDER BY display_order;

    -- Latest ATS score
    SELECT * FROM ats_scores
    WHERE resume_id = p_resume_id
    ORDER BY created_at DESC
    LIMIT 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserCompleteProfile` (IN `p_user_id` INT)   BEGIN
    -- User basic info
    SELECT * FROM users WHERE user_id = p_user_id;

    -- Education
    SELECT * FROM user_education WHERE user_id = p_user_id ORDER BY display_order, end_date DESC;

    -- Experience
    SELECT * FROM user_experience WHERE user_id = p_user_id ORDER BY display_order, end_date DESC;

    -- Skills
    SELECT * FROM user_skills WHERE user_id = p_user_id ORDER BY display_order;

    -- Resumes
    SELECT * FROM resumes WHERE user_id = p_user_id ORDER BY updated_at DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `LogActivity` (IN `p_user_id` INT, IN `p_activity_type` VARCHAR(100), IN `p_activity_description` TEXT, IN `p_ip_address` VARCHAR(45), IN `p_user_agent` TEXT)   BEGIN
    INSERT INTO activity_logs (user_id, activity_type, activity_description, ip_address, user_agent)
    VALUES (p_user_id, p_activity_type, p_activity_description, p_ip_address, p_user_agent);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `activity_type`, `activity_description`, `ip_address`, `user_agent`, `metadata`, `created_at`) VALUES
(1, 1, 'resume_created', 'Resume created: Resume 1', NULL, NULL, NULL, '2025-12-01 09:05:05'),
(2, 1, 'resume_deleted', 'Resume deleted: Resume 1', NULL, NULL, NULL, '2025-12-01 09:10:00'),
(3, 1, 'resume_created', 'Resume created: Untitled Resume', NULL, NULL, NULL, '2025-12-01 09:19:25'),
(4, 1, 'resume_created', 'Resume created: ACDM 1', NULL, NULL, NULL, '2025-12-01 13:32:13'),
(5, 1, 'resume_created', 'Resume created: Untitled Resume', NULL, NULL, NULL, '2025-12-01 13:55:05'),
(6, 1, 'resume_created', 'Resume created: ACCCC', NULL, NULL, NULL, '2025-12-01 14:01:49'),
(7, 1, 'resume_created', 'Resume created: AI Generated Resume', NULL, NULL, NULL, '2025-12-02 07:09:12'),
(8, 1, 'resume_created', 'Resume created: AI Generated Resume', NULL, NULL, NULL, '2025-12-02 07:09:13'),
(9, 1, 'resume_created', 'Resume created: ATS Optimized', NULL, NULL, NULL, '2025-12-02 07:52:30'),
(10, 1, 'resume_created', 'Resume created: ATS Optimized Resume', NULL, NULL, NULL, '2025-12-02 08:13:20'),
(11, 1, 'resume_created', 'Resume created: Untitled Resume', NULL, NULL, NULL, '2025-12-02 08:44:51'),
(12, 1, 'resume_deleted', 'Resume deleted: ACDM 1', NULL, NULL, NULL, '2025-12-02 08:51:37'),
(13, 1, 'resume_deleted', 'Resume deleted: Untitled Resume', NULL, NULL, NULL, '2025-12-02 08:51:41'),
(14, 1, 'resume_deleted', 'Resume deleted: Untitled Resumett', NULL, NULL, NULL, '2025-12-02 08:51:45'),
(15, 1, 'resume_deleted', 'Resume deleted: AI Generated Resume', NULL, NULL, NULL, '2025-12-02 08:51:50'),
(16, 1, 'resume_deleted', 'Resume deleted: okay', NULL, NULL, NULL, '2025-12-02 08:51:54'),
(17, 1, 'resume_deleted', 'Resume deleted: AI Generated Resume', NULL, NULL, NULL, '2025-12-02 08:52:04'),
(18, 1, 'resume_deleted', 'Resume deleted: ATS Optimized Resume', NULL, NULL, NULL, '2025-12-02 08:54:03'),
(19, 1, 'resume_deleted', 'Resume deleted: ATS Optimized', NULL, NULL, NULL, '2025-12-02 08:54:07'),
(20, 1, 'resume_deleted', 'Resume deleted: ACCCC', NULL, NULL, NULL, '2025-12-02 08:54:11'),
(21, 1, 'resume_created', 'Resume created: one', NULL, NULL, NULL, '2025-12-02 12:53:02'),
(22, 1, 'resume_deleted', 'Resume deleted: one', NULL, NULL, NULL, '2025-12-02 12:53:08'),
(23, 1, 'resume_created', 'Resume created: Harshana Weligampola Academic', NULL, NULL, NULL, '2025-12-02 13:44:47'),
(24, 1, 'resume_deleted', 'Resume deleted: Harshana Weligampola Academic', NULL, NULL, NULL, '2025-12-03 09:06:38'),
(25, 1, 'resume_created', 'Resume created: HW', NULL, NULL, NULL, '2025-12-03 11:25:33'),
(26, 1, 'resume_deleted', 'Resume deleted: HW', NULL, NULL, NULL, '2025-12-05 17:25:25'),
(27, 1, 'resume_created', 'Resume created: AI Generated Resume', NULL, NULL, NULL, '2025-12-05 17:48:04'),
(28, 1, 'resume_deleted', 'Resume deleted: AI Generated Resume', NULL, NULL, NULL, '2025-12-05 17:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `application_activity`
--

CREATE TABLE `application_activity` (
  `activity_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_timeline`
--

CREATE TABLE `application_timeline` (
  `timeline_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `event_type` enum('application_submitted','status_changed','interview_scheduled','interview_completed','offer_received','follow_up','note_added','other') NOT NULL,
  `event_title` varchar(255) NOT NULL,
  `event_description` text DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ats_scores`
--

CREATE TABLE `ats_scores` (
  `score_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `resume_id` int(11) NOT NULL,
  `resume_text` longtext DEFAULT NULL,
  `overall_score` int(11) NOT NULL CHECK (`overall_score` between 0 and 100),
  `job_description` text DEFAULT NULL,
  `job_title` varchar(255) DEFAULT NULL,
  `keywords_score` int(11) DEFAULT NULL,
  `content_structure_score` int(11) DEFAULT NULL,
  `contact_info_score` int(11) DEFAULT NULL,
  `experience_format_score` int(11) DEFAULT NULL,
  `technical_score` int(11) DEFAULT NULL,
  `formatting_score` int(11) DEFAULT NULL CHECK (`formatting_score` between 0 and 100),
  `experience_score` int(11) DEFAULT NULL CHECK (`experience_score` between 0 and 100),
  `education_score` int(11) DEFAULT NULL CHECK (`education_score` between 0 and 100),
  `analysis_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`analysis_results`)),
  `recommendations` text DEFAULT NULL,
  `matched_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`matched_keywords`)),
  `missing_keywords` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`missing_keywords`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `improvements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`improvements`)),
  `strengths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`strengths`)),
  `keywords_found` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keywords_found`)),
  `keywords_missing` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`keywords_missing`)),
  `file_type` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `application_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resume_id` int(11) DEFAULT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `job_location` varchar(255) DEFAULT NULL,
  `job_type` enum('Full-time','Part-time','Contract','Internship','Freelance') DEFAULT 'Full-time',
  `salary_range` varchar(100) DEFAULT NULL,
  `application_date` date NOT NULL,
  `status` enum('Applied','In Review','Interview Scheduled','Interview Completed','Offer Received','Accepted','Rejected','Withdrawn') DEFAULT 'Applied',
  `application_url` varchar(500) DEFAULT NULL,
  `job_description` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `interview_date` datetime DEFAULT NULL,
  `interview_location` varchar(500) DEFAULT NULL,
  `interview_notes` text DEFAULT NULL,
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `notification_type` enum('interview_reminder','follow_up_reminder','status_update','general') DEFAULT 'general',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `scheduled_for` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `recent_activity`
-- (See below for the actual view)
--
CREATE TABLE `recent_activity` (
`log_id` bigint(20)
,`user_id` int(11)
,`full_name` varchar(255)
,`email` varchar(255)
,`activity_type` varchar(100)
,`activity_description` text
,`ip_address` varchar(45)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Table structure for table `resumes`
--

CREATE TABLE `resumes` (
  `resume_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT 1,
  `resume_title` varchar(255) NOT NULL,
  `template_name` varchar(100) DEFAULT 'classic',
  `personal_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`personal_details`)),
  `summary_text` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `share_token` varchar(64) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `shared_at` timestamp NULL DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `last_viewed_at` timestamp NULL DEFAULT NULL,
  `version` int(11) DEFAULT 1,
  `file_url` varchar(500) DEFAULT NULL,
  `thumbnail_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_accessed` timestamp NULL DEFAULT NULL,
  `certifications` longtext DEFAULT NULL COMMENT 'JSON array of certifications',
  `languages` longtext DEFAULT NULL COMMENT 'JSON array or text for languages',
  `affiliations` longtext DEFAULT NULL,
  `research_interests` text DEFAULT NULL,
  `publications` longtext DEFAULT NULL,
  `grants` longtext DEFAULT NULL,
  `teaching` longtext DEFAULT NULL,
  `memberships` text DEFAULT NULL,
  `skills` longtext DEFAULT NULL COMMENT 'JSON array of skills with categories',
  `experience` longtext DEFAULT NULL COMMENT 'JSON array of work experience',
  `education` longtext DEFAULT NULL COMMENT 'JSON array of education'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `resumes`
--
DELIMITER $$
CREATE TRIGGER `after_resume_insert` AFTER INSERT ON `resumes` FOR EACH ROW BEGIN
    INSERT INTO activity_logs (user_id, activity_type, activity_description)
    VALUES (NEW.user_id, 'resume_created', CONCAT('Resume created: ', NEW.resume_title));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_resume_delete` BEFORE DELETE ON `resumes` FOR EACH ROW BEGIN
    INSERT INTO activity_logs (user_id, activity_type, activity_description)
    VALUES (OLD.user_id, 'resume_deleted', CONCAT('Resume deleted: ', OLD.resume_title));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `resume_downloads`
--

CREATE TABLE `resume_downloads` (
  `download_id` bigint(20) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_sections`
--

CREATE TABLE `resume_sections` (
  `section_id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `section_type` enum('experience','education','skills','certifications','projects','custom') NOT NULL,
  `section_title` varchar(255) DEFAULT NULL,
  `section_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`section_content`)),
  `display_order` int(11) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resume_shares`
--

CREATE TABLE `resume_shares` (
  `share_id` int(11) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `share_token` varchar(255) NOT NULL,
  `share_type` enum('link','email','linkedin','twitter') DEFAULT 'link',
  `is_active` tinyint(1) DEFAULT 1,
  `password_protected` tinyint(1) DEFAULT 0,
  `password_hash` varchar(255) DEFAULT NULL,
  `expiry_date` datetime DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `last_viewed` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `resume_shares`
--
DELIMITER $$
CREATE TRIGGER `after_share_view` AFTER UPDATE ON `resume_shares` FOR EACH ROW BEGIN
    IF NEW.view_count > OLD.view_count THEN
        UPDATE resume_shares
        SET last_viewed = NOW()
        WHERE share_id = NEW.share_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `resume_statistics`
-- (See below for the actual view)
--
CREATE TABLE `resume_statistics` (
`resume_id` int(11)
,`resume_title` varchar(255)
,`user_id` int(11)
,`user_name` varchar(255)
,`template_name` varchar(100)
,`status` enum('draft','published','archived')
,`created_at` timestamp
,`updated_at` timestamp
,`total_sections` bigint(21)
,`total_ats_checks` bigint(21)
,`best_ats_score` int(11)
,`total_shares` bigint(21)
,`total_views` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Table structure for table `resume_views`
--

CREATE TABLE `resume_views` (
  `view_id` bigint(20) NOT NULL,
  `resume_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `referer` varchar(500) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`session_data`)),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Triggers `sessions`
--
DELIMITER $$
CREATE TRIGGER `after_session_insert` AFTER INSERT ON `sessions` FOR EACH ROW BEGIN
    UPDATE users
    SET last_login = NOW()
    WHERE user_id = NEW.user_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `template_id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`template_id`, `template_name`, `template_display_name`, `template_category`, `description`, `preview_url`, `template_config`, `is_active`, `is_premium`, `usage_count`, `created_at`, `updated_at`) VALUES
(1, 'modern', 'Simple', 'professional', 'Clean, minimalist design with modern typography. Perfect for all industries and roles.', 'templates/modern.html', '{\"layout\":\"two-column\",\"font\":\"Arial\",\"color_scheme\":\"blue-accent\"}', 1, 0, 0, '2025-12-01 08:22:26', '2025-12-01 08:22:26'),
(2, 'professional', 'Professional', 'professional', 'Sophisticated serif design with boxed header. Ideal for finance, consulting, and formal corporate roles.', 'templates/professional.html', '{\"layout\":\"single-column\",\"font\":\"Georgia\",\"color_scheme\":\"black-white\"}', 1, 0, 0, '2025-12-01 08:22:26', '2025-12-01 08:22:26'),
(3, 'academic-standard', 'Academic', 'professional', 'Traditional academic CV for PhD candidates, postdocs, and tenure-track positions. Comprehensive publication and grant sections.', 'templates/academic-standard.html', '{\"layout\":\"single-column\",\"font\":\"Times New Roman\",\"color_scheme\":\"black-white\"}', 1, 0, 0, '2025-12-01 08:22:26', '2025-12-01 08:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
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
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `full_name`, `phone`, `date_of_birth`, `address_line1`, `address_line2`, `city`, `state`, `zip_code`, `country`, `professional_title`, `professional_summary`, `profile_photo_url`, `email_notifications`, `resume_tips`, `application_updates`, `profile_visibility`, `dark_mode`, `two_factor_enabled`, `email_verified`, `verification_token`, `reset_token`, `reset_token_expiry`, `account_status`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'mahbubkousar@gmail.com', '$2y$10$nN93t8lpPJ786zuyS3YTNeFbkdl2zWirGOMNiwI06xFq0wbwigkrm', 'Mahbubur Rahman Khan', '+8801753019520', NULL, 'Projonmo Hostel', NULL, 'Dhaka Cantonment', 'Dhaka', '1020', 'Bangladesh', 'BS Student', '', NULL, 1, 1, 1, 'private', 0, 0, 0, NULL, NULL, NULL, 'active', '2025-12-01 08:24:36', '2025-12-01 08:24:36', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_certifications`
--

CREATE TABLE `user_certifications` (
  `certification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resume_id` int(11) DEFAULT NULL,
  `certification_name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) DEFAULT NULL,
  `issue_date` varchar(50) DEFAULT NULL,
  `expiry_date` varchar(50) DEFAULT NULL,
  `credential_id` varchar(255) DEFAULT NULL,
  `credential_url` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_education`
--

CREATE TABLE `user_education` (
  `education_id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_experience`
--

CREATE TABLE `user_experience` (
  `experience_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `current_position` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_languages`
--

CREATE TABLE `user_languages` (
  `language_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resume_id` int(11) DEFAULT NULL,
  `language_name` varchar(100) NOT NULL,
  `proficiency_level` enum('elementary','limited_working','professional_working','full_professional','native') DEFAULT 'professional_working',
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_profile_summary`
-- (See below for the actual view)
--
CREATE TABLE `user_profile_summary` (
`user_id` int(11)
,`full_name` varchar(255)
,`email` varchar(255)
,`professional_title` varchar(255)
,`created_at` timestamp
,`last_login` timestamp
,`total_resumes` bigint(21)
,`total_education` bigint(21)
,`total_experience` bigint(21)
,`total_skills` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `user_skills`
--

CREATE TABLE `user_skills` (
  `skill_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_name` varchar(255) NOT NULL,
  `skill_category` varchar(100) DEFAULT NULL,
  `proficiency_level` enum('beginner','intermediate','advanced','expert') DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `recent_activity`
--
DROP TABLE IF EXISTS `recent_activity`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `recent_activity`  AS SELECT `al`.`log_id` AS `log_id`, `al`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, `al`.`activity_type` AS `activity_type`, `al`.`activity_description` AS `activity_description`, `al`.`ip_address` AS `ip_address`, `al`.`created_at` AS `created_at` FROM (`activity_logs` `al` left join `users` `u` on(`al`.`user_id` = `u`.`user_id`)) ORDER BY `al`.`created_at` DESC LIMIT 0, 100 ;

-- --------------------------------------------------------

--
-- Structure for view `resume_statistics`
--
DROP TABLE IF EXISTS `resume_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `resume_statistics`  AS SELECT `r`.`resume_id` AS `resume_id`, `r`.`resume_title` AS `resume_title`, `r`.`user_id` AS `user_id`, `u`.`full_name` AS `user_name`, `r`.`template_name` AS `template_name`, `r`.`status` AS `status`, `r`.`created_at` AS `created_at`, `r`.`updated_at` AS `updated_at`, count(distinct `rs`.`section_id`) AS `total_sections`, count(distinct `ats`.`score_id`) AS `total_ats_checks`, coalesce(max(`ats`.`overall_score`),0) AS `best_ats_score`, count(distinct `sh`.`share_id`) AS `total_shares`, coalesce(sum(`sh`.`view_count`),0) AS `total_views` FROM ((((`resumes` `r` left join `users` `u` on(`r`.`user_id` = `u`.`user_id`)) left join `resume_sections` `rs` on(`r`.`resume_id` = `rs`.`resume_id`)) left join `ats_scores` `ats` on(`r`.`resume_id` = `ats`.`resume_id`)) left join `resume_shares` `sh` on(`r`.`resume_id` = `sh`.`resume_id`)) GROUP BY `r`.`resume_id` ;

-- --------------------------------------------------------

--
-- Structure for view `user_profile_summary`
--
DROP TABLE IF EXISTS `user_profile_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_profile_summary`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`full_name` AS `full_name`, `u`.`email` AS `email`, `u`.`professional_title` AS `professional_title`, `u`.`created_at` AS `created_at`, `u`.`last_login` AS `last_login`, count(distinct `r`.`resume_id`) AS `total_resumes`, count(distinct `ue`.`education_id`) AS `total_education`, count(distinct `uex`.`experience_id`) AS `total_experience`, count(distinct `us`.`skill_id`) AS `total_skills` FROM ((((`users` `u` left join `resumes` `r` on(`u`.`user_id` = `r`.`user_id`)) left join `user_education` `ue` on(`u`.`user_id` = `ue`.`user_id`)) left join `user_experience` `uex` on(`u`.`user_id` = `uex`.`user_id`)) left join `user_skills` `us` on(`u`.`user_id` = `us`.`user_id`)) WHERE `u`.`account_status` = 'active' GROUP BY `u`.`user_id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `application_activity`
--
ALTER TABLE `application_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_application_id` (`application_id`);

--
-- Indexes for table `application_timeline`
--
ALTER TABLE `application_timeline`
  ADD PRIMARY KEY (`timeline_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_event_date` (`event_date`);

--
-- Indexes for table `ats_scores`
--
ALTER TABLE `ats_scores`
  ADD PRIMARY KEY (`score_id`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_overall_score` (`overall_score`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `resume_id` (`resume_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_application_date` (`application_date`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_scheduled_for` (`scheduled_for`);

--
-- Indexes for table `resumes`
--
ALTER TABLE `resumes`
  ADD PRIMARY KEY (`resume_id`),
  ADD UNIQUE KEY `share_token` (`share_token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_updated_at` (`updated_at`),
  ADD KEY `idx_share_token` (`share_token`);

--
-- Indexes for table `resume_downloads`
--
ALTER TABLE `resume_downloads`
  ADD PRIMARY KEY (`download_id`),
  ADD KEY `idx_resume_downloads` (`resume_id`,`downloaded_at`);

--
-- Indexes for table `resume_sections`
--
ALTER TABLE `resume_sections`
  ADD PRIMARY KEY (`section_id`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_section_type` (`section_type`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `resume_shares`
--
ALTER TABLE `resume_shares`
  ADD PRIMARY KEY (`share_id`),
  ADD UNIQUE KEY `share_token` (`share_token`),
  ADD KEY `idx_resume_id` (`resume_id`),
  ADD KEY `idx_share_token` (`share_token`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `resume_views`
--
ALTER TABLE `resume_views`
  ADD PRIMARY KEY (`view_id`),
  ADD KEY `idx_resume_views` (`resume_id`,`viewed_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
  ADD PRIMARY KEY (`template_id`),
  ADD UNIQUE KEY `template_name` (`template_name`),
  ADD KEY `idx_template_name` (`template_name`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_account_status` (`account_status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `user_certifications`
--
ALTER TABLE `user_certifications`
  ADD PRIMARY KEY (`certification_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_resume_id` (`resume_id`);

--
-- Indexes for table `user_education`
--
ALTER TABLE `user_education`
  ADD PRIMARY KEY (`education_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_display_order` (`display_order`);

--
-- Indexes for table `user_experience`
--
ALTER TABLE `user_experience`
  ADD PRIMARY KEY (`experience_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_display_order` (`display_order`),
  ADD KEY `idx_current_position` (`current_position`);

--
-- Indexes for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD PRIMARY KEY (`language_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_resume_id` (`resume_id`);

--
-- Indexes for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`skill_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_skill_category` (`skill_category`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `application_activity`
--
ALTER TABLE `application_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_timeline`
--
ALTER TABLE `application_timeline`
  MODIFY `timeline_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ats_scores`
--
ALTER TABLE `ats_scores`
  MODIFY `score_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resumes`
--
ALTER TABLE `resumes`
  MODIFY `resume_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `resume_downloads`
--
ALTER TABLE `resume_downloads`
  MODIFY `download_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `resume_sections`
--
ALTER TABLE `resume_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_shares`
--
ALTER TABLE `resume_shares`
  MODIFY `share_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resume_views`
--
ALTER TABLE `resume_views`
  MODIFY `view_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
  MODIFY `template_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_certifications`
--
ALTER TABLE `user_certifications`
  MODIFY `certification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_education`
--
ALTER TABLE `user_education`
  MODIFY `education_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_experience`
--
ALTER TABLE `user_experience`
  MODIFY `experience_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_languages`
--
ALTER TABLE `user_languages`
  MODIFY `language_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `skill_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `application_activity`
--
ALTER TABLE `application_activity`
  ADD CONSTRAINT `application_activity_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `application_timeline`
--
ALTER TABLE `application_timeline`
  ADD CONSTRAINT `application_timeline_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `ats_scores`
--
ALTER TABLE `ats_scores`
  ADD CONSTRAINT `ats_scores_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ats_scores_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `job_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_applications_ibfk_2` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `job_applications` (`application_id`) ON DELETE CASCADE;

--
-- Constraints for table `resumes`
--
ALTER TABLE `resumes`
  ADD CONSTRAINT `resumes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resumes_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `templates` (`template_id`) ON DELETE SET NULL;

--
-- Constraints for table `resume_downloads`
--
ALTER TABLE `resume_downloads`
  ADD CONSTRAINT `resume_downloads_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_sections`
--
ALTER TABLE `resume_sections`
  ADD CONSTRAINT `resume_sections_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_shares`
--
ALTER TABLE `resume_shares`
  ADD CONSTRAINT `resume_shares_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE;

--
-- Constraints for table `resume_views`
--
ALTER TABLE `resume_views`
  ADD CONSTRAINT `resume_views_ibfk_1` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_certifications`
--
ALTER TABLE `user_certifications`
  ADD CONSTRAINT `user_certifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_certifications_ibfk_2` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_education`
--
ALTER TABLE `user_education`
  ADD CONSTRAINT `user_education_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_experience`
--
ALTER TABLE `user_experience`
  ADD CONSTRAINT `user_experience_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_languages`
--
ALTER TABLE `user_languages`
  ADD CONSTRAINT `user_languages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_languages_ibfk_2` FOREIGN KEY (`resume_id`) REFERENCES `resumes` (`resume_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `clean_expired_sessions_daily` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-22 15:30:12' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM sessions WHERE expires_at < NOW();
END$$

CREATE DEFINER=`root`@`localhost` EVENT `clean_old_activity_logs_monthly` ON SCHEDULE EVERY 1 MONTH STARTS '2025-11-22 15:30:12' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
END$$

CREATE DEFINER=`root`@`localhost` EVENT `clean_expired_shares_daily` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-22 15:30:12' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    UPDATE resume_shares
    SET is_active = FALSE
    WHERE expiry_date IS NOT NULL AND expiry_date < NOW() AND is_active = TRUE;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
