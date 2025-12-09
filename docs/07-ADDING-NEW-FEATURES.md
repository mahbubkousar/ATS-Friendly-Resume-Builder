# Adding New Features - Developer Guide

## Table of Contents
1. [Development Workflow](#development-workflow)
2. [Example: Add Resume Tags](#example-add-resume-tags)
3. [Example: Add Email Notifications](#example-add-email-notifications)
4. [Example: Add Resume Analytics](#example-add-resume-analytics)
5. [Testing Guidelines](#testing-guidelines)
6. [Common Patterns](#common-patterns)

---

## Development Workflow

### Standard Process for Adding Features

```
1. Plan Feature
   ├── Define requirements
   ├── Design database schema (if needed)
   └── Plan API endpoints

2. Database Changes
   ├── Create migration SQL
   ├── Update database
   └── Test schema

3. Backend Development
   ├── Create API endpoint(s)
   ├── Add business logic
   └── Test API responses

4. Frontend Development
   ├── Create/update UI
   ├── Add JavaScript logic
   ├── Connect to API
   └── Test user interactions

5. Integration Testing
   ├── Test full workflow
   ├── Check error handling
   └── Verify data persistence

6. Documentation
   └── Update README/docs
```

---

## Example: Add Resume Tags

**Feature**: Allow users to add tags to resumes for organization (e.g., "Tech Jobs", "Remote", "High Priority")

### Step 1: Database Schema

**Create new table**:
```sql
-- Create tags table
CREATE TABLE resume_tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    tag_name VARCHAR(50) NOT NULL,
    tag_color VARCHAR(7) DEFAULT '#3B82F6',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE,
    INDEX idx_resume_id (resume_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Run migration**:
```bash
mysql -u root -p resumesync_db < migration.sql
```

---

### Step 2: Create API Endpoints

#### API 1: Add Tag

**File**: `api/add-resume-tag.php`

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$resumeId = $input['resume_id'] ?? null;
$tagName = trim($input['tag_name'] ?? '');
$tagColor = $input['tag_color'] ?? '#3B82F6';
$userId = getCurrentUserId();

// Validate
if (!$resumeId || empty($tagName)) {
    echo json_encode(['success' => false, 'message' => 'Resume ID and tag name required']);
    exit();
}

// Verify resume ownership
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT resume_id FROM resumes WHERE resume_id = ? AND user_id = ?");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit();
}

// Insert tag
$stmt = $conn->prepare("INSERT INTO resume_tags (resume_id, tag_name, tag_color) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $resumeId, $tagName, $tagColor);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Tag added',
        'tag_id' => $stmt->insert_id,
        'tag_name' => $tagName,
        'tag_color' => $tagColor
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add tag']);
}

$stmt->close();
$conn->close();
?>
```

---

#### API 2: Get Tags

**File**: `api/get-resume-tags.php`

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$resumeId = $_GET['resume_id'] ?? null;
$userId = getCurrentUserId();

if (!$resumeId) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit();
}

$conn = getDBConnection();

// Verify ownership
$stmt = $conn->prepare("SELECT resume_id FROM resumes WHERE resume_id = ? AND user_id = ?");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Resume not found']);
    exit();
}

// Get tags
$stmt = $conn->prepare("SELECT tag_id, tag_name, tag_color FROM resume_tags WHERE resume_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $resumeId);
$stmt->execute();
$result = $stmt->get_result();

$tags = [];
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}

echo json_encode([
    'success' => true,
    'tags' => $tags
]);

$stmt->close();
$conn->close();
?>
```

---

#### API 3: Delete Tag

**File**: `api/delete-resume-tag.php`

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$tagId = $input['tag_id'] ?? null;
$userId = getCurrentUserId();

if (!$tagId) {
    echo json_encode(['success' => false, 'message' => 'Tag ID required']);
    exit();
}

$conn = getDBConnection();

// Verify ownership (join with resumes table)
$stmt = $conn->prepare("
    DELETE rt FROM resume_tags rt
    INNER JOIN resumes r ON rt.resume_id = r.resume_id
    WHERE rt.tag_id = ? AND r.user_id = ?
");
$stmt->bind_param("ii", $tagId, $userId);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Tag deleted']);
} else {
    echo json_encode(['success' => false, 'message' => 'Tag not found']);
}

$stmt->close();
$conn->close();
?>
```

---

### Step 3: Frontend UI

#### HTML (in dashboard.php)

```html
<div class="resume-card" data-resume-id="123">
    <h3 class="resume-title">My Resume</h3>

    <!-- Tags Container -->
    <div class="tags-container" id="tags-123">
        <!-- Tags will be dynamically added here -->
    </div>

    <!-- Add Tag Button -->
    <button class="add-tag-btn" onclick="showAddTagModal(123)">
        <i class="fa fa-plus"></i> Add Tag
    </button>
</div>

<!-- Add Tag Modal -->
<div id="addTagModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Add Tag</h2>
        <input type="text" id="tagNameInput" placeholder="Tag name">
        <input type="color" id="tagColorInput" value="#3B82F6">
        <button onclick="addTag()">Add</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>
```

---

#### CSS (in dashboard.css)

```css
.tags-container {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 10px 0;
}

.tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12px;
    font-weight: 500;
    color: white;
    cursor: pointer;
}

.tag .delete-icon {
    cursor: pointer;
    opacity: 0.7;
    transition: opacity 0.2s;
}

.tag .delete-icon:hover {
    opacity: 1;
}

.add-tag-btn {
    padding: 6px 12px;
    background: #f0f0f0;
    border: 1px dashed #ccc;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    transition: all 0.2s;
}

.add-tag-btn:hover {
    background: #e0e0e0;
    border-color: #999;
}
```

---

#### JavaScript (in dashboard.js)

```javascript
let currentResumeId = null;

// Load tags for a resume
async function loadTags(resumeId) {
    try {
        const response = await fetch(`api/get-resume-tags.php?resume_id=${resumeId}`);
        const data = await response.json();

        if (data.success) {
            const container = document.getElementById(`tags-${resumeId}`);
            container.innerHTML = '';

            data.tags.forEach(tag => {
                const tagEl = document.createElement('span');
                tagEl.className = 'tag';
                tagEl.style.backgroundColor = tag.tag_color;
                tagEl.innerHTML = `
                    ${tag.tag_name}
                    <i class="fa fa-times delete-icon" onclick="deleteTag(${tag.tag_id}, ${resumeId})"></i>
                `;
                container.appendChild(tagEl);
            });
        }
    } catch (error) {
        console.error('Error loading tags:', error);
    }
}

// Show add tag modal
function showAddTagModal(resumeId) {
    currentResumeId = resumeId;
    document.getElementById('addTagModal').style.display = 'block';
    document.getElementById('tagNameInput').value = '';
    document.getElementById('tagColorInput').value = '#3B82F6';
}

// Close modal
function closeModal() {
    document.getElementById('addTagModal').style.display = 'none';
    currentResumeId = null;
}

// Add tag
async function addTag() {
    const tagName = document.getElementById('tagNameInput').value.trim();
    const tagColor = document.getElementById('tagColorInput').value;

    if (!tagName) {
        alert('Please enter a tag name');
        return;
    }

    try {
        const response = await fetch('api/add-resume-tag.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                resume_id: currentResumeId,
                tag_name: tagName,
                tag_color: tagColor
            })
        });

        const data = await response.json();

        if (data.success) {
            closeModal();
            loadTags(currentResumeId);
            showNotification('Tag added successfully', 'success');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error adding tag:', error);
        alert('Failed to add tag');
    }
}

// Delete tag
async function deleteTag(tagId, resumeId) {
    if (!confirm('Delete this tag?')) return;

    try {
        const response = await fetch('api/delete-resume-tag.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ tag_id: tagId })
        });

        const data = await response.json();

        if (data.success) {
            loadTags(resumeId);
            showNotification('Tag deleted', 'success');
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error deleting tag:', error);
    }
}

// Load tags on page load
document.addEventListener('DOMContentLoaded', () => {
    const resumeCards = document.querySelectorAll('.resume-card');
    resumeCards.forEach(card => {
        const resumeId = card.dataset.resumeId;
        loadTags(resumeId);
    });
});
```

---

### Step 4: Testing

**Test Checklist**:
- [ ] Add tag to resume
- [ ] View tags on dashboard
- [ ] Change tag color
- [ ] Delete tag
- [ ] Verify tags persist after refresh
- [ ] Test with multiple resumes
- [ ] Check authorization (can't tag other user's resumes)
- [ ] Test error cases (empty name, invalid ID)

---

## Example: Add Email Notifications

**Feature**: Send email when job application status changes

### Step 1: Install PHPMailer

```bash
composer require phpmailer/phpmailer
```

Or download manually to `vendor/phpmailer/`

---

### Step 2: Email Configuration

**File**: `config/email.php`

```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/phpmailer/phpmailer/src/Exception.php';
require 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require 'vendor/phpmailer/phpmailer/src/SMTP.php';

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com';
        $mail->Password = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('noreply@resumesync.com', 'ResumeSync');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return ['success' => true];

    } catch (Exception $e) {
        error_log("Email error: {$mail->ErrorInfo}");
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
?>
```

---

### Step 3: Update Application Status API

**File**: `api/update-application-status.php` (modify existing)

```php
<?php
// ... existing code ...

// After successful status update
if ($stmt->execute()) {
    // Send email notification
    require_once '../config/email.php';

    $user = getCurrentUser();
    $userEmail = $user['email'];

    $subject = "Application Status Updated: {$input['company_name']}";
    $body = "
        <h2>Application Status Update</h2>
        <p>Your application for <strong>{$input['job_title']}</strong> at <strong>{$input['company_name']}</strong> has been updated.</p>
        <p><strong>New Status:</strong> {$input['status']}</p>
        <p>View details in your <a href='http://localhost/ATS/dashboard.php'>dashboard</a>.</p>
    ";

    sendEmail($userEmail, $subject, $body);

    echo json_encode(['success' => true, 'message' => 'Status updated']);
}
?>
```

---

## Example: Add Resume Analytics

**Feature**: Track resume views and downloads

### Step 1: Database Schema (already exists)

```sql
-- resume_views table (already created)
CREATE TABLE resume_views (
    view_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
);

-- resume_downloads table (already created)
CREATE TABLE resume_downloads (
    download_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    resume_id INT NOT NULL,
    ip_address VARCHAR(45),
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resume_id) REFERENCES resumes(resume_id) ON DELETE CASCADE
);
```

---

### Step 2: Track View API

**File**: `api/track-view.php` (create new)

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';

$resumeId = $_POST['resume_id'] ?? null;
$ipAddress = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'];

if (!$resumeId) {
    echo json_encode(['success' => false]);
    exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("
    INSERT INTO resume_views (resume_id, ip_address, user_agent)
    VALUES (?, ?, ?)
");
$stmt->bind_param("iss", $resumeId, $ipAddress, $userAgent);

if ($stmt->execute()) {
    // Update view count in resumes table
    $conn->query("UPDATE resumes SET view_count = view_count + 1 WHERE resume_id = $resumeId");
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$stmt->close();
$conn->close();
?>
```

---

### Step 3: Update view-resume.php

```php
<?php
// ... existing code to load resume ...

// Track view
$resumeId = $resumeData['resume_id'];
?>

<script>
// Track view when page loads
fetch('api/track-view.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'resume_id=<?php echo $resumeId; ?>'
});
</script>
```

---

### Step 4: Analytics Dashboard

**File**: `api/get-analytics.php`

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

requireLogin();

$resumeId = $_GET['resume_id'] ?? null;
$userId = getCurrentUserId();

if (!$resumeId) {
    echo json_encode(['success' => false, 'message' => 'Resume ID required']);
    exit();
}

$conn = getDBConnection();

// Verify ownership
$stmt = $conn->prepare("SELECT resume_id FROM resumes WHERE resume_id = ? AND user_id = ?");
$stmt->bind_param("ii", $resumeId, $userId);
$stmt->execute();

if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

// Get stats
$stats = [];

// Total views
$result = $conn->query("SELECT COUNT(*) as total FROM resume_views WHERE resume_id = $resumeId");
$stats['total_views'] = $result->fetch_assoc()['total'];

// Total downloads
$result = $conn->query("SELECT COUNT(*) as total FROM resume_downloads WHERE resume_id = $resumeId");
$stats['total_downloads'] = $result->fetch_assoc()['total'];

// Views by date (last 30 days)
$result = $conn->query("
    SELECT DATE(viewed_at) as date, COUNT(*) as views
    FROM resume_views
    WHERE resume_id = $resumeId AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(viewed_at)
    ORDER BY date
");

$stats['views_by_date'] = [];
while ($row = $result->fetch_assoc()) {
    $stats['views_by_date'][] = $row;
}

echo json_encode([
    'success' => true,
    'analytics' => $stats
]);

$conn->close();
?>
```

---

## Testing Guidelines

### Unit Testing (Manual)

1. **Test API Directly**:
   ```javascript
   // In browser console
   fetch('api/your-api.php', {
       method: 'POST',
       headers: {'Content-Type': 'application/json'},
       body: JSON.stringify({test: 'data'})
   })
   .then(r => r.json())
   .then(d => console.log(d));
   ```

2. **Test Database**:
   ```sql
   SELECT * FROM your_table WHERE condition;
   ```

3. **Check Logs**:
   - PHP errors: Check XAMPP error logs
   - JavaScript errors: Browser console (F12)

---

### Integration Testing

**Test Complete Workflow**:
```
1. User action (click button)
2. JavaScript event handler
3. AJAX request
4. Backend processing
5. Database update
6. Response received
7. UI update
8. Verify persistence (refresh page)
```

---

### Error Testing

**Test These Scenarios**:
- [ ] Invalid input (empty, wrong type)
- [ ] Unauthorized access (wrong user)
- [ ] Missing parameters
- [ ] Database connection failure
- [ ] Network error
- [ ] Large data (stress test)

---

## Common Patterns

### Pattern 1: CRUD Operations

**Create → Read → Update → Delete**

Every major feature follows this pattern:
1. Create API for each operation
2. Frontend functions for each operation
3. UI buttons/forms trigger functions
4. Test all four operations

---

### Pattern 2: Modal Forms

**Standard modal pattern**:
```html
<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Modal Title</h2>
        <form id="myForm">
            <!-- Form fields -->
            <button type="submit">Submit</button>
        </form>
    </div>
</div>
```

```javascript
function showModal() {
    document.getElementById('myModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('myModal').style.display = 'none';
}

document.getElementById('myForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    // Handle form submission
});
```

---

### Pattern 3: Loading States

**Show feedback during async operations**:
```javascript
async function saveData() {
    const btn = document.getElementById('saveBtn');

    // Show loading
    btn.disabled = true;
    btn.textContent = 'Saving...';

    try {
        await fetch('api/save.php', {/*...*/});
        showNotification('Saved!', 'success');
    } catch (error) {
        showNotification('Error', 'error');
    } finally {
        // Reset button
        btn.disabled = false;
        btn.textContent = 'Save';
    }
}
```

---

## Viva Questions & Answers

**Q: How do you add a new feature?**
A: Plan → Database schema → API endpoints → Frontend UI → JavaScript logic → Testing → Documentation.

**Q: What is CRUD?**
A: Create, Read, Update, Delete - four basic operations for data management.

**Q: How do you test an API?**
A: Browser console fetch(), Postman, cURL, or DevTools Network tab.

**Q: What is a migration?**
A: SQL script to modify database schema (add table, column, index, etc.).

**Q: How do you handle errors?**
A: Try-catch in JavaScript, error responses in PHP, log errors, show user-friendly messages.

**Q: What is PHPMailer?**
A: PHP library for sending emails via SMTP.

**Q: How do you track analytics?**
A: Insert records on events (views, downloads), aggregate with SQL queries.

**Q: What is a modal?**
A: Popup overlay for forms/dialogs, shown with JavaScript (display: block/none).
