# ResumeSync

ResumeSync is a PHP/MySQL resume builder with ATS scoring, Google
Gemini-assisted editing, resume sharing, and job-application tracking.

## Requirements

- PHP 8.0+ with `mysqli`, `curl`, `fileinfo`, and `zip`
- MySQL 8+ or compatible MariaDB
- Apache/XAMPP or PHP's development server

## Installation

1. Create a database and application user:

   ```sql
   CREATE DATABASE resumesync_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'resumesync_app'@'localhost' IDENTIFIED BY 'replace-with-a-strong-password';
   GRANT ALL PRIVILEGES ON resumesync_db.* TO 'resumesync_app'@'localhost';
   FLUSH PRIVILEGES;
   ```

2. Import the canonical schema and public template seed:

   ```bash
   mysql -u resumesync_app -p resumesync_db < resumesync_db_structure.sql
   mysql -u resumesync_app -p resumesync_db < database_seed.sql
   ```

3. Create the environment file:

   ```bash
   cp .env.example .env
   ```

   Update `.env` with the database password, Gemini API key, and a supported
   Gemini model. `APP_URL` must match the URL used to open the application.

4. Start the application:

   ```bash
   APP_ENV=development php -S 127.0.0.1:8080
   ```

   Open `http://127.0.0.1:8080`. For XAMPP, place the repository under the web
   root and set `APP_URL` to its full local URL instead.

### Existing databases

Back up the database and apply the files in [`migrations`](migrations/README.md)
in numeric order. Do not re-import the full schema.

## Main features

- Resume editing with modern, professional, and academic templates
- ATS scoring and optional Gemini-assisted workflows
- Explicit, revocable consent before external AI processing
- Public resume sharing and view/download statistics
- Profile and job-application management
- Session, CSRF, upload, rate-limit, and ownership protections

## Tests

```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
find js -name '*.js' -print0 | xargs -0 -n1 node --check
APP_ENV=test GEMINI_API_KEY=test-key GEMINI_MODEL=test-model php tests/run.php
bash tests/http-smoke.sh
```

GitHub Actions runs these checks on pushes and pull requests. Additional design
and API documentation is available in [`docs`](docs/README.md).

Never commit `.env`, API keys, production database exports, resumes, or logs.

## License

Copyright © 2025 ResumeSync. All rights reserved.
