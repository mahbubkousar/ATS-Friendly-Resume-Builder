# ResumeSync - Complete Documentation

## 📚 Documentation Index

Welcome to the comprehensive documentation for ResumeSync. This guide will help you understand every aspect of the project for your viva defense.

---

## 📖 Documentation Files

### 1. [Project Overview](01-PROJECT-OVERVIEW.md)
**Read First!** - High-level understanding of the entire project
- Project description and purpose
- Complete feature list
- Technology stack breakdown
- Project structure
- System architecture diagrams
- Database schema overview
- Security features
- Deployment environment

**Key Topics for Viva**:
- Three-tier architecture
- Technology choices (Why PHP? Why Gemini?)
- Database design
- Security implementation

---

### 2. [Authentication System](02-AUTHENTICATION-SYSTEM.md)
**Core Feature** - How users register, login, and session management works
- Database configuration (singleton pattern)
- Session management functions
- Registration flow (step-by-step)
- Login flow (password verification)
- Protected routes implementation
- Security best practices (bcrypt, prepared statements)

**Key Code Files**:
- `config/database.php` - Database connection
- `config/session.php` - Session helpers
- `login.php` - Login page
- `register.php` - Registration page
- `api/register.php` - Registration API

**Key Topics for Viva**:
- How password hashing works (bcrypt)
- SQL injection prevention
- Session-based authentication
- Cookie handling (Remember Me)

---

### 3. [Gemini API Integration](03-GEMINI-API-INTEGRATION.md)
**Advanced Feature** - AI-powered resume generation and analysis
- API configuration (.env file)
- How Gemini API works (request/response)
- cURL implementation
- AI conversation system
- Response parsing (regex, JSON extraction)
- ATS score analysis
- Error handling

**Key Code Files**:
- `config/gemini.php` - API configuration and call function
- `api/ai-conversation.php` - Conversational AI endpoint
- `api/analyze-ats-score.php` - ATS analysis
- `js/ai-editor.js` - Frontend AI logic

**Key Topics for Viva**:
- REST API architecture
- Natural language processing
- Prompt engineering
- Data extraction from AI responses
- Generation config parameters (temperature, topK, etc.)

---

### 4. [Editor System & Dynamic Updates](04-EDITOR-SYSTEM.md)
**Core Feature** - Real-time preview and data synchronization
- Manual vs AI editor
- Iframe-based preview system
- Data binding with data-field attributes
- Real-time update mechanism
- Resume state management
- Save/load mechanism (auto-save)
- Data flow diagrams

**Key Code Files**:
- `editor-modern.php` - Modern template editor
- `js/editor.js` - Editor logic
- `js/ai-editor.js` - AI editor logic
- `api/save-resume.php` - Save API
- `api/load-resume.php` - Load API

**Key Topics for Viva**:
- Why use iframe?
- How data-field attributes work
- State synchronization
- Auto-save vs manual save
- Array handling (experience, education)

---

### 5. [API Endpoints & AJAX](05-API-ENDPOINTS.md)
**Technical Implementation** - Complete API reference and AJAX patterns
- RESTful API design
- All 13+ API endpoints documented
- Fetch API vs jQuery AJAX vs XMLHttpRequest
- Creating new APIs (step-by-step guide)
- Error handling patterns
- Security measures

**Key Code Files**:
- `api/` directory - All API endpoints
- `js/dashboard.js` - AJAX examples
- `js/score-checker.js` - File upload AJAX

**Key Topics for Viva**:
- What is AJAX?
- GET vs POST methods
- JSON request/response format
- Error handling (try-catch)
- Authentication in APIs (requireLogin)

---

### 6. [Template System & PDF Generation](06-TEMPLATE-SYSTEM.md)
**Visual Component** - Resume templates and print functionality
- Template architecture (embedded CSS)
- Three template types (Modern, Professional, Academic)
- Data binding system
- PDF generation (window.print())
- Print styles (@media print, @page)
- Page break control
- Creating new templates

**Key Code Files**:
- `templates/modern.html` - Modern template
- `templates/professional.html` - Professional template
- `templates/academic-standard.html` - Academic template

**Key Topics for Viva**:
- Why standalone HTML?
- @page rule
- @media print
- page-break-inside property
- Print optimization
- Data injection via JavaScript

---

### 7. [Adding New Features](07-ADDING-NEW-FEATURES.md)
**Developer Guide** - How to extend the application
- Development workflow
- Three complete examples:
  1. Add Resume Tags (full CRUD)
  2. Email Notifications (PHPMailer)
  3. Resume Analytics (tracking)
- Testing guidelines
- Common patterns (CRUD, modals, loading states)

**Key Topics for Viva**:
- Feature development process
- Database migrations
- API creation
- Frontend-backend integration
- Testing strategies

---

## 🎯 Quick Reference for Viva

### Most Important Concepts

#### 1. Architecture
- **Three-tier**: Presentation (HTML/CSS/JS) → Application (PHP) → Data (MySQL)
- **MVC-like**: Separation of concerns
- **REST API**: Stateless, resource-based endpoints

#### 2. Technologies

| Layer | Technology | Purpose |
|-------|-----------|---------|
| Frontend | HTML5, CSS3, JavaScript | User interface |
| Styling | Tailwind CSS, Custom CSS | Design |
| Backend | PHP 8.x | Server logic |
| Database | MySQL/MariaDB | Data persistence |
| Communication | AJAX (Fetch API) | Async requests |
| AI | Google Gemini API | AI features |

#### 3. Security
- ✅ Password hashing (bcrypt)
- ✅ Prepared statements (SQL injection prevention)
- ✅ Session-based auth
- ✅ XSS protection (htmlspecialchars)
- ✅ Input validation

#### 4. Key Workflows

**User Registration**:
```
Form → api/register.php → Validate → Hash password →
Insert DB → Create session → Redirect to dashboard
```

**Resume Creation**:
```
User fills form → JavaScript captures data →
AJAX to api/save-resume.php → Insert/Update DB →
Return ID → Update UI
```

**AI Resume Building**:
```
User message → api/ai-conversation.php →
Build prompt + system instruction → Call Gemini API →
Parse JSON response → Extract updates →
Apply to resumeState → Update preview
```

**PDF Generation**:
```
User clicks Download → JavaScript calls window.print() →
Browser applies @media print styles →
User saves as PDF
```

#### 5. Code Locations

**Authentication**: `config/session.php`, `login.php`
**Database**: `config/database.php`
**AI**: `config/gemini.php`, `api/ai-conversation.php`
**Editor**: `js/editor.js`, `editor-*.php`
**Templates**: `templates/*.html`
**APIs**: `api/*.php`
**Styles**: `css/*.css`

---

## 🔍 Common Viva Questions

### General Questions

**Q: What is this project?**
A: ResumeSync is a web application for creating ATS-optimized resumes using AI (Google Gemini), with features for resume editing, ATS score checking, and job application tracking.

**Q: What technologies did you use?**
A: Frontend: HTML, CSS (Tailwind), JavaScript. Backend: PHP, MySQL. APIs: Google Gemini for AI. Communication: AJAX (Fetch API).

**Q: Why did you choose PHP?**
A: Easy deployment with XAMPP, strong MySQL integration, mature ecosystem, suitable for web applications course requirements.

**Q: What is the architecture?**
A: Three-tier architecture - Presentation (client-side), Application (PHP server-side), Data (MySQL database).

---

### Technical Questions

**Q: How does authentication work?**
A: Session-based. User logs in → password verified with bcrypt → session created with user_id → requireLogin() checks session on protected pages.

**Q: How do you prevent SQL injection?**
A: Prepared statements with parameter binding via MySQLi. Never concatenate user input into SQL queries.

**Q: How does AJAX work?**
A: Asynchronous JavaScript sends HTTP requests to server (API endpoints) without page reload, receives JSON responses, updates UI dynamically.

**Q: Explain the Gemini API integration?**
A: Send POST request with prompt to Gemini endpoint, receive AI-generated text in JSON format, parse and extract structured data, apply to resume.

**Q: How does real-time preview work?**
A: Template loads in iframe, JavaScript accesses iframe document, finds elements by data-field attributes, updates textContent based on form inputs.

**Q: How is PDF generated?**
A: Browser's window.print() function, uses @media print CSS styles, user saves as PDF from print dialog.

**Q: What is a prepared statement?**
A: SQL query with placeholders (?), values bound separately, prevents SQL injection by treating user input as data, not code.

**Q: What is bcrypt?**
A: Password hashing algorithm using Blowfish cipher, includes automatic salt, configurable cost factor, one-way (can't decrypt).

**Q: What is REST API?**
A: Architectural style using HTTP methods (GET, POST, DELETE) with resource-based URLs, stateless communication, JSON format.

**Q: What is JSON?**
A: JavaScript Object Notation, lightweight data format, human-readable, used for API communication.

---

### Feature-Specific Questions

**Q: How does AI editor work?**
A: User types message → sent to Gemini API with system instruction → AI responds with conversational text + structured JSON → JSON parsed and applied to resume state → preview updates.

**Q: How does ATS checker work?**
A: User uploads resume + job description → extract text from PDF/DOCX → send to Gemini for analysis → AI returns scores, keywords, suggestions → display results.

**Q: How does template system work?**
A: Standalone HTML files with embedded CSS, loaded in iframe, JavaScript injects data using data-field attributes, printable with @media print styles.

**Q: How do you handle multiple resume templates?**
A: Each template is separate HTML file, user selects template, appropriate editor page loads, template-specific fields shown/hidden, same API for all templates.

---

## 📝 Study Tips

### For Your Viva

1. **Read in Order**: Follow the documentation files in sequence (01 → 07)

2. **Understand Flow**: Trace complete workflows (registration, resume creation, PDF generation)

3. **Know the Code**: Be able to explain key functions:
   - `getDBConnection()` - Database connection
   - `requireLogin()` - Authentication check
   - `callGeminiAPI()` - AI API call
   - `updatePreview()` - Template update
   - `saveResume()` - Data persistence

4. **Practice Explaining**: Use diagrams and flow charts to explain concepts

5. **Run the Code**: Test features yourself, see what happens in browser DevTools

6. **Common Mistakes to Avoid**:
   - Don't say "I don't know" - explain what you think it does
   - Don't memorize code - understand the logic
   - Don't skip error handling - it's important!
   - Don't ignore security - explain protections

---

## 🚀 Quick Start

### Setup for Demo

1. **Start XAMPP**:
   - Start Apache
   - Start MySQL

2. **Import Database**:
   ```bash
   mysql -u root -p < create_database.sql
   ```

3. **Configure Gemini API**:
   - Create `.env` file
   - Add `GEMINI_API_KEY=your_key_here`

4. **Access Application**:
   - Open `http://localhost/ATS/`
   - Register new account
   - Create resume

---

## 📊 Project Statistics

- **Total Files**: 50+ PHP, JS, CSS files
- **API Endpoints**: 13+ RESTful endpoints
- **Database Tables**: 11 main tables
- **Templates**: 3 professional templates
- **Lines of Code**: ~5000+ lines (estimated)
- **Features**: 6 major features
- **Technologies**: 10+ technologies

---

## 🎓 Learning Outcomes

By completing this project, you've learned:

1. ✅ Full-stack web development (Frontend + Backend + Database)
2. ✅ RESTful API design and implementation
3. ✅ AJAX for asynchronous communication
4. ✅ Session-based authentication
5. ✅ SQL database design and queries
6. ✅ Security best practices
7. ✅ Third-party API integration (Gemini)
8. ✅ Real-time UI updates
9. ✅ PDF generation with print styles
10. ✅ Version control with Git

---

## 📬 Support

For questions or issues:
- Review documentation files
- Check code comments
- Test in browser DevTools
- Review error logs (XAMPP)

---

## ✅ Final Checklist

Before your viva:

- [ ] Read all 7 documentation files
- [ ] Test all features
- [ ] Can explain authentication flow
- [ ] Can explain Gemini API integration
- [ ] Can explain AJAX workflow
- [ ] Can explain template system
- [ ] Know security measures
- [ ] Understand database schema
- [ ] Can trace code execution
- [ ] Prepared to demo live

---

**Good luck with your viva! You've got this! 🎉**

---

## 📄 Document Version

- **Version**: 1.0
- **Last Updated**: December 2025
- **Author**: Generated for Web Technologies Course
- **Project**: ResumeSync ATS System
