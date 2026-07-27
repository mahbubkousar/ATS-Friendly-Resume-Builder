# ResumeSync - AI-Powered ATS Resume Builder

AI-powered Applicant Tracking System (ATS) resume builder and career management platform.


## Quick Start

### 1. Setup Database
```bash
# Open MySQL
mysql -u root -p

# Create database
CREATE DATABASE resumesync_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
mysql -u root -p < create_database.sql

# Existing installations: apply security counter tables
mysql -u root -p resumesync_db < migrations/001_create_ai_rate_limits.sql
mysql -u root -p resumesync_db < migrations/002_create_auth_rate_limits.sql
```

### 2. Configure Database Connection
Edit `config/database.php` if needed (default settings work with standard XAMPP):
- Host: `localhost`
- User: `root`
- Password: (empty)
- Database: `resumesync_db`

### 3. Start XAMPP
- Start Apache
- Start MySQL

### 4. Access the Application
```
Homepage: http://localhost/ATS/
Login: http://localhost/ATS/login.php
Register: http://localhost/ATS/register.php
Dashboard: http://localhost/ATS/dashboard.php
```

## Features

### Authentication
- User registration with multi-step form
- Secure login with password hashing
- Session-based authentication
- Remember me functionality
- Logout capability
- Protected pages (dashboard, editors, score checker)

### User Management
- Personal information storage
- Education history
- Work experience
- Profile management

### Security
- Bcrypt password hashing
- SQL injection prevention (prepared statements)
- XSS protection (output escaping)
- Session security
- Access control on protected pages

## File Structure

```
/ATS/
├── config/               # Configuration files
│   ├── database.php      # DB connection
│   └── session.php       # Session management
├── api/                  # API endpoints
│   └── register.php      # Registration handler
├── login.php             # Login page
├── register.php          # Registration page
├── dashboard.php         # User dashboard (protected)
├── editor.php            # Resume editor (protected)
├── ai-editor.php         # AI editor (protected)
├── score-checker.php     # ATS checker (protected)
├── logout.php            # Logout handler
└── database_schema.sql   # Database schema
```

## Usage

### Register New User
1. Go to `http://localhost/ATS/register.php`
2. Complete the 4-step registration:
   - Step 1: Account (email, password)
   - Step 2: Personal (phone, address, etc.)
   - Step 3: Education (optional)
   - Step 4: Experience (optional)
3. Submit and auto-login

### Login
1. Go to `http://localhost/ATS/login.php`
2. Enter email and password
3. Optional: Check "Remember me"
4. Access dashboard and protected features

### Logout
1. Navigate to Dashboard → Settings tab
2. Click the red "Logout" button
3. Session destroyed, redirected to login

## Protected Pages

These pages require authentication:
- `dashboard.php` - User dashboard
- `editor.php` - Resume editor
- `ai-editor.php` - AI-powered editor
- `score-checker.php` - ATS score checker

Unauthenticated access automatically redirects to login page.

## Database Schema

### users
- User accounts with hashed passwords
- Personal information (name, email, phone, address)
- Professional details (title, bio)

### education
- Linked to users
- Institution, degree, field of study
- Start/end dates, GPA

### work_experience
- Linked to users
- Company, job title, location
- Start/end dates, current job flag
- Job description

## API Endpoints

### POST /api/register.php
Register new user account
- **Input**: JSON with user data
- **Output**: Success/error message
- **Side Effect**: Creates session on success

## Development

### Adding New Protected Pages
```php
<?php
require_once 'config/session.php';
requireLogin();
?>
<!DOCTYPE html>
<!-- Your page content -->
```

### Accessing Current User
```php
<?php
$user = getCurrentUser();
echo $user['fullname']; // User's name
echo $user['email'];    // User's email
echo $user['id'];       // User's ID
?>
```

## Troubleshooting

### "Database connection failed"
- Check MySQL is running in XAMPP
- Verify credentials in `config/database.php`
- Ensure database `resumesync_db` exists

### Can't login after registration
- Check browser console for errors
- Verify user was created: `SELECT * FROM users;`
- Clear browser cache and cookies

### Redirected to login immediately
- Check session is working: `var_dump($_SESSION);`
- Verify PHP session support is enabled
- Check file permissions on session directory

## Documentation

- `DATABASE_SETUP.md` - Detailed database setup instructions
- `IMPLEMENTATION_SUMMARY.md` - Complete implementation details

## Security Notes

- Never commit `config/database.php` with production credentials
- Never commit database exports containing user accounts, password hashes, resumes, tokens, or activity logs
- Keep tracked SQL files limited to schema definitions and synthetic/public seed data
- Store local exports under `database-backups/` or name them `*.local.sql` so Git ignores them
- Always use HTTPS in production
- Keep CSRF protection enabled for state-changing requests
- Keep authentication and AI rate limits aligned with production capacity
- Add email verification for new accounts

## License

Copyright © 2025 ResumeSync. All rights reserved.
