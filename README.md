# ResumeSync

ResumeSync is a framework-free PHP/MySQL resume builder with ATS analysis,
Google Gemini-assisted editing, public resume sharing, profile management, and
job-application tracking.

## Requirements

- PHP 8.0+ with `mysqli`, `curl`, `fileinfo`, and `zip`
- MySQL 8+ or a compatible MariaDB release
- Apache with `mod_headers` and `mod_rewrite`, or PHP's development server
- Node.js 20+ only for JavaScript syntax checks

## Installation

1. Create the database and import the one canonical schema:

   ```bash
   mysql -u root -p -e "CREATE DATABASE resumesync_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
   mysql -u root -p resumesync_db < resumesync_db_structure.sql
   mysql -u root -p resumesync_db < database_seed.sql
   ```

2. Copy `.env.example` to `.env` and configure:

   ```env
   APP_ENV=development
   APP_URL=http://localhost/ATS
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=resumesync_db
   DB_USER=resumesync_app
   DB_PASS=use-a-strong-password
   GEMINI_API_KEY=your-api-key
   GEMINI_MODEL=a-model-enabled-for-your-key
   ```

   `APP_URL` may point to the domain root or any subdirectory. Production mode
   rejects root or blank-password database accounts.

3. Serve the repository through Apache/XAMPP, or locally with:

   ```bash
   APP_ENV=development php -S 127.0.0.1:8080
   ```

### Existing databases

Do not re-import the full schema. Back up the database and apply files in
[`migrations`](migrations/README.md) in numeric order.

## Architecture

```text
Browser pages and shared JavaScript
              ↓
PHP pages / JSON API bootstrap
              ↓
Services and repositories
              ↓
MySQL                  Google Gemini
```

Important shared components:

- `config/` — environment, application URL, database, session, and AI client
- `includes/api-bootstrap.php` — JSON errors, authentication, and HTTP methods
- `includes/editor-bootstrap.php` — shared editor initialization
- `repositories/` and `services/` — dashboard data and view-model logic
- `js/shared/` — deployment URLs, CSRF, editor controls, ATS flow, and AI consent
- `resumesync_db_structure.sql` — canonical schema for new installations
- `database_seed.sql` — synthetic/public template data only
- `migrations/` — ordered changes for existing installations

## Feature status

Implemented:

- Registration, login, protected sessions, and logout
- Resume editing with modern, professional, and academic templates
- ATS scoring and Gemini-assisted resume workflows
- Explicit, revocable consent before external AI processing
- Resume sharing with public tokens and download/view statistics
- Profile education/experience management
- Job-application tracking, timelines, and notification updates

The ATS converter navigation item is currently hidden while its user experience
is being finalized. Photo upload, two-factor authentication, email verification,
and account deletion fields exist in the schema but are not complete workflows.

## AI privacy

AI features are optional. Before the first AI request, the application explains
that the minimum required resume, prompt, or job-description data will be sent
to Google Gemini. Consent is stored with a policy version and can be revoked
from the “AI privacy” control. Server endpoints reject AI processing without a
current consent record.

Operators must configure their Google project retention settings and privacy
notice for their jurisdiction. Do not send information that is unnecessary for
the requested result.

## Security controls

- Prepared SQL statements and ownership-scoped resource queries
- CSRF protection for authenticated mutations
- Strict session lifecycle and protected cookie settings
- Upload validation, request limits, and AI/authentication rate limits
- Content Security Policy, SRI-pinned CDN assets, and hardened headers
- Server-side request validation and generic production errors
- Environment-managed credentials and model selection

Never commit `.env`, user data, production exports, resumes, tokens, or logs.

## Testing

Run the local checks:

```bash
find . -name '*.php' -not -path './vendor/*' -print0 | xargs -0 -n1 php -l
find js -name '*.js' -print0 | xargs -0 -n1 node --check
APP_ENV=test GEMINI_API_KEY=test-key GEMINI_MODEL=test-model php tests/run.php
bash tests/http-smoke.sh
```

GitHub Actions runs the same syntax, unit, architecture, and HTTP contract
checks for every push and pull request.

## Documentation

Detailed design and feature documentation is under [`docs`](docs/README.md).
When implementation and documentation disagree, this README and the executable
tests describe the supported setup.

## License

Copyright © 2025 ResumeSync. All rights reserved.
