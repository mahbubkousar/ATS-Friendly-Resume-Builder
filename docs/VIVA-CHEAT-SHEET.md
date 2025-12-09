# Viva Defense - Quick Reference Cheat Sheet

## 🎯 Project Elevator Pitch (30 seconds)

"ResumeSync is a full-stack web application for creating ATS-optimized resumes. It uses PHP and MySQL on the backend, HTML/CSS/JavaScript on the frontend, and integrates Google Gemini AI for intelligent resume building and analysis. Key features include multiple professional templates, real-time preview, AI-powered content generation, ATS score checking, and job application tracking."

---

## 🔑 Key Numbers to Remember

- **3** resume templates (Modern, Professional, Academic)
- **13+** API endpoints
- **11** database tables
- **3-tier** architecture (Presentation, Application, Data)
- **100** maximum ATS score
- **5** security measures implemented

---

## 📋 Technology Stack - Quick Reference

| Category | Technology | Why? |
|----------|-----------|------|
| **Frontend** | HTML5, CSS3, JavaScript ES6+ | Industry standard |
| **Styling** | Tailwind CSS | Utility-first, fast development |
| **Backend** | PHP 8.x | Easy XAMPP deployment, MySQL integration |
| **Database** | MySQL/MariaDB | Relational data, ACID compliance |
| **AJAX** | Fetch API | Modern, promise-based |
| **AI** | Google Gemini API | Advanced NLP, free tier |
| **Security** | bcrypt, MySQLi prepared statements | Industry best practices |

---

## 💡 Most Common Questions & Answers

### Q1: How does authentication work?
**A**: Session-based. Login → verify password with bcrypt → create session with user_id → requireLogin() checks session on protected pages. Password stored as bcrypt hash (never plain text).

### Q2: How do you prevent SQL injection?
**A**: Prepared statements with MySQLi. Parameters bound separately using bind_param(). Example:
```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
```

### Q3: Explain the AJAX workflow
**A**: JavaScript → Fetch API → POST to PHP endpoint → Backend processes → Returns JSON → JavaScript updates UI. Example:
```javascript
fetch('api/save.php', {
    method: 'POST',
    body: JSON.stringify(data)
})
.then(r => r.json())
.then(data => updateUI(data));
```

### Q4: How does Gemini API work?
**A**: Send HTTP POST with prompt → Gemini processes with AI → Returns text in JSON → Parse response → Extract structured data. Uses cURL in PHP, temperature=0.4 for balanced creativity.

### Q5: How does real-time preview work?
**A**: Template loads in iframe → JavaScript captures input events → Updates resumeState object → Accesses iframe document → Finds elements by data-field attribute → Updates textContent → Visual change appears instantly.

### Q6: How is PDF generated?
**A**: window.print() on iframe → Browser applies @media print styles → User selects "Save as PDF" → Browser generates PDF. @page rule sets A4 size, page-break controls keep sections together.

### Q7: What is bcrypt?
**A**: Password hashing algorithm using Blowfish cipher, includes automatic salt, cost factor 10, one-way (can't decrypt), industry standard for password security.

### Q8: What is REST API?
**A**: REpresentational State Transfer - uses HTTP methods (GET, POST, DELETE) with resource-based URLs, stateless communication, JSON format.

---

## 🗂️ File Locations - Quick Map

```
AUTHENTICATION:
- config/session.php → isLoggedIn(), requireLogin()
- login.php → Login page
- api/register.php → Registration API

DATABASE:
- config/database.php → getDBConnection()
- create_database.sql → Schema

AI FEATURES:
- config/gemini.php → callGeminiAPI()
- api/ai-conversation.php → Chat endpoint
- js/ai-editor.js → Frontend logic

EDITOR:
- editor-modern.php → Editor page
- js/editor.js → Editor logic
- templates/modern.html → Template

APIs:
- api/save-resume.php → Save
- api/load-resume.php → Load
- api/analyze-ats-score.php → ATS check
```

---

## 🔐 Security Measures (Explain All 5)

1. **Password Hashing**: bcrypt with cost 10, auto-salt, one-way
2. **SQL Injection Prevention**: Prepared statements, parameter binding
3. **XSS Prevention**: htmlspecialchars() on output, JSON encoding
4. **Authentication**: Session-based, requireLogin() on protected pages
5. **Input Validation**: Required fields, data types, format checking

---

## 🎨 Key Functions to Explain

### Backend (PHP)

**getDBConnection()**
```php
// Returns singleton MySQL connection
// Sets UTF-8 charset
// Handles errors gracefully
```

**requireLogin()**
```php
// Checks $_SESSION['user_id']
// Redirects to login if not set
// Protects all sensitive pages
```

**callGeminiAPI($prompt, $systemInstruction)**
```php
// Builds request with prompt
// Sends via cURL to Gemini
// Parses JSON response
// Returns {success, text}
```

### Frontend (JavaScript)

**updatePreview()**
```javascript
// Access iframe document
// Find elements by data-field
// Update textContent from state
// Show/hide sections based on data
```

**saveResume()**
```javascript
// Collect form data
// POST to api/save-resume.php
// Update resume_id if new
// Show notification
```

**applyUpdates(updates)**
```javascript
// Parse AI response updates
// Merge into resumeState
// Trigger preview update
```

---

## 📊 Data Flow - Essential Workflows

### User Registration
```
Form → Validate → Hash password → INSERT → Get user_id →
Create session → Redirect to dashboard
```

### Resume Creation
```
Form → Capture data → POST to save API → Validate →
INSERT/UPDATE → Return ID → Update UI
```

### AI Conversation
```
User message → Build prompt → Call Gemini → Parse JSON →
Extract updates → Apply to state → Update preview
```

### ATS Analysis
```
Upload PDF → Extract text via Gemini → Send for analysis →
Receive scores → Save to DB → Display results
```

---

## 🎓 Technical Terms to Define

**AJAX**: Asynchronous JavaScript And XML - send/receive data without page reload

**JSON**: JavaScript Object Notation - lightweight data format

**API**: Application Programming Interface - endpoints for communication

**REST**: REpresentational State Transfer - API architectural style

**CRUD**: Create, Read, Update, Delete - four basic operations

**ORM**: Object-Relational Mapping (we don't use one, use raw SQL)

**Session**: Server-side storage of user state across requests

**Cookie**: Client-side storage (we use for Remember Me)

**iframe**: Inline frame element - separate HTML document within page

**Prepared Statement**: Parameterized SQL query to prevent injection

**bcrypt**: Password hashing algorithm using Blowfish

**Salt**: Random data added to password before hashing

**Hash**: One-way cryptographic function (can't reverse)

---

## 📈 Database Schema - Quick Reference

**users**
- user_id (PK), email (UNIQUE), password_hash, full_name
- Purpose: User accounts and profiles

**resumes**
- resume_id (PK), user_id (FK), template_name, personal_details (JSON), experience (JSON), education (JSON)
- Purpose: Resume data storage

**ats_scores**
- score_id (PK), resume_id (FK), overall_score, keywords_found (JSON), improvements (JSON)
- Purpose: ATS analysis results

**job_applications**
- application_id (PK), user_id (FK), company_name, job_title, status
- Purpose: Job tracking

**sessions**
- session_id (PK), user_id (FK), expires_at
- Purpose: Session management

---

## 🚀 Demo Workflow (If Asked to Show)

1. **Registration**: Show register page → Create account → Auto-login
2. **Dashboard**: Show resumes list → Create new resume button
3. **Template Selection**: Choose Modern template
4. **Editor**: Fill personal details → Show real-time preview
5. **AI Editor**: Switch to AI → Type "I'm a software engineer at Google" → Show auto-fill
6. **Save**: Click save → Show success notification
7. **ATS Checker**: Navigate to score checker → Upload resume → Show analysis
8. **PDF Download**: Click download → Show print dialog → Save as PDF

---

## 💬 If You Get Stuck

**Strategy 1 - Buy Time**:
"That's a great question. Let me think about the implementation... [pause] ..."

**Strategy 2 - Explain What You Know**:
"While I may not recall the exact line of code, the concept is..."

**Strategy 3 - Show Related Knowledge**:
"That's related to [similar concept], which works by..."

**Strategy 4 - Be Honest**:
"I'm not entirely certain about that specific detail, but I know the general approach is..."

**Strategy 5 - Offer to Demonstrate**:
"Let me show you in the code / Can I demonstrate how it works?"

---

## 🎯 Confidence Boosters

✅ You built a full-stack application
✅ You integrated external AI API
✅ You implemented security measures
✅ You created real-time features
✅ You have comprehensive documentation
✅ You can explain your design choices
✅ You tested all features

**Remember**: You know this project better than anyone else!

---

## 📝 Quick Mental Checklist Before Viva

- [ ] I can explain the three-tier architecture
- [ ] I understand how authentication works
- [ ] I can describe the Gemini API integration
- [ ] I know how real-time preview works
- [ ] I can explain AJAX and JSON
- [ ] I understand prepared statements
- [ ] I can describe bcrypt password hashing
- [ ] I know how templates and PDF work
- [ ] I can walk through a complete feature workflow
- [ ] I'm ready to demo the application

---

## 🎬 Opening Statement (Memorize This)

"Good morning/afternoon. I'm presenting ResumeSync, a full-stack web application for creating ATS-optimized resumes. The application uses a three-tier architecture with HTML/CSS/JavaScript on the frontend, PHP for business logic, and MySQL for data persistence. Key features include AI-powered resume generation using Google Gemini API, real-time preview with iframe-based templates, ATS score analysis, and job application tracking. Security is implemented through bcrypt password hashing, prepared statements for SQL injection prevention, and session-based authentication. The system demonstrates complete CRUD operations, RESTful API design, and modern AJAX communication patterns."

---

## 🎤 Closing Statement

"Thank you for your time. I'm confident in the technical implementation and ready to answer any questions about the architecture, features, or code. Would you like me to demonstrate any specific functionality?"

---

**Good luck! You've got this! 🚀**
