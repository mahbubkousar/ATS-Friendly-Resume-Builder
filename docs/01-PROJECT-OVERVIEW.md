# ResumeSync - Project Overview

## Table of Contents
1. [Project Description](#project-description)
2. [Key Features](#key-features)
3. [Technology Stack](#technology-stack)
4. [Project Structure](#project-structure)
5. [System Architecture](#system-architecture)

---

## Project Description

**ResumeSync** is a comprehensive web application for creating ATS (Applicant Tracking System) optimized resumes with AI assistance. It helps job seekers create professional resumes, check ATS compatibility scores, and track job applications.

### Purpose
- Create ATS-friendly resumes using professional templates
- Analyze resume compatibility with job descriptions
- Generate resumes using AI conversation (Google Gemini)
- Track job applications and manage career progress
- Download resumes as PDF

---

## Key Features

### 1. **User Authentication System**
- User registration and login
- Session-based authentication
- Password hashing with bcrypt
- Remember me functionality
- Secure session management

### 2. **Resume Editor (Multiple Templates)**
- **Modern Template**: Clean, minimalist design
- **Professional Template**: Sophisticated serif design
- **Academic Template**: Traditional CV for researchers
- Real-time preview
- Dynamic form fields
- Auto-save functionality

### 3. **AI-Powered Resume Builder**
- Conversational interface using Google Gemini API
- Natural language processing
- Automatic data extraction
- Template-specific guidance
- Real-time preview updates

### 4. **ATS Score Checker**
- Upload resume (PDF/DOCX)
- Compare with job description
- AI-powered analysis using Gemini
- Detailed scoring breakdown
- Improvement suggestions
- Keyword matching

### 5. **Job Application Tracker**
- Track multiple job applications
- Status management (Applied, Interview, Offer, etc.)
- Timeline visualization
- Priority levels
- Notes and follow-ups

### 6. **Resume Management**
- Create multiple resumes
- Edit existing resumes
- Delete resumes
- Download as PDF
- Share resumes (public links)

---

## Technology Stack

### **Frontend Technologies**
| Technology | Purpose | Version/CDN |
|-----------|---------|-------------|
| **HTML5** | Structure | Native |
| **CSS3** | Styling | Native |
| **Tailwind CSS** | Utility-first CSS | CDN v3.3.0 |
| **JavaScript (ES6+)** | Client-side logic | Native |
| **Font Awesome** | Icons | CDN v6.5.1 |
| **Google Fonts** | Typography | Montserrat, Inter |

### **Backend Technologies**
| Technology | Purpose | Notes |
|-----------|---------|-------|
| **PHP 8.x** | Server-side logic | Object-oriented approach |
| **MySQL** | Database | MariaDB compatible |
| **MySQLi** | Database driver | Prepared statements |
| **cURL** | API requests | For Gemini API |

### **Third-Party APIs**
| API | Purpose | Integration |
|-----|---------|-------------|
| **Google Gemini API** | AI resume generation & analysis | REST API via cURL |
| Environment-configured Gemini model | Natural language processing | Validated JSON responses |

### **AJAX & Communication**
- **Fetch API**: Modern promise-based HTTP requests
- **XMLHttpRequest**: Legacy support (minimal use)
- **jQuery AJAX**: Used in some modules
- **JSON**: Data interchange format

---

## Project Structure

```
ATS/
├── config/
│   ├── database.php          # Database connection
│   ├── session.php           # Session management
│   └── gemini.php            # Gemini API configuration
│
├── api/                      # Backend API endpoints
│   ├── save-resume.php       # Save/update resume
│   ├── load-resume.php       # Load resume data
│   ├── ai-conversation.php   # AI chat interface
│   ├── analyze-ats-score.php # ATS analysis
│   ├── add-application.php   # Job application CRUD
│   └── ...
│
├── templates/                # Resume templates (HTML)
│   ├── modern.html
│   ├── professional.html
│   └── academic-standard.html
│
├── js/                       # JavaScript files
│   ├── editor.js             # Manual editor logic
│   ├── ai-editor.js          # AI editor logic
│   ├── dashboard.js          # Dashboard functionality
│   ├── score-checker.js      # ATS checker
│   └── ...
│
├── css/                      # Stylesheets
│   ├── styles.css            # Global styles
│   ├── editor.css            # Editor styles
│   ├── dashboard.css         # Dashboard styles
│   └── ...
│
├── includes/                 # Reusable components
│   └── editor-modals.php     # Modal templates
│
├── index.php                 # Landing page
├── login.php                 # Login page
├── register.php              # Registration page
├── dashboard.php             # User dashboard
├── editor.php                # Resume editor (router)
├── editor-modern.php         # Modern template editor
├── editor-professional.php   # Professional template editor
├── editor-academic-standard.php # Academic template editor
├── ai-editor.php             # AI-powered editor
├── score-checker.php         # ATS score checker
└── ...
```

---

## System Architecture

### **Three-Tier Architecture**

```
┌─────────────────────────────────────────────┐
│         PRESENTATION LAYER                  │
│  (HTML, CSS, JavaScript, AJAX)              │
│  - User Interface                           │
│  - Client-side validation                   │
│  - Dynamic updates                          │
└─────────────────┬───────────────────────────┘
                  │
                  │ HTTP/AJAX Requests
                  ▼
┌─────────────────────────────────────────────┐
│         APPLICATION LAYER                   │
│  (PHP - Business Logic)                     │
│  - Authentication (session.php)             │
│  - API Endpoints (api/*.php)                │
│  - Data validation                          │
│  - External API integration (Gemini)        │
└─────────────────┬───────────────────────────┘
                  │
                  │ MySQLi
                  ▼
┌─────────────────────────────────────────────┐
│         DATA LAYER                          │
│  (MySQL Database)                           │
│  - Users                                    │
│  - Resumes                                  │
│  - ATS Scores                               │
│  - Job Applications                         │
└─────────────────────────────────────────────┘
```

### **Data Flow Example (Creating a Resume)**

```
1. User fills form → 2. JavaScript collects data →
3. AJAX POST to api/save-resume.php →
4. PHP validates & processes →
5. MySQL INSERT/UPDATE →
6. JSON response →
7. JavaScript updates UI
```

---

## Database Schema (Key Tables)

### **users**
- User authentication and profile data
- Fields: `user_id`, `email`, `password_hash`, `full_name`, etc.

### **resumes**
- Resume content storage
- Fields: `resume_id`, `user_id`, `template_name`, `personal_details`, `experience`, `education`, `skills` (JSON)

### **ats_scores**
- ATS analysis results
- Fields: `score_id`, `resume_id`, `overall_score`, `keywords_found`, `improvements` (JSON)

### **job_applications**
- Job tracking
- Fields: `application_id`, `user_id`, `company_name`, `job_title`, `status`

---

## Security Features

1. **Authentication**
   - Session-based auth
   - Password hashing (bcrypt)
   - Login verification on protected pages

2. **SQL Injection Prevention**
   - Prepared statements
   - Parameter binding
   - Input validation

3. **XSS Prevention**
   - `htmlspecialchars()` for output
   - JSON encoding for data

4. **CSRF Protection**
   - Session validation
   - Origin checking (implicit)

5. **File Upload Security**
   - File type validation
   - Size limits
   - Secure file handling

---

## Deployment Environment

- **Web Server**: Apache (XAMPP)
- **PHP Version**: 8.0+
- **Database**: MySQL/MariaDB
- **Document Root**: `/Applications/XAMPP/xamppfiles/htdocs/ATS/`
- **Base URL**: `http://localhost/ATS/`

---

## Next Steps

For detailed implementation guides, see:
- [Authentication System](02-AUTHENTICATION-SYSTEM.md)
- [Gemini API Integration](03-GEMINI-API-INTEGRATION.md)
- [Editor & Dynamic Updates](04-EDITOR-SYSTEM.md)
- [API Endpoints](05-API-ENDPOINTS.md)
- [Template System](06-TEMPLATE-SYSTEM.md)
- [Creating New Features](07-ADDING-NEW-FEATURES.md)
