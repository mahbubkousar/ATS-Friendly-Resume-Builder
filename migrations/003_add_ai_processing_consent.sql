ALTER TABLE `users`
  ADD COLUMN `ai_processing_consent_at` timestamp NULL DEFAULT NULL AFTER `two_factor_enabled`,
  ADD COLUMN `ai_consent_version` varchar(20) DEFAULT NULL AFTER `ai_processing_consent_at`;
