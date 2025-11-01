ATS-Friendly Resume Builder
A lightweight, modern web application for creating ATS (Applicant Tracking System) optimized resumes. Built with PHP, MySQL, and Tailwind CSS.

🚀 Features
📝 Smart Resume Builder - Guided form-based resume creation

📊 ATS Score Analysis - Real-time compatibility scoring

🎯 Keyword Optimization - Industry-specific suggestions

📱 Responsive Design - Works on all devices

💾 Save & Manage - Multiple resume versions

🔒 Secure Authentication - User registration and login

🎨 Professional Templates - ATS-optimized layouts

🛠 Technology Stack
Frontend
HTML5 - Semantic markup and structure

Tailwind CSS - Utility-first CSS framework

JavaScript - Client-side interactivity and validation

AJAX - Asynchronous operations

Backend
PHP - Server-side logic and processing

MySQL - Database management

PDO - Secure database interactions

Security
Password Hashing - bcrypt for secure storage

Input Validation - Client and server-side

SQL Injection Prevention - Prepared statements

XSS Protection - Output escaping

📁 Project Structure
text
ats-resume-builder/
├── index.php                 # Landing page
├── dashboard.php            # User dashboard
├── auth/                    # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── resume/                  # Resume management
│   ├── create.php
│   ├── edit.php
│   ├── view.php
│   └── list.php
├── api/                     # API endpoints
│   ├── save-resume.php
│   ├── analyze-ats.php
│   └── export-pdf.php
├── includes/                # Core components
│   ├── header.php
│   ├── footer.php
│   ├── session.php
│   └── database.php
├── css/                     # Stylesheets
│   └── custom.css
├── js/                      # JavaScript
│   ├── resume-builder.js
│   └── validation.js
└── sql/                     # Database
    └── database.sql
⚡ Quick Start
Prerequisites
PHP 7.4 or higher

MySQL 5.7 or higher

Web server (Apache/Nginx)

Modern web browser

Installation
Clone or Download the Project

bash
# Create project directory
mkdir ats-resume-builder
cd ats-resume-builder
Setup Database

sql
-- Create database (run in phpMyAdmin or MySQL CLI)
CREATE DATABASE ats_resume_builder;

-- Import the SQL file located in sql/database.sql
-- Or run the schema manually from the file
Configure Database Connection
Edit includes/database.php with your database credentials:

php
private $host = "localhost";
private $db_name = "ats_resume_builder";
private $username = "your_username";
private $password = "your_password";
Setup Web Server

Place files in your web server root directory

For XAMPP: C:/xampp/htdocs/ats-resume-builder/

For WAMP: C:/wamp/www/ats-resume-builder/

For LAMP: /var/www/html/ats-resume-builder/

Access Application
Open your browser and navigate to:

text
http://localhost/ats-resume-builder
Default Login (if using sample data)
Username: demo

Password: password

🎯 How to Use
1. Create an Account
Navigate to the registration page

Fill in your details

Verify your email (basic validation)

2. Build Your Resume
Click "Create New Resume"

Fill in personal information

Add work experience and education

Include skills and certifications

Use real-time ATS feedback to optimize

3. Analyze ATS Score
Click "Analyze ATS Score" during editing

View detailed feedback and suggestions

Improve your resume based on recommendations

4. Save and Export
Save your resume to your account

View and edit later

Export as PDF (when implemented)

🔧 Configuration
Database Settings
Edit includes/database.php:

php
private $host = "localhost";
private $db_name = "ats_resume_builder";
private $username = "your_mysql_username";
private $password = "your_mysql_password";
Session Configuration
Default session settings in includes/session.php can be modified for:

Session lifetime

Security settings

Cookie parameters

📊 ATS Scoring Algorithm
The application evaluates resumes based on:

Contact Information - Presence of email and phone

Keywords - Industry-specific terms and action verbs

Structure - Proper section organization

Formatting - ATS-friendly formatting

Content Quality - Quantifiable achievements

Score Ranges
80-100% - Excellent ATS compatibility

60-79% - Good, with room for improvement

Below 60% - Needs significant optimization

🚀 API Endpoints
Save Resume
URL: /api/save-resume.php

Method: POST

Data: JSON resume data

Response: Success status and resume ID

Analyze ATS Score
URL: /api/analyze-ats.php

Method: POST

Data: Resume content

Response: Score and improvement suggestions

🛡 Security Features
Password Hashing - Uses PHP's password_hash()

SQL Injection Prevention - PDO prepared statements

XSS Protection - Input validation and output escaping

Session Management - Secure session handling

CSRF Protection - Basic token validation

📱 Browser Support
Chrome 60+

Firefox 55+

Safari 12+

Edge 79+

🔮 Future Enhancements
PDF export functionality

Resume templates gallery

Advanced ATS analytics

Cover letter builder

Job search integration

Multi-language support

Social media integration

🐛 Troubleshooting
Common Issues
Database Connection Failed

Check MySQL service is running

Verify database credentials

Ensure database exists

Page Not Loading

Check file permissions

Verify PHP is installed and running

Check web server configuration

Session Issues

Clear browser cookies

Check PHP session configuration

Verify write permissions for session directory

Error Logging
Check your PHP error logs for detailed debugging information.

🤝 Contributing
Fork the project

Create a feature branch (git checkout -b feature/AmazingFeature)

Commit your changes (git commit -m 'Add some AmazingFeature')

Push to the branch (git push origin feature/AmazingFeature)

Open a Pull Request

📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

🙏 Acknowledgments
Tailwind CSS for the utility-first CSS framework

PHP community for extensive documentation

Modern web standards for cross-browser compatibility

📞 Support
For support and questions:

Check the documentation above

Review the code comments

Create an issue in the project repository

Built with ❤️ for job seekers everywhere

Make your resume stand out in the digital age!
