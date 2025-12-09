# Editor System & Dynamic Updates - Complete Guide

## Table of Contents
1. [Overview](#overview)
2. [Editor Types](#editor-types)
3. [Dynamic Preview System](#dynamic-preview-system)
4. [Real-time Updates](#real-time-updates)
5. [Data Flow](#data-flow)
6. [Template Integration](#template-integration)
7. [Save Mechanism](#save-mechanism)

---

## Overview

The editor system provides two ways to create resumes:
1. **Manual Editor** - Form-based with real-time preview
2. **AI Editor** - Conversational interface with automatic data extraction

Both use:
- ✅ **Iframe-based preview** (isolated HTML rendering)
- ✅ **Data attributes** for field mapping
- ✅ **JSON state management** for resume data
- ✅ **AJAX** for auto-save and loading

---

## Editor Types

### 1. Manual Editor

**Files**:
- `editor-modern.php` → Modern template
- `editor-professional.php` → Professional template
- `editor-academic-standard.php` → Academic template
- `js/editor.js` → Shared editor logic

**Key Features**:
- Form inputs for each resume section
- Add/remove experience/education items
- Character counters
- Real-time validation
- Template-specific fields

### 2. AI Editor

**Files**:
- `ai-editor.php` → AI chat interface
- `js/ai-editor.js` → AI logic
- `api/ai-conversation.php` → Backend AI handler

**Key Features**:
- Natural language input
- Conversational flow
- Automatic field extraction
- Context-aware suggestions
- Quick action buttons

---

## Dynamic Preview System

### Architecture

```
┌─────────────────────────────────┐
│   Main Editor Page              │
│   (editor-modern.php)           │
│                                 │
│  ┌───────────┐  ┌────────────┐ │
│  │   Form    │  │   Preview  │ │
│  │  Inputs   │  │   IFrame   │ │
│  │           │  │            │ │
│  │  <input>  │  │ <iframe    │ │
│  │  <input>  │  │   src=     │ │
│  │  <input>  │  │ "modern.   │ │
│  └───────────┘  │  html">    │ │
│                 └────────────┘ │
└─────────────────────────────────┘
```

### Why IFrame?

**Advantages**:
- ✅ **Isolation**: Template CSS doesn't affect main page
- ✅ **Print support**: Can print iframe content directly
- ✅ **Real document**: Actual HTML resume rendered
- ✅ **Security**: Prevents template code from accessing parent

### IFrame Implementation

**HTML**:
```html
<iframe
    id="resumePreview"
    src="templates/modern.html"
    style="width: 100%; height: 100%; border: none;">
</iframe>
```

**JavaScript Access**:
```javascript
const iframe = document.getElementById('resumePreview');
const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

// Now can manipulate template HTML
const nameElement = iframeDoc.querySelector('[data-field="fullName"]');
nameElement.textContent = 'John Doe';
```

---

## Real-time Updates

### Update Flow

```
1. User types in form input
   ↓
2. JavaScript captures input event
   ↓
3. Extract value from form field
   ↓
4. Update JavaScript resumeState object
   ↓
5. Call updatePreview()
   ↓
6. Access iframe document
   ↓
7. Find element by data-field attribute
   ↓
8. Update element's textContent/innerHTML
   ↓
9. Visual update appears instantly
```

### Example: Updating Name Field

**HTML Form** (in `editor-modern.php`):
```html
<input
    type="text"
    id="fullName"
    class="form-input"
    placeholder="Full Name"
    value="<?php echo htmlspecialchars($personalDetails['fullName'] ?? ''); ?>"
    oninput="updateFieldAndPreview('personal_details', 'fullName', this.value)">
```

**Template** (in `templates/modern.html`):
```html
<div class="name" data-field="fullName">Your Name</div>
```

**JavaScript** (in `js/editor.js`):
```javascript
function updateFieldAndPreview(section, field, value) {
    // Update state
    if (section === 'personal_details') {
        currentResumeData.personal_details = currentResumeData.personal_details || {};
        currentResumeData.personal_details[field] = value;
    }

    // Update preview
    updatePreview();
}

function updatePreview() {
    const iframe = document.getElementById('resumePreview');
    const iframeDoc = iframe.contentDocument;

    // Find element in template
    const element = iframeDoc.querySelector('[data-field="fullName"]');

    if (element) {
        element.textContent = currentResumeData.personal_details.fullName || '';
    }
}
```

### Data Attributes System

Templates use `data-field` attributes for field mapping:

```html
<!-- Personal Details -->
<div data-field="fullName">Name</div>
<div data-field="email">Email</div>
<div data-field="phone">Phone</div>

<!-- Summary -->
<div data-section="summary">
    <p data-field="summary">Summary text</p>
</div>

<!-- Experience List -->
<div data-section="experience">
    <div data-field="experience-list">
        <!-- Dynamically generated -->
    </div>
</div>
```

**Why data-field?**
- ✅ Semantic: Clear purpose
- ✅ Flexible: Works with any HTML structure
- ✅ Easy querying: `querySelector('[data-field="name"]')`
- ✅ No class conflicts: Separate from styling

---

## Data Flow

### Resume State Object

**Structure**:
```javascript
let currentResumeData = {
    id: null,                    // Resume ID (if editing)
    resume_title: 'My Resume',   // Title
    template_name: 'modern',     // Template identifier
    personal_details: {          // Object
        fullName: 'John Doe',
        email: 'john@example.com',
        phone: '123-456-7890',
        location: 'New York, NY',
        linkedin: 'linkedin.com/in/johndoe'
    },
    summary_text: 'Professional summary...',  // String
    experience: [                // Array of objects
        {
            job_title: 'Software Engineer',
            company_name: 'Google',
            location: 'Mountain View, CA',
            start_date: 'Jan 2020',
            end_date: 'Present',
            description: 'Developed scalable systems...'
        }
    ],
    education: [                 // Array of objects
        {
            degree: 'B.S. Computer Science',
            institution_name: 'MIT',
            start_date: '2016',
            end_date: '2020',
            gpa: '3.8'
        }
    ],
    skills: 'Python, JavaScript, React, Node.js',  // String
    certifications: [],          // Array
    languages: 'English, Spanish',  // String
    status: 'draft'              // Enum: draft/published/archived
};
```

### State → Preview Sync

**Function**: `updatePreview()`

```javascript
function updatePreview() {
    const iframeDoc = previewIframe.contentDocument;

    // Update different sections
    updatePersonalDetails(iframeDoc);
    updateSummary(iframeDoc);
    updateExperienceList(iframeDoc);
    updateEducationList(iframeDoc);
    updateSkills(iframeDoc);
}

function updatePersonalDetails(iframeDoc) {
    const fieldMapping = {
        'fullName': 'fullName',
        'email': 'email',
        'phone': 'phone',
        'location': 'location',
        'linkedin': 'linkedin'
    };

    Object.keys(fieldMapping).forEach(dataField => {
        const element = iframeDoc.querySelector(`[data-field="${dataField}"]`);
        if (element) {
            const stateField = fieldMapping[dataField];
            const value = currentResumeData.personal_details[stateField] || '';
            element.textContent = value;
        }
    });
}

function updateExperienceList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="experience-list"]');
    if (!container) return;

    // Clear existing
    container.innerHTML = '';

    // Add each experience item
    currentResumeData.experience.forEach(item => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';
        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.job_title || ''}</div>
                    <div class="entry-date">${item.start_date || ''} - ${item.end_date || ''}</div>
                </div>
                <div class="entry-company">${item.company_name || ''}</div>
            </div>
            <div class="entry-description">${item.description || ''}</div>
        `;
        container.appendChild(entry);
    });
}
```

---

## Template Integration

### Template Structure

**File**: `templates/modern.html`

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modern Resume Template</title>
    <style>
        /* Embedded CSS for isolation */
        body { font-family: Arial, sans-serif; }
        .header { margin-bottom: 20px; }
        .name { font-size: 32px; font-weight: bold; }
        /* ... more styles ... */
    </style>
</head>
<body>
    <div class="resume-container">
        <!-- Header -->
        <div class="header">
            <div class="name" data-field="fullName">Your Name</div>
            <div class="title" data-field="professionalTitle">Job Title</div>
            <div class="contact-info">
                <span data-field="email">email@example.com</span>
                <span data-field="phone">123-456-7890</span>
                <span data-field="location">City, State</span>
            </div>
        </div>

        <!-- Summary Section -->
        <div class="section" data-section="summary" style="display: none;">
            <div class="section-title">Summary</div>
            <p class="summary" data-field="summary"></p>
        </div>

        <!-- Experience Section -->
        <div class="section" data-section="experience">
            <div class="section-title">Experience</div>
            <div data-field="experience-list">
                <!-- Dynamically populated -->
            </div>
        </div>

        <!-- Education Section -->
        <div class="section" data-section="education">
            <div class="section-title">Education</div>
            <div data-field="education-list">
                <!-- Dynamically populated -->
            </div>
        </div>

        <!-- Skills Section -->
        <div class="section" data-section="skills" style="display: none;">
            <div class="section-title">Skills</div>
            <p data-field="skills"></p>
        </div>
    </div>
</body>
</html>
```

### Key Template Features

1. **Embedded CSS**: All styles in `<style>` tag (iframe isolation)
2. **data-field**: Identifies updatable elements
3. **data-section**: Controls section visibility
4. **Print styles**: `@media print` for PDF generation
5. **Default content**: Placeholder text for empty fields

### Section Visibility Control

```javascript
function updateSummary(iframeDoc) {
    const summaryElement = iframeDoc.querySelector('[data-field="summary"]');
    const summarySection = iframeDoc.querySelector('[data-section="summary"]');

    if (summarySection) {
        if (currentResumeData.summary_text && currentResumeData.summary_text.trim()) {
            // Show section if has content
            summarySection.style.display = 'block';
            summaryElement.textContent = currentResumeData.summary_text;
        } else {
            // Hide section if empty
            summarySection.style.display = 'none';
        }
    }
}
```

---

## Save Mechanism

### Auto-Save vs Manual Save

**Auto-Save** (every 30 seconds):
```javascript
let autoSaveInterval = setInterval(() => {
    saveResume(true);  // Silent save
}, 30000);
```

**Manual Save** (button click):
```javascript
document.getElementById('saveBtn').addEventListener('click', () => {
    saveResume(false);  // Show notification
});
```

### Save Function

**File**: `js/editor.js`

```javascript
async function saveResume(silent = false) {
    if (!silent) {
        const saveBtn = document.getElementById('saveBtn');
        saveBtn.textContent = 'Saving...';
        saveBtn.disabled = true;
    }

    try {
        // Prepare data
        const resumeData = {
            resume_id: currentResumeData.id,
            resume_title: currentResumeData.resume_title,
            template_name: currentResumeData.template_name,
            personal_details: currentResumeData.personal_details,
            summary_text: currentResumeData.summary_text,
            experience: JSON.stringify(currentResumeData.experience),
            education: JSON.stringify(currentResumeData.education),
            skills: currentResumeData.skills,
            status: 'draft'
        };

        // Send to API
        const response = await fetch('api/save-resume.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(resumeData)
        });

        const data = await response.json();

        if (data.success) {
            // Update ID if new resume
            if (!currentResumeData.id) {
                currentResumeData.id = data.resume_id;
                // Update URL without reload
                window.history.replaceState({}, '', `editor-modern.php?id=${data.resume_id}`);
            }

            if (!silent) {
                showNotification('Resume saved successfully!', 'success');
            }
        } else {
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Save error:', error);
        showNotification('Failed to save resume', 'error');
    } finally {
        if (!silent) {
            saveBtn.textContent = 'Save Resume';
            saveBtn.disabled = false;
        }
    }
}
```

### Backend Save API

**File**: `api/save-resume.php`

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
$resumeId = $input['resume_id'] ?? null;
$userId = getCurrentUserId();

$conn = getDBConnection();

if ($resumeId) {
    // UPDATE existing resume
    $stmt = $conn->prepare("
        UPDATE resumes SET
            resume_title = ?,
            template_name = ?,
            personal_details = ?,
            summary_text = ?,
            experience = ?,
            education = ?,
            skills = ?,
            updated_at = NOW()
        WHERE resume_id = ? AND user_id = ?
    ");

    $stmt->bind_param("sssssssii",
        $input['resume_title'],
        $input['template_name'],
        json_encode($input['personal_details']),
        $input['summary_text'],
        $input['experience'],
        $input['education'],
        $input['skills'],
        $resumeId,
        $userId
    );

    $success = $stmt->execute();

    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Resume updated' : 'Update failed',
        'resume_id' => $resumeId
    ]);

} else {
    // INSERT new resume
    $stmt = $conn->prepare("
        INSERT INTO resumes (
            user_id, resume_title, template_name,
            personal_details, summary_text, experience,
            education, skills, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $status = 'draft';
    $stmt->bind_param("issssssss",
        $userId,
        $input['resume_title'],
        $input['template_name'],
        json_encode($input['personal_details']),
        $input['summary_text'],
        $input['experience'],
        $input['education'],
        $input['skills'],
        $status
    );

    if ($stmt->execute()) {
        $newResumeId = $stmt->insert_id;

        echo json_encode([
            'success' => true,
            'message' => 'Resume created',
            'resume_id' => $newResumeId
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create resume'
        ]);
    }
}
?>
```

---

## Load Mechanism

### Loading Existing Resume

**File**: `editor-modern.php`

```php
<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$resumeId = $_GET['id'] ?? null;
$resumeData = ['template_name' => 'modern'];

if ($resumeId) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT * FROM resumes
        WHERE resume_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $resumeId, getCurrentUserId());
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $resumeData = $result->fetch_assoc();

        // Decode JSON fields
        $resumeData['personal_details'] = json_decode($resumeData['personal_details'] ?? '{}', true);
        $resumeData['experience'] = json_decode($resumeData['experience'] ?? '[]', true);
        $resumeData['education'] = json_decode($resumeData['education'] ?? '[]', true);
    }
}
?>

<script>
// Pass PHP data to JavaScript
const resumeData = <?php echo json_encode($resumeData); ?>;
let currentResumeData = resumeData;

// Load template and populate
document.addEventListener('DOMContentLoaded', () => {
    loadTemplate();
    setTimeout(() => updatePreview(), 500);
});
</script>
```

---

## Viva Questions & Answers

**Q: How does real-time preview work?**
A: JavaScript updates iframe document by finding elements with data-field attributes and changing their textContent.

**Q: Why use iframe for preview?**
A: Isolation - template CSS doesn't affect editor, and we can print iframe content directly.

**Q: How do you map form inputs to preview?**
A: Using data-field attributes. Form updates resumeState, then updatePreview() syncs state to iframe.

**Q: What is the resume state object?**
A: JavaScript object holding all resume data (personal details, experience, education, etc.) in memory.

**Q: How does auto-save work?**
A: setInterval() calls saveResume() every 30 seconds, sending data to api/save-resume.php via AJAX.

**Q: How do you handle arrays like experience?**
A: Loop through array, create HTML elements dynamically, append to container in iframe.

**Q: What happens when user types in a field?**
A: oninput event → update resumeState → call updatePreview() → update iframe → visual change.

**Q: How do you show/hide sections?**
A: Check if data exists, then set section's style.display to 'block' or 'none'.
