# ResumeSync - Architecture Diagrams

Visual representations of system architecture and workflows for better understanding.

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    CLIENT BROWSER                           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  PRESENTATION LAYER                                  │   │
│  │  - HTML5 (Structure)                                 │   │
│  │  - CSS3 + Tailwind (Styling)                         │   │
│  │  - JavaScript ES6+ (Logic)                           │   │
│  │  - Font Awesome (Icons)                              │   │
│  └──────────────────────────────────────────────────────┘   │
│              │                           ▲                   │
│              │ HTTP/AJAX Requests        │ JSON Responses    │
│              ▼                           │                   │
└──────────────┼───────────────────────────┼───────────────────┘
               │                           │
┌──────────────┴───────────────────────────┴───────────────────┐
│                    XAMPP SERVER                              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  APPLICATION LAYER (PHP)                             │   │
│  │  ┌────────────────┐  ┌────────────────────────────┐ │   │
│  │  │ Core Config    │  │ API Endpoints              │ │   │
│  │  │ - database.php │  │ - save-resume.php          │ │   │
│  │  │ - session.php  │  │ - ai-conversation.php      │ │   │
│  │  │ - gemini.php   │  │ - analyze-ats-score.php    │ │   │
│  │  └────────────────┘  │ - add-application.php      │ │   │
│  │                      │ - get-user-data.php        │ │   │
│  │  ┌────────────────┐  └────────────────────────────┘ │   │
│  │  │ Page Files     │                                 │   │
│  │  │ - login.php    │  ┌───────────────────────────┐ │   │
│  │  │ - dashboard.php│  │ Business Logic            │ │   │
│  │  │ - editor-*.php │  │ - Authentication          │ │   │
│  │  │ - ai-editor.php│  │ - Validation              │ │   │
│  │  └────────────────┘  │ - Data Processing         │ │   │
│  │                      └───────────────────────────┘ │   │
│  └──────────────────────────────────────────────────────┘   │
│              │                           ▲                   │
│              │ MySQLi Queries            │ Result Sets       │
│              ▼                           │                   │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  DATA LAYER (MySQL/MariaDB)                          │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────────────┐ │   │
│  │  │  users   │ │ resumes  │ │ job_applications     │ │   │
│  │  └──────────┘ └──────────┘ └──────────────────────┘ │   │
│  │  ┌──────────┐ ┌──────────┐ ┌──────────────────────┐ │   │
│  │  │ sessions │ │ ats_scores│ │ activity_logs        │ │   │
│  │  └──────────┘ └──────────┘ └──────────────────────┘ │   │
│  └──────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────┐
│              EXTERNAL SERVICES                               │
│  ┌────────────────────────────────────────────────────┐     │
│  │  Google Gemini API                                 │     │
│  │  - AI Text Generation                              │     │
│  │  - Resume Analysis                                 │     │
│  │  - ATS Score Calculation                           │     │
│  │  Endpoint: generativelanguage.googleapis.com      │     │
│  └────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

---

## User Registration Flow

```
USER                 BROWSER              SERVER (PHP)          DATABASE
 |                      |                       |                    |
 | 1. Fill form         |                       |                    |
 |--------------------->|                       |                    |
 |                      |                       |                    |
 | 2. Click Register    |                       |                    |
 |--------------------->|                       |                    |
 |                      | 3. POST /api/register.php                  |
 |                      |---------------------->|                    |
 |                      |                       | 4. Validate input  |
 |                      |                       |-----------------   |
 |                      |                       |                 |  |
 |                      |                       | 5. Check email     |
 |                      |                       | exists?            |
 |                      |                       |------------------->|
 |                      |                       |                    |
 |                      |                       | 6. Email check     |
 |                      |                       |    result          |
 |                      |                       |<-------------------|
 |                      |                       |                    |
 |                      |                       | 7. Hash password   |
 |                      |                       |    (bcrypt)        |
 |                      |                       |-----------------   |
 |                      |                       |                 |  |
 |                      |                       | 8. INSERT INTO     |
 |                      |                       |    users           |
 |                      |                       |------------------->|
 |                      |                       |                    |
 |                      |                       | 9. Return user_id  |
 |                      |                       |<-------------------|
 |                      |                       |                    |
 |                      |                       | 10. Create session |
 |                      |                       |    $_SESSION[...]  |
 |                      |                       |-----------------   |
 |                      |                       |                 |  |
 |                      | 11. JSON response     |                    |
 |                      |    {success: true}    |                    |
 |                      |<----------------------|                    |
 | 12. Redirect to      |                       |                    |
 |     dashboard        |                       |                    |
 |<---------------------|                       |                    |
```

---

## Login Flow

```
USER                 BROWSER              SERVER (PHP)          DATABASE
 |                      |                       |                    |
 | 1. Enter email       |                       |                    |
 |    & password        |                       |                    |
 |--------------------->|                       |                    |
 |                      |                       |                    |
 | 2. Click Login       |                       |                    |
 |--------------------->|                       |                    |
 |                      | 3. POST /login.php    |                    |
 |                      |---------------------->|                    |
 |                      |                       | 4. Validate input  |
 |                      |                       |-----------------   |
 |                      |                       |                 |  |
 |                      |                       | 5. SELECT FROM     |
 |                      |                       |    users WHERE     |
 |                      |                       |    email = ?       |
 |                      |                       |------------------->|
 |                      |                       |                    |
 |                      |                       | 6. Return user row |
 |                      |                       |    (with hash)     |
 |                      |                       |<-------------------|
 |                      |                       |                    |
 |                      |                       | 7. Verify password |
 |                      |                       |    password_verify()|
 |                      |                       |-----------------   |
 |                      |                       |                 |  |
 |                      |                       | 8. Match? Create   |
 |                      |                       |    session         |
 |                      |                       |-----------------   |
 |                      |                       |                 |  |
 |                      | 9. Redirect           |                    |
 |                      |    Location: dashboard|                    |
 |                      |<----------------------|                    |
 | 10. Dashboard loaded |                       |                    |
 |<---------------------|                       |                    |
```

---

## AI Conversation Flow

```
USER              BROWSER (JS)       SERVER (PHP)       GEMINI API      DATABASE
 |                    |                    |                  |              |
 | 1. Type message    |                    |                  |              |
 |    "I'm a SE at    |                    |                  |              |
 |     Google"        |                    |                  |              |
 |------------------->|                    |                  |              |
 |                    | 2. POST /api/      |                  |              |
 |                    |    ai-conversation |                  |              |
 |                    |    {message,       |                  |              |
 |                    |     resumeState}   |                  |              |
 |                    |------------------->|                  |              |
 |                    |                    | 3. Build prompt  |              |
 |                    |                    |    + system      |              |
 |                    |                    |    instruction   |              |
 |                    |                    |--------------    |              |
 |                    |                    |              |   |              |
 |                    |                    | 4. POST to       |              |
 |                    |                    |    Gemini API    |              |
 |                    |                    |----------------->|              |
 |                    |                    |                  | 5. Process   |
 |                    |                    |                  |    with AI   |
 |                    |                    |                  |------------  |
 |                    |                    |                  |            | |
 |                    |                    | 6. JSON response |              |
 |                    |                    |    {text: "..."}  |              |
 |                    |                    |<-----------------|              |
 |                    |                    |                  |              |
 |                    |                    | 7. Parse response|              |
 |                    |                    |    Extract JSON  |              |
 |                    |                    |    from text     |              |
 |                    |                    |--------------    |              |
 |                    |                    |              |   |              |
 |                    | 8. JSON response   |                  |              |
 |                    |    {response,      |                  |              |
 |                    |     updates}       |                  |              |
 |                    |<-------------------|                  |              |
 | 9. Display AI      |                    |                  |              |
 |    message in chat |                    |                  |              |
 |<-------------------|                    |                  |              |
 |                    | 10. Apply updates  |                  |              |
 |                    |     to resumeState |                  |              |
 |                    |--------------      |                  |              |
 |                    |              |     |                  |              |
 |                    | 11. Update preview|                  |              |
 |                    |     (iframe)       |                  |              |
 |                    |--------------      |                  |              |
 |                    |              |     |                  |              |
 | 12. See updated    |                    |                  |              |
 |     resume         |                    |                  |              |
 |<-------------------|                    |                  |              |
```

---

## Resume Save Flow

```
USER              BROWSER (JS)       SERVER (PHP)          DATABASE
 |                    |                    |                    |
 | 1. Click Save      |                    |                    |
 |------------------->|                    |                    |
 |                    | 2. Collect form    |                    |
 |                    |    data into       |                    |
 |                    |    resumeState     |                    |
 |                    |--------------      |                    |
 |                    |              |     |                    |
 |                    | 3. POST /api/      |                    |
 |                    |    save-resume.php |                    |
 |                    |    {resumeState}   |                    |
 |                    |------------------->|                    |
 |                    |                    | 4. Authenticate    |
 |                    |                    |    requireLogin()  |
 |                    |                    |--------------      |
 |                    |                    |              |     |
 |                    |                    | 5. Validate input  |
 |                    |                    |--------------      |
 |                    |                    |              |     |
 |                    |                    | 6. Check if new    |
 |                    |                    |    or update?      |
 |                    |                    |--------------      |
 |                    |                    |              |     |
 |                    |                    | 7. INSERT or       |
 |                    |                    |    UPDATE resumes  |
 |                    |                    |------------------->|
 |                    |                    |                    |
 |                    |                    | 8. Return ID       |
 |                    |                    |<-------------------|
 |                    | 9. JSON response   |                    |
 |                    |    {success,       |                    |
 |                    |     resume_id}     |                    |
 |                    |<-------------------|                    |
 | 10. Show success   |                    |                    |
 |     notification   |                    |                    |
 |<-------------------|                    |                    |
```

---

## Real-time Preview Update Flow

```
USER              BROWSER (JS)             IFRAME (Template)
 |                    |                            |
 | 1. Type in form    |                            |
 |    field "John Doe"|                            |
 |------------------->|                            |
 |                    | 2. oninput event          |
 |                    |    captured               |
 |                    |-----------                |
 |                    |           |               |
 |                    | 3. Update resumeState     |
 |                    |    .personal_details      |
 |                    |    .fullName = "John Doe" |
 |                    |-----------                |
 |                    |           |               |
 |                    | 4. Call updatePreview()   |
 |                    |-----------                |
 |                    |           |               |
 |                    | 5. Access iframe document |
 |                    |    iframeDoc = iframe     |
 |                    |    .contentDocument       |
 |                    |-------------------------->|
 |                    |                           |
 |                    | 6. Query selector         |
 |                    |    element = iframeDoc    |
 |                    |    .querySelector(        |
 |                    |    '[data-field="name"]') |
 |                    |-------------------------->|
 |                    |                           | 7. Find element
 |                    |                           |    <div data-field
 |                    |                           |     ="name">
 |                    |                           |----------
 |                    |                           |          |
 |                    | 8. Return element         |
 |                    |<--------------------------|
 |                    |                           |
 |                    | 9. Update textContent     |
 |                    |    element.textContent    |
 |                    |    = "John Doe"           |
 |                    |-------------------------->|
 |                    |                           | 10. DOM updated
 |                    |                           |     <div>John Doe
 |                    |                           |     </div>
 |                    |                           |----------
 |                    |                           |          |
 | 11. See name       |                           |
 |     updated in     |                           |
 |     preview        |                           |
 |<-------------------|                           |
```

---

## ATS Score Analysis Flow

```
USER              BROWSER           SERVER (PHP)      GEMINI API       DATABASE
 |                   |                   |                 |               |
 | 1. Upload resume  |                   |                 |               |
 |    (PDF) + job    |                   |                 |               |
 |    description    |                   |                 |               |
 |------------------>|                   |                 |               |
 |                   | 2. POST /api/     |                 |               |
 |                   |    analyze-ats    |                 |               |
 |                   |    -score.php     |                 |               |
 |                   |    FormData       |                 |               |
 |                   |------------------>|                 |               |
 |                   |                   | 3. Extract text |               |
 |                   |                   |    from PDF     |               |
 |                   |                   |    via Gemini   |               |
 |                   |                   |    (file API)   |               |
 |                   |                   |---------------->|               |
 |                   |                   |                 | 4. OCR/Parse  |
 |                   |                   |                 |    PDF        |
 |                   |                   |                 |----------     |
 |                   |                   |                 |          |    |
 |                   |                   | 5. Text extracted              |
 |                   |                   |<----------------|               |
 |                   |                   |                 |               |
 |                   |                   | 6. Build analysis              |
 |                   |                   |    prompt       |               |
 |                   |                   |    (resume +    |               |
 |                   |                   |     job desc)   |               |
 |                   |                   |----------       |               |
 |                   |                   |          |      |               |
 |                   |                   | 7. POST to      |               |
 |                   |                   |    Gemini API   |               |
 |                   |                   |---------------->|               |
 |                   |                   |                 | 8. AI analyzes|
 |                   |                   |                 |    compatibility|
 |                   |                   |                 |    scores     |
 |                   |                   |                 |    keywords   |
 |                   |                   |                 |----------     |
 |                   |                   |                 |          |    |
 |                   |                   | 9. JSON response|               |
 |                   |                   |    {scores,     |               |
 |                   |                   |     suggestions}|               |
 |                   |                   |<----------------|               |
 |                   |                   |                 |               |
 |                   |                   | 10. Parse JSON  |               |
 |                   |                   |     extract data|               |
 |                   |                   |----------       |               |
 |                   |                   |          |      |               |
 |                   |                   | 11. INSERT INTO |               |
 |                   |                   |     ats_scores  |               |
 |                   |                   |---------------->|               |
 |                   |                   |                 |               |
 |                   | 12. JSON response |                 |               |
 |                   |     {success,     |                 |               |
 |                   |      analysis}    |                 |               |
 |                   |<------------------|                 |               |
 | 13. Display scores|                   |                 |               |
 |     & suggestions |                   |                 |               |
 |<------------------|                   |                 |               |
```

---

## PDF Generation Flow

```
USER              BROWSER (JS)        IFRAME (Template)       BROWSER PRINT
 |                   |                       |                       |
 | 1. Click Download |                       |                       |
 |    PDF button     |                       |                       |
 |------------------>|                       |                       |
 |                   | 2. Call downloadPDF() |                       |
 |                   |    function           |                       |
 |                   |----------             |                       |
 |                   |          |            |                       |
 |                   | 3. Get iframe window  |                       |
 |                   |    iframeWindow =     |                       |
 |                   |    iframe.contentWindow                       |
 |                   |---------------------->|                       |
 |                   |                       |                       |
 |                   | 4. Call print()       |                       |
 |                   |    iframeWindow.print()|                      |
 |                   |---------------------->|                       |
 |                   |                       | 5. Trigger print      |
 |                   |                       |    dialog             |
 |                   |                       |---------------------->|
 |                   |                       |                       | 6. Apply
 |                   |                       |                       |    @media print
 |                   |                       |                       |    styles
 |                   |                       |                       |--------
 |                   |                       |                       |        |
 |                   |                       |                       | 7. Render
 |                   |                       |                       |    for print
 |                   |                       |                       |--------
 |                   |                       |                       |        |
 | 8. Print dialog   |                       |                       |
 |    shown          |                       |                       |
 |<------------------|                       |                       |
 |                   |                       |                       |
 | 9. Select "Save   |                       |                       |
 |    as PDF"        |                       |                       |
 |------------------>|                       |                       |
 |                   |                       |                       | 10. Generate
 |                   |                       |                       |     PDF file
 |                   |                       |                       |--------
 |                   |                       |                       |        |
 | 11. Download PDF  |                       |                       |
 |<------------------|                       |                       |
```

---

## Database Entity Relationship

```
┌─────────────┐
│    users    │
│─────────────│
│ user_id (PK)│
│ email       │
│ password    │
│ full_name   │
└──────┬──────┘
       │ 1
       │
       │ N
┌──────┴───────┐      ┌──────────────┐
│   resumes    │      │ sessions     │
│──────────────│      │──────────────│
│ resume_id(PK)│      │ session_id   │
│ user_id (FK) │      │ user_id (FK) │
│ template_name│      │ expires_at   │
│ personal...  │      └──────────────┘
│ experience   │
│ education    │
└──────┬───────┘
       │ 1
       │
       │ N
┌──────┴────────┐     ┌─────────────────┐
│  ats_scores   │     │ job_applications│
│───────────────│     │─────────────────│
│ score_id (PK) │     │ application_id  │
│ resume_id(FK) │     │ user_id (FK)    │
│ overall_score │     │ company_name    │
│ keywords_found│     │ job_title       │
│ improvements  │     │ status          │
└───────────────┘     └────────┬────────┘
                               │ 1
                               │
                               │ N
                      ┌────────┴─────────┐
                      │ application_     │
                      │   timeline       │
                      │──────────────────│
                      │ timeline_id (PK) │
                      │ application_id   │
                      │ event_type       │
                      │ event_date       │
                      └──────────────────┘
```

---

## Security Layers

```
┌────────────────────────────────────────────┐
│           INPUT VALIDATION                 │
│  - Required fields check                   │
│  - Data type validation                    │
│  - Length limits                           │
│  - Format validation (email, date)         │
└──────────────┬─────────────────────────────┘
               │
               ▼
┌────────────────────────────────────────────┐
│      AUTHENTICATION & AUTHORIZATION        │
│  - Session-based auth                      │
│  - requireLogin() on protected pages       │
│  - User ID verification in queries         │
│  - No direct user input in file paths      │
└──────────────┬─────────────────────────────┘
               │
               ▼
┌────────────────────────────────────────────┐
│        SQL INJECTION PREVENTION            │
│  - Prepared statements (100%)              │
│  - Parameter binding                       │
│  - No string concatenation in queries      │
│  - Type casting for integers               │
└──────────────┬─────────────────────────────┘
               │
               ▼
┌────────────────────────────────────────────┐
│           XSS PREVENTION                   │
│  - htmlspecialchars() on output            │
│  - JSON encoding for data                  │
│  - Content-Type: application/json          │
│  - No eval() or innerHTML with user data   │
└──────────────┬─────────────────────────────┘
               │
               ▼
┌────────────────────────────────────────────┐
│         PASSWORD SECURITY                  │
│  - bcrypt hashing (cost factor 10)         │
│  - Automatic salt generation               │
│  - password_verify() for checking          │
│  - Never store plain text passwords        │
└──────────────┬─────────────────────────────┘
               │
               ▼
┌────────────────────────────────────────────┐
│         SESSION SECURITY                   │
│  - HTTP only cookies (default)             │
│  - Session timeout handling                │
│  - Unique session IDs                      │
│  - session_regenerate_id() on login        │
└────────────────────────────────────────────┘
```

---

## Component Communication

```
┌─────────────────────────────────────────────────────────┐
│                    FRONTEND LAYER                       │
│  ┌──────────────┐    ┌──────────────┐   ┌───────────┐  │
│  │  UI Components│    │   JavaScript │   │   AJAX    │  │
│  │  - Forms      │◄──►│   - editor.js│◄─►│  - Fetch  │  │
│  │  - Buttons    │    │   - dashboard│   │  - jQuery │  │
│  │  - Modals     │    │   - ai-editor│   │  - XHR    │  │
│  └──────────────┘    └──────────────┘   └─────┬─────┘  │
└────────────────────────────────────────────────┼────────┘
                                                 │
                                       JSON over HTTP/HTTPS
                                                 │
┌────────────────────────────────────────────────┼────────┐
│                   BACKEND LAYER                │        │
│  ┌────────────────────────────────────────┐    │        │
│  │            API ENDPOINTS               │    │        │
│  │  ┌──────────────────┐  ┌──────────┐   │◄───┘        │
│  │  │ Public APIs      │  │ Auth APIs│   │             │
│  │  │ - register.php   │  │ - save   │   │             │
│  │  │ - analyze-ats    │  │ - load   │   │             │
│  │  └──────────────────┘  │ - delete │   │             │
│  │                        └──────────┘   │             │
│  └───────────┬────────────────────────────┘             │
│              │                                          │
│  ┌───────────▼────────────────────────────┐            │
│  │      BUSINESS LOGIC LAYER              │            │
│  │  - Authentication (session.php)        │            │
│  │  - Validation                          │            │
│  │  - Data processing                     │            │
│  │  - External API calls (Gemini)         │            │
│  └───────────┬────────────────────────────┘            │
│              │                                          │
│  ┌───────────▼────────────────────────────┐            │
│  │      DATABASE ACCESS LAYER             │            │
│  │  - Connection (database.php)           │            │
│  │  - Prepared statements                 │            │
│  │  - Query execution                     │            │
│  │  - Result processing                   │            │
│  └───────────┬────────────────────────────┘            │
└──────────────┼─────────────────────────────────────────┘
               │
               │ MySQLi Protocol
               │
┌──────────────▼─────────────────────────────────────────┐
│                   DATABASE LAYER                       │
│  ┌────────────────────────────────────────────────┐   │
│  │               MySQL Server                     │   │
│  │  ┌──────────┐  ┌──────────┐  ┌─────────────┐  │   │
│  │  │ Tables   │  │ Indexes  │  │ Constraints │  │   │
│  │  │ - users  │  │ - PK     │  │ - FK        │  │   │
│  │  │ - resumes│  │ - FK     │  │ - UNIQUE    │  │   │
│  │  └──────────┘  └──────────┘  └─────────────┘  │   │
│  └────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────┘
```

---

These diagrams should help visualize the system architecture and data flows for your viva presentation!
