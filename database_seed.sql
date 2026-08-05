-- Synthetic/public seed data only. Do not add user, resume, or activity data.
INSERT INTO `templates`
  (`template_id`, `template_name`, `template_display_name`, `template_category`,
   `description`, `preview_url`, `template_config`, `is_active`, `is_premium`, `usage_count`)
VALUES
  (1, 'modern', 'Simple', 'professional',
   'Clean, minimalist design with modern typography.', 'templates/modern.html',
   '{"layout":"two-column","font":"Arial","color_scheme":"blue-accent"}', 1, 0, 0),
  (2, 'professional', 'Professional', 'professional',
   'Sophisticated design for corporate roles.', 'templates/professional.html',
   '{"layout":"single-column","font":"Georgia","color_scheme":"black-white"}', 1, 0, 0),
  (3, 'academic-standard', 'Academic', 'academic',
   'Traditional academic CV for research and faculty roles.', 'templates/academic-standard.html',
   '{"layout":"single-column","font":"Times New Roman","color_scheme":"black-white"}', 1, 0, 0)
ON DUPLICATE KEY UPDATE
  `template_display_name` = VALUES(`template_display_name`),
  `template_category` = VALUES(`template_category`),
  `description` = VALUES(`description`),
  `preview_url` = VALUES(`preview_url`),
  `template_config` = VALUES(`template_config`),
  `is_active` = VALUES(`is_active`);
