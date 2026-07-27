CREATE TABLE IF NOT EXISTS `auth_rate_limits` (
  `action_name` varchar(32) NOT NULL,
  `limit_key` char(64) NOT NULL,
  `window_start` int unsigned NOT NULL,
  `attempt_count` int unsigned NOT NULL DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`action_name`, `limit_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
