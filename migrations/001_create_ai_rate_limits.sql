CREATE TABLE IF NOT EXISTS `ai_rate_limits` (
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
