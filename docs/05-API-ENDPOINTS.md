# API Endpoints & AJAX - Complete Guide

## Table of Contents
1. [API Architecture](#api-architecture)
2. [AJAX Implementation](#ajax-implementation)
3. [All API Endpoints](#all-api-endpoints)
4. [Creating New APIs](#creating-new-apis)
5. [Error Handling](#error-handling)
6. [Security](#security)

---

## API Architecture

### RESTful Design Pattern

All APIs follow REST principles:
- **URL**: Resource-based (`/api/save-resume.php`)
- **Method**: HTTP verbs (GET, POST, DELETE)
- **Format**: JSON request/response
- **Status**: HTTP status codes (200, 40x, 50x)
- **Authentication**: Session-based

### Standard Response Format

**Success Response**:
```json
{
  "success": true,
  "message": "Operation successful",
  "data": { /* result data */ }
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Error description",
  "error": "Technical error details"
}
```

---

## AJAX Implementation

### Three Methods Used

1. **Fetch API** (Modern, Promise-based)
2. **jQuery AJAX** (Legacy support)
3. **XMLHttpRequest** (Rare, legacy)

### Method 1: Fetch API (Recommended)

**Example**: Save Resume

```javascript
async function saveResume(resumeData) {
    try {
        const response = await fetch('api/save-resume.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(resumeData)
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            console.log('Saved:', data.resume_id);
            return data;
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Save error:', error);
        throw error;
    }
}
```

**Key Features**:
- ✅ Promise-based (async/await)
- ✅ Native JavaScript (no library)
- ✅ Clean error handling
- ✅ Modern browsers

### Method 2: jQuery AJAX

**Example**: Load User Data

```javascript
$.ajax({
    url: 'api/get-user-data.php',
    type: 'GET',
    dataType: 'json',
    data: {
        type: 'experience'
    },
    success: function(response) {
        if (response.success) {
            console.log('Data:', response.data);
            displayExperience(response.data);
        } else {
            console.error('Error:', response.message);
        }
    },
    error: function(xhr, status, error) {
        console.error('AJAX error:', error);
    }
});
```

**When to use**:
- Legacy code maintenance
- When jQuery already loaded
- Simpler syntax for beginners

### Method 3: XMLHttpRequest (Legacy)

**Example**: Check ATS Score

```javascript
const xhr = new XMLHttpRequest();
xhr.open('POST', 'api/analyze-ats-score.php', true);
xhr.setRequestHeader('Content-Type', 'application/json');

xhr.onload = function() {
    if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        console.log('Score:', response.data);
    } else {
        console.error('Error:', xhr.status);
    }
};

xhr.onerror = function() {
    console.error('Request failed');
};

xhr.send(JSON.stringify(requestData));
```

**When to use**:
- Very old browser support needed
- File upload with progress tracking
- Generally avoid for new code

---

## All API Endpoints

### Resume Management

#### 1. Save Resume
**Endpoint**: `api/save-resume.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "resume_id": 123,
  "resume_title": "Software Engineer Resume",
  "template_name": "modern",
  "personal_details": {
    "fullName": "John Doe",
    "email": "john@example.com"
  },
  "experience": "[{...}]",
  "education": "[{...}]",
  "skills": "Python, JavaScript"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Resume saved successfully",
  "resume_id": 123
}
```

**Code Reference**: See line 29-110 in `api/save-resume.php`

---

#### 2. Load Resume
**Endpoint**: `api/load-resume.php`
**Method**: GET
**Auth**: Required

**Request**:
```
GET api/load-resume.php?id=123
```

**Response**:
```json
{
  "success": true,
  "data": {
    "resume_id": 123,
    "resume_title": "My Resume",
    "template_name": "modern",
    "personal_details": {...},
    "experience": [...],
    "education": [...]
  }
}
```

---

#### 3. Delete Resume
**Endpoint**: `api/delete-resume.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "resume_id": 123
}
```

**Response**:
```json
{
  "success": true,
  "message": "Resume deleted successfully"
}
```

---

### AI Features

#### 4. AI Conversation
**Endpoint**: `api/ai-conversation.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "message": "I worked as a software engineer at Google",
  "resumeState": {...},
  "conversationHistory": [...],
  "templateName": "modern"
}
```

**Response**:
```json
{
  "success": true,
  "response": "Great! I've added your experience...",
  "updates": {
    "experience": [{
      "jobTitle": "Software Engineer",
      "company": "Google"
    }]
  }
}
```

**Code Reference**: See `api/ai-conversation.php` lines 44-81

---

#### 5. Analyze ATS Score
**Endpoint**: `api/analyze-ats-score.php`
**Method**: POST
**Auth**: Required

**Request** (Form Data):
```
resume_file: <file>
job_description: "Job requirements..."
```

**Response**:
```json
{
  "success": true,
  "data": {
    "overall_score": 85,
    "keywords_score": 90,
    "formatting_score": 80,
    "improvements": ["Add more keywords", "..."],
    "strengths": ["Good formatting", "..."],
    "keywords_found": ["Python", "JavaScript"],
    "keywords_missing": ["Docker", "Kubernetes"]
  }
}
```

---

### User Profile

#### 6. Get User Data
**Endpoint**: `api/get-user-data.php`
**Method**: GET
**Auth**: Required

**Request**:
```
GET api/get-user-data.php?type=experience
GET api/get-user-data.php?type=education
```

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "experience_id": 1,
      "job_title": "Software Engineer",
      "company_name": "Google",
      "start_date": "2020-01",
      "end_date": "2023-12"
    }
  ]
}
```

---

#### 7. Add Experience
**Endpoint**: `api/add-experience.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "job_title": "Senior Developer",
  "company_name": "Microsoft",
  "location": "Seattle, WA",
  "start_date": "2020-01",
  "end_date": "Present",
  "description": "Led team of 5 engineers..."
}
```

**Response**:
```json
{
  "success": true,
  "message": "Experience added",
  "experience_id": 42
}
```

---

#### 8. Add Education
**Endpoint**: `api/add-education.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "institution_name": "MIT",
  "degree": "B.S. Computer Science",
  "field_of_study": "Computer Science",
  "start_date": "2016",
  "end_date": "2020",
  "gpa": "3.8"
}
```

---

### Job Application Tracking

#### 9. Add Application
**Endpoint**: `api/add-application.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "company_name": "Google",
  "job_title": "Software Engineer",
  "job_location": "Mountain View, CA",
  "application_date": "2024-01-15",
  "status": "Applied",
  "job_description": "We are looking for...",
  "notes": "Applied through referral"
}
```

**Response**:
```json
{
  "success": true,
  "message": "Application added",
  "application_id": 15
}
```

---

#### 10. Update Application Status
**Endpoint**: `api/update-application-status.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "application_id": 15,
  "status": "Interview Scheduled",
  "notes": "Phone screen on Friday"
}
```

---

#### 11. Get Timeline
**Endpoint**: `api/get-timeline.php`
**Method**: GET
**Auth**: Required

**Request**:
```
GET api/get-timeline.php?application_id=15
```

**Response**:
```json
{
  "success": true,
  "timeline": [
    {
      "event_type": "application_submitted",
      "event_title": "Application Submitted",
      "event_date": "2024-01-15",
      "event_description": "Applied online"
    },
    {
      "event_type": "interview_scheduled",
      "event_title": "Interview Scheduled",
      "event_date": "2024-01-22",
      "event_description": "Phone screen"
    }
  ]
}
```

---

### Sharing & Export

#### 12. Generate Share Link
**Endpoint**: `api/generate-share-link.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "resume_id": 123
}
```

**Response**:
```json
{
  "success": true,
  "share_token": "abc123xyz789",
  "share_url": "http://localhost/ATS/view-resume.php?token=abc123xyz789"
}
```

---

#### 13. Toggle Resume Public
**Endpoint**: `api/toggle-resume-public.php`
**Method**: POST
**Auth**: Required

**Request**:
```json
{
  "resume_id": 123,
  "is_public": true
}
```

---

## Creating New APIs

### Step-by-Step Guide

#### Step 1: Create API File

**Location**: `api/your-new-api.php`

**Template**:
```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

// 1. Require authentication
requireLogin();

// 2. Get current user
$user = getCurrentUser();
$userId = $user['id'];

// 3. Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// 4. Get input data
$input = json_decode(file_get_contents('php://input'), true);

// For form data use: $_POST
// For GET parameters use: $_GET

// 5. Validate input
if (empty($input['required_field'])) {
    echo json_encode(['success' => false, 'message' => 'Required field missing']);
    exit();
}

// 6. Database operation
$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
}

try {
    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO table_name (column1, column2) VALUES (?, ?)");
    $stmt->bind_param("ss", $input['field1'], $input['field2']);

    // Execute
    if ($stmt->execute()) {
        $insertId = $stmt->insert_id;

        echo json_encode([
            'success' => true,
            'message' => 'Operation successful',
            'data' => ['id' => $insertId]
        ]);
    } else {
        throw new Exception('Database operation failed');
    }

    $stmt->close();

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Operation failed',
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
```

---

#### Step 2: Create Frontend AJAX Call

**Example**: Using Fetch API

```javascript
async function callYourAPI(data) {
    try {
        const response = await fetch('api/your-new-api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            console.log('Success:', result.data);
            return result;
        } else {
            throw new Error(result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// Usage
const data = {
    required_field: 'value',
    other_field: 'value2'
};

callYourAPI(data)
    .then(result => {
        console.log('API returned:', result);
    })
    .catch(error => {
        console.error('Failed:', error);
    });
```

---

#### Step 3: Test Your API

**Using Browser Console**:
```javascript
fetch('api/your-new-api.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({required_field: 'test'})
})
.then(r => r.json())
.then(d => console.log(d));
```

**Using cURL**:
```bash
curl -X POST http://localhost/ATS/api/your-new-api.php \
  -H "Content-Type: application/json" \
  -d '{"required_field":"test"}' \
  -b "PHPSESSID=your_session_id"
```

---

## Error Handling

### Backend Error Responses

**400 Bad Request**:
```php
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid input']);
```

**401 Unauthorized**:
```php
http_response_code(401);
echo json_encode(['success' => false, 'message' => 'Not authenticated']);
```

**404 Not Found**:
```php
http_response_code(404);
echo json_encode(['success' => false, 'message' => 'Resource not found']);
```

**500 Server Error**:
```php
http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Server error']);
```

### Frontend Error Handling

```javascript
async function apiCall() {
    try {
        const response = await fetch('api/endpoint.php', {...});

        // Check HTTP status
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        // Check API success flag
        if (!data.success) {
            throw new Error(data.message || 'API error');
        }

        return data;

    } catch (error) {
        // Log to console
        console.error('API Error:', error);

        // Show to user
        showNotification(error.message, 'error');

        // Re-throw if needed
        throw error;
    }
}
```

---

## Security

### Authentication Check

Every API must start with:
```php
require_once '../config/session.php';
requireLogin();  // Redirects if not authenticated
```

### SQL Injection Prevention

Always use prepared statements:
```php
// ✅ SAFE
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);

// ❌ UNSAFE - DO NOT USE
$query = "SELECT * FROM users WHERE email = '$email'";
```

### XSS Prevention

Escape output:
```php
echo htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

### CSRF Protection (Implicit)

- Session validation on every API call
- Same-origin policy enforced
- Can add explicit CSRF tokens for extra security

---

## Viva Questions & Answers

**Q: What is AJAX?**
A: Asynchronous JavaScript And XML - technique to send/receive data from server without page reload.

**Q: What's the difference between GET and POST?**
A: GET retrieves data (params in URL), POST sends data (params in body). POST for creating/updating data.

**Q: How do you handle errors in AJAX?**
A: Try-catch blocks in JavaScript, check response.ok, validate data.success flag, show user-friendly messages.

**Q: How does authentication work in APIs?**
A: Session-based. requireLogin() checks $_SESSION['user_id'], redirects if missing.

**Q: What is JSON?**
A: JavaScript Object Notation - lightweight data format for API communication.

**Q: How do you prevent SQL injection?**
A: Prepared statements with parameter binding via MySQLi.

**Q: What is REST API?**
A: Representational State Transfer - architectural style using HTTP methods and resource-based URLs.

**Q: How do you test an API?**
A: Browser console (fetch), Postman, cURL, or browser DevTools Network tab.
