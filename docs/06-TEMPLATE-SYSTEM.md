# Template System & PDF Generation - Complete Guide

## Table of Contents
1. [Template Architecture](#template-architecture)
2. [Template Types](#template-types)
3. [How Templates Work](#how-templates-work)
4. [PDF Generation](#pdf-generation)
5. [Print Styles](#print-styles)
6. [Creating New Templates](#creating-new-templates)

---

## Template Architecture

### Overview

Templates are standalone HTML files with:
- ✅ **Embedded CSS** (no external stylesheets)
- ✅ **data-field attributes** for content injection
- ✅ **Print-optimized styles** (@media print)
- ✅ **A4 page sizing** (210mm × 297mm)
- ✅ **No JavaScript** (pure HTML/CSS)

### Why Standalone HTML?

1. **Iframe Rendering**: Templates load in `<iframe>` for isolation
2. **Print Support**: Can be printed directly as PDF
3. **Portability**: Self-contained, no dependencies
4. **Security**: Cannot access parent page JavaScript

---

## Template Types

### 1. Modern Template

**File**: `templates/modern.html`
**Best For**: Tech industry, startups, creative roles
**Style**: Clean, minimalist, blue accent
**Layout**: Two-column friendly

**Features**:
- Large name header
- Blue section dividers
- Modern sans-serif font (Arial)
- Bullet-friendly experience format

---

### 2. Professional Template

**File**: `templates/professional.html`
**Best For**: Corporate, finance, consulting
**Style**: Traditional, serif font, boxed header
**Layout**: Single-column, formal

**Features**:
- Boxed header section
- Georgia serif font
- Black and white color scheme
- Conservative formatting

---

### 3. Academic Template

**File**: `templates/academic-standard.html`
**Best For**: PhDs, researchers, professors
**Style**: Traditional academic CV
**Layout**: Comprehensive sections

**Features**:
- Research interests section
- Publications list
- Grants and funding
- Teaching experience
- Professional memberships
- References

---

## How Templates Work

### Template Structure

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Template Name</title>
    <style>
        /* ALL CSS EMBEDDED HERE */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Print styles */
        @page {
            size: A4;
            margin: 0.75in 1in;
        }

        @media print {
            /* Print-specific styles */
        }

        /* Regular styles */
        body { font-family: Arial, sans-serif; }
        .header { margin-bottom: 20px; }
        /* ... more styles ... */
    </style>
</head>
<body>
    <div class="resume-container">
        <!-- Template content with data-field attributes -->
    </div>
</body>
</html>
```

---

### Data Binding System

#### Personal Details

**Template HTML**:
```html
<div class="header">
    <div class="name" data-field="fullName">Your Name</div>
    <div class="title" data-field="professionalTitle">Job Title</div>
    <div class="contact-info">
        <span data-field="email">email@example.com</span>
        <span data-field="phone">123-456-7890</span>
        <span data-field="location">City, State</span>
        <span data-field="linkedin">linkedin.com/in/profile</span>
    </div>
</div>
```

**JavaScript Update**:
```javascript
function updatePersonalDetails(iframeDoc) {
    const fields = ['fullName', 'professionalTitle', 'email', 'phone', 'location', 'linkedin'];

    fields.forEach(field => {
        const element = iframeDoc.querySelector(`[data-field="${field}"]`);
        if (element) {
            const value = resumeState.personal_details[field] || '';
            element.textContent = value;

            // Hide if empty
            if (!value) {
                element.style.display = 'none';
            } else {
                element.style.display = '';
            }
        }
    });
}
```

---

#### Summary Section

**Template HTML**:
```html
<div class="section" data-section="summary" style="display: none;">
    <div class="section-title">Professional Summary</div>
    <p class="summary" data-field="summary"></p>
</div>
```

**JavaScript Update**:
```javascript
function updateSummary(iframeDoc) {
    const summaryElement = iframeDoc.querySelector('[data-field="summary"]');
    const summarySection = iframeDoc.querySelector('[data-section="summary"]');

    if (resumeState.summary_text && resumeState.summary_text.trim()) {
        summarySection.style.display = 'block';
        summaryElement.textContent = resumeState.summary_text;
    } else {
        summarySection.style.display = 'none';
    }
}
```

**How It Works**:
1. Section initially hidden (`display: none`)
2. JavaScript checks if summary exists
3. If yes: show section + fill content
4. If no: keep hidden

---

#### Experience List (Dynamic)

**Template HTML**:
```html
<div class="section" data-section="experience">
    <div class="section-title">Work Experience</div>
    <div data-field="experience-list">
        <!-- JavaScript will populate this -->
    </div>
</div>
```

**JavaScript Update**:
```javascript
function updateExperienceList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="experience-list"]');

    // Clear existing entries
    container.innerHTML = '';

    // Loop through experience array
    resumeState.experience.forEach(item => {
        // Create entry element
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';

        // Build HTML
        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.jobTitle || ''}</div>
                    <div class="entry-date">${item.dates || ''}</div>
                </div>
                <div class="entry-company">${item.company || ''}</div>
            </div>
            ${item.description ? `<div class="entry-description">${item.description}</div>` : ''}
        `;

        // Append to container
        container.appendChild(entry);
    });
}
```

**Flow**:
```
1. Get container element
   ↓
2. Clear any existing content
   ↓
3. Loop through experience array
   ↓
4. For each item: create div, set HTML, append
   ↓
5. Result: Dynamically generated list
```

---

#### Education List (Similar Pattern)

**Template HTML**:
```html
<div class="section" data-section="education">
    <div class="section-title">Education</div>
    <div data-field="education-list"></div>
</div>
```

**JavaScript Update**:
```javascript
function updateEducationList(iframeDoc) {
    const container = iframeDoc.querySelector('[data-field="education-list"]');
    container.innerHTML = '';

    resumeState.education.forEach(item => {
        const entry = iframeDoc.createElement('div');
        entry.className = 'entry';
        entry.innerHTML = `
            <div class="entry-header">
                <div class="entry-title-line">
                    <div class="entry-title">${item.degree || ''}</div>
                    <div class="entry-date">${item.dates || ''}</div>
                </div>
                <div class="entry-company">${item.institution || ''}</div>
            </div>
        `;
        container.appendChild(entry);
    });
}
```

---

## PDF Generation

### How It Works

**User clicks "Download PDF"** →
**JavaScript triggers print dialog** →
**Browser uses print styles** →
**User saves as PDF**

### Implementation

**HTML Button**:
```html
<button id="downloadBtn" onclick="downloadPDF()">
    <i class="fa-solid fa-download"></i>
    Download PDF
</button>
```

**JavaScript Function**:
```javascript
function downloadPDF() {
    try {
        // Get iframe window
        const iframeWindow = previewIframe.contentWindow;

        // Trigger print dialog
        iframeWindow.print();

        // Browser handles the rest (PDF generation)
    } catch (error) {
        console.error('Print error:', error);
        showNotification('Error opening print dialog', 'error');
    }
}
```

**How Browser Print Works**:
1. `window.print()` opens print dialog
2. Browser applies `@media print` styles
3. User selects "Save as PDF" or printer
4. Browser renders PDF using print stylesheet

---

## Print Styles

### @page Rule

**Purpose**: Define page dimensions and margins

```css
@page {
    size: A4;              /* 210mm × 297mm */
    margin: 0.75in 1in;    /* Top/Bottom: 0.75in, Left/Right: 1in */
}
```

### @media print Block

**Purpose**: Styles that ONLY apply when printing

```css
@media print {
    /* 1. Force exact page size */
    html, body {
        width: 210mm;
        height: 297mm;
        margin: 0;
        padding: 0;
        overflow: visible;
    }

    /* 2. Remove editor-specific elements */
    .resume-container {
        padding: 0;
        margin: 0;
        box-shadow: none;  /* Remove shadow for print */
    }

    /* 3. Page break control */
    .section {
        page-break-inside: avoid;  /* Keep sections together */
    }

    .entry {
        page-break-inside: avoid;  /* Keep experience entries together */
    }

    .section-title {
        page-break-after: avoid;   /* Keep title with content */
    }

    /* 4. Typography adjustments */
    body {
        font-size: 12pt;  /* Print-friendly size */
        color: #000;      /* Pure black for printing */
    }

    /* 5. Prevent orphans and widows */
    p, li {
        orphans: 3;  /* Min 3 lines at bottom of page */
        widows: 3;   /* Min 3 lines at top of page */
    }
}
```

### Page Break Control

**Properties Explained**:

| Property | Values | Purpose |
|----------|--------|---------|
| `page-break-before` | `auto`, `always`, `avoid` | Break before element |
| `page-break-after` | `auto`, `always`, `avoid` | Break after element |
| `page-break-inside` | `auto`, `avoid` | Break inside element |
| `orphans` | Number | Min lines at page bottom |
| `widows` | Number | Min lines at page top |

**Example Usage**:
```css
/* Force new page before this section */
.education-section {
    page-break-before: always;
}

/* Never split this element across pages */
.experience-entry {
    page-break-inside: avoid;
}

/* Keep title with following content */
.section-title {
    page-break-after: avoid;
}
```

---

### Print Optimization Tips

1. **Use points (pt) for print**:
   ```css
   @media print {
       body { font-size: 11pt; }
       h1 { font-size: 18pt; }
   }
   ```

2. **Remove colors for B&W printing**:
   ```css
   @media print {
       * {
           color: #000 !important;
           background: #fff !important;
       }
   }
   ```

3. **Hide unnecessary elements**:
   ```css
   @media print {
       .no-print {
           display: none !important;
       }
   }
   ```

4. **Ensure contrast**:
   ```css
   @media print {
       a {
           color: #000;
           text-decoration: underline;
       }
   }
   ```

---

## Creating New Templates

### Step 1: Create HTML File

**Location**: `templates/your-template.html`

**Base Structure**:
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Template Name</title>
    <style>
        /* Reset */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        /* Print setup */
        @page {
            size: A4;
            margin: 0.75in 1in;
        }

        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
            }
            .section { page-break-inside: avoid; }
        }

        /* Your custom styles */
        body {
            font-family: 'Your Font', sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .resume-container {
            max-width: 850px;
            margin: 0 auto;
            padding: 40px;
        }

        .header {
            margin-bottom: 30px;
        }

        .name {
            font-size: 32px;
            font-weight: bold;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        /* Add more styles... */
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
            </div>
        </div>

        <!-- Summary -->
        <div class="section" data-section="summary" style="display: none;">
            <div class="section-title">Summary</div>
            <p data-field="summary"></p>
        </div>

        <!-- Experience -->
        <div class="section" data-section="experience">
            <div class="section-title">Experience</div>
            <div data-field="experience-list"></div>
        </div>

        <!-- Education -->
        <div class="section" data-section="education">
            <div class="section-title">Education</div>
            <div data-field="education-list"></div>
        </div>

        <!-- Skills -->
        <div class="section" data-section="skills" style="display: none;">
            <div class="section-title">Skills</div>
            <p data-field="skills"></p>
        </div>
    </div>
</body>
</html>
```

---

### Step 2: Add to Template Config

**File**: `js/editor.js`

```javascript
const TEMPLATE_CONFIG = {
    'modern': {
        type: 'professional',
        fields: []
    },
    'professional': {
        type: 'professional',
        fields: []
    },
    'academic-standard': {
        type: 'academic',
        fields: ['researchInterests', 'publications', 'grants', 'teaching']
    },
    // Add your template
    'your-template': {
        type: 'professional',  // or 'academic'
        fields: []             // Optional: add special fields
    }
};
```

---

### Step 3: Create Editor Page

**File**: `editor-your-template.php`

```php
<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();

$resumeId = $_GET['id'] ?? null;
$resumeData = ['template_name' => 'your-template'];

if ($resumeId) {
    // Load existing resume
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM resumes WHERE resume_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resumeId, getCurrentUserId());
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $resumeData = $result->fetch_assoc();
        $resumeData['personal_details'] = json_decode($resumeData['personal_details'] ?? '{}', true);
        $resumeData['experience'] = json_decode($resumeData['experience'] ?? '[]', true);
        $resumeData['education'] = json_decode($resumeData['education'] ?? '[]', true);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Your Template Editor</title>
    <link rel="stylesheet" href="css/editor.css">
</head>
<body>
    <!-- Copy editor structure from editor-modern.php -->
    <!-- Update iframe src to your template -->
    <iframe id="resumePreview" src="templates/your-template.html"></iframe>

    <script>
        const resumeData = <?php echo json_encode($resumeData); ?>;
    </script>
    <script src="js/editor.js"></script>
</body>
</html>
```

---

### Step 4: Add to Database

**Insert into templates table**:
```sql
INSERT INTO templates (
    template_name,
    template_display_name,
    template_category,
    description,
    preview_url,
    is_active
) VALUES (
    'your-template',
    'Your Template Name',
    'professional',
    'Description of your template',
    'templates/your-template.html',
    1
);
```

---

### Step 5: Test Template

1. **Preview**: Open `templates/your-template.html` directly
2. **Editor**: Navigate to `editor-your-template.php`
3. **Print**: Test PDF generation (Ctrl+P or Cmd+P)
4. **Data**: Fill form, check preview updates
5. **Save**: Verify data saves correctly

---

## Viva Questions & Answers

**Q: How do templates work?**
A: Standalone HTML files with embedded CSS, loaded in iframe, updated via JavaScript using data-field attributes.

**Q: How is PDF generated?**
A: Browser's print dialog (window.print()), user saves as PDF, uses @media print styles.

**Q: What is @page rule?**
A: CSS rule defining page dimensions and margins for print, e.g., @page { size: A4; margin: 1in; }

**Q: What is page-break-inside?**
A: CSS property controlling if element can split across pages. 'avoid' keeps element together.

**Q: Why use iframe for templates?**
A: Isolation - template CSS doesn't affect editor, can print iframe directly, security.

**Q: How do you update template content?**
A: Access iframe document, find elements by data-field, update textContent or innerHTML.

**Q: What is data-field attribute?**
A: Custom attribute marking elements for content injection, e.g., data-field="fullName".

**Q: How do you hide empty sections?**
A: Check if data exists, set section's style.display = 'none' if empty.
