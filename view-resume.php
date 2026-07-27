<?php
require_once 'config/database.php';

// Helper function to show private resume page
function showPrivatePage() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resume Not Available</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha384-t1nt8BQoYMLFN5p42tRAtuAAFQaCQODekUVeKKZrEnEyp4H2R0RHFz0KWpmj7i8g" crossorigin="anonymous">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                background: white;
                border-radius: 16px;
                padding: 3rem;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                animation: slideIn 0.4s ease-out;
            }
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .icon-wrapper {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
            }
            .icon-wrapper i {
                font-size: 2.5rem;
                color: white;
            }
            h1 {
                color: #1f2937;
                font-size: 1.75rem;
                margin: 0 0 1rem;
            }
            p {
                color: #6b7280;
                font-size: 1rem;
                line-height: 1.6;
                margin: 0 0 2rem;
            }
            .cta-text {
                color: #4b5563;
                font-size: 0.9rem;
                margin: 2rem 0 1.5rem;
                font-weight: 500;
            }
            .btn-container {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 0.875rem 2rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            }
            .btn-secondary {
                background: transparent;
                color: #667eea;
                border: 2px solid #667eea;
                padding: 0.875rem 2rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                transition: all 0.2s;
            }
            .btn-secondary:hover {
                background: #667eea;
                color: white;
                transform: translateY(-2px);
            }
            .btn-primary i, .btn-secondary i {
                font-size: 1.1rem;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="icon-wrapper">
                <i class="fas fa-lock"></i>
            </div>
            <h1>Resume Not Available</h1>
            <p>This resume is currently set to private by the owner. If you believe you should have access to this resume, please contact the owner directly.</p>

            <div class="cta-text">Want to create your own professional resume?</div>

            <div class="btn-container">
                <a href="/ATS/register.php" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Create Your Resume for Free
                </a>
                <a href="/ATS/" class="btn-secondary">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

// Helper function to show not found page
function showNotFoundPage() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resume Not Found</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha384-t1nt8BQoYMLFN5p42tRAtuAAFQaCQODekUVeKKZrEnEyp4H2R0RHFz0KWpmj7i8g" crossorigin="anonymous">
        <style>
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .error-card {
                background: white;
                border-radius: 16px;
                padding: 3rem;
                max-width: 500px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                animation: slideIn 0.4s ease-out;
            }
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            .icon-wrapper {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1.5rem;
            }
            .icon-wrapper i {
                font-size: 2.5rem;
                color: white;
            }
            h1 {
                color: #1f2937;
                font-size: 1.75rem;
                margin: 0 0 1rem;
            }
            p {
                color: #6b7280;
                font-size: 1rem;
                line-height: 1.6;
                margin: 0 0 2rem;
            }
            .cta-text {
                color: #4b5563;
                font-size: 0.9rem;
                margin: 2rem 0 1.5rem;
                font-weight: 500;
            }
            .btn-container {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 0.875rem 2rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                transition: transform 0.2s, box-shadow 0.2s;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            }
            .btn-secondary {
                background: transparent;
                color: #667eea;
                border: 2px solid #667eea;
                padding: 0.875rem 2rem;
                border-radius: 8px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                transition: all 0.2s;
            }
            .btn-secondary:hover {
                background: #667eea;
                color: white;
                transform: translateY(-2px);
            }
            .btn-primary i, .btn-secondary i {
                font-size: 1.1rem;
            }
        </style>
    </head>
    <body>
        <div class="error-card">
            <div class="icon-wrapper">
                <i class="fas fa-file-slash"></i>
            </div>
            <h1>Resume Not Found</h1>
            <p>The resume you're looking for doesn't exist or the link may have expired. Please check the URL and try again.</p>

            <div class="cta-text">Why not create your own professional resume?</div>

            <div class="btn-container">
                <a href="/ATS/register.php" class="btn-primary">
                    <i class="fas fa-user-plus"></i> Create Your Resume for Free
                </a>
                <a href="/ATS/" class="btn-secondary">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$token = $_GET['token'] ?? null;

if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(404);
    showNotFoundPage();
    exit();
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM resumes WHERE share_token = ? AND is_public = 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$resumeData = $result->fetch_assoc();
$stmt->close();

if (!$resumeData) {
    http_response_code(404);
    showNotFoundPage();
    exit();
}

$resumeId = $resumeData['resume_id'];

// Track view
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
$referer = $_SERVER['HTTP_REFERER'] ?? null;

// Insert view record
$viewStmt = $conn->prepare("INSERT INTO resume_views (resume_id, ip_address, user_agent, referer) VALUES (?, ?, ?, ?)");
$viewStmt->bind_param("isss", $resumeId, $ipAddress, $userAgent, $referer);
$viewStmt->execute();
$viewStmt->close();

// Update view count
$updateStmt = $conn->prepare("UPDATE resumes SET view_count = view_count + 1, last_viewed_at = NOW() WHERE resume_id = ?");
$updateStmt->bind_param("i", $resumeId);
$updateStmt->execute();
$updateStmt->close();

// Get resume-specific data (NOT user-level data)
$personalDetails = json_decode($resumeData['personal_details'], true) ?? [];
$summaryText = $resumeData['summary_text'] ?? '';
$templateName = $resumeData['template_name'] ?? 'classic';

// Decode resume-specific experience, education, skills from JSON
$experience = json_decode($resumeData['experience'], true) ?? [];
$education = json_decode($resumeData['education'], true) ?? [];

// Handle skills - can be JSON array or comma-separated string
$skillsData = $resumeData['skills'] ?? '';
if (is_string($skillsData) && !empty($skillsData)) {
    // Try to decode as JSON first
    $decodedSkills = json_decode($skillsData, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedSkills)) {
        $skills = $decodedSkills;
    } else {
        // If not JSON, treat as comma-separated string
        $skills = array_map('trim', explode(',', $skillsData));
    }
} else {
    $skills = [];
}

// Load template
// Load template
$templatePath = __DIR__ . '/templates/' . $templateName . '.html';
if (!file_exists($templatePath)) {
    $templatePath = __DIR__ . '/templates/classic.html';
}

// Read the template HTML
$templateHTML = file_get_contents($templatePath);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resumeData['resume_title']); ?> - <?php echo htmlspecialchars($personalDetails['fullName'] ?? 'Resume'); ?></title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha384-t1nt8BQoYMLFN5p42tRAtuAAFQaCQODekUVeKKZrEnEyp4H2R0RHFz0KWpmj7i8g" crossorigin="anonymous">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
        }
        .public-header {
            background: white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .public-header-title {
            margin: 0;
            font-size: 1.25rem;
            color: #7c3aed;
            font-weight: 600;
        }
        .create-resume-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        .create-resume-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .download-btn {
            background: #7c3aed;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            transition: background 0.2s;
        }
        .download-btn:hover {
            background: #6d28d9;
        }
        .resume-wrapper {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }
            .public-header {
                display: none !important;
            }
            .resume-wrapper {
                max-width: none;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>
    <div class="public-header">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <h1 class="public-header-title">
                <i class="fas fa-user"></i> <?php echo htmlspecialchars($personalDetails['fullName'] ?? 'Resume'); ?>
            </h1>
            <a href="/ATS/register.php" class="create-resume-btn">
                <i class="fas fa-plus-circle"></i> Create Your Own
            </a>
        </div>
        <button class="download-btn" onclick="handleDownload()">
            <i class="fas fa-download"></i> Download PDF
        </button>
    </div>

    <div class="resume-wrapper">
        <iframe id="resumeFrame" style="width: 100%; border: none; min-height: 1100px;"></iframe>
    </div>

    <script>
        const resumeToken = <?php echo json_encode($token, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        const templateName = <?php echo json_encode($templateName, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        // Track download
        function handleDownload() {
            // Track download via API
            fetch('/ATS/api/track-download.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ share_token: resumeToken })
            }).then(() => {
                // Trigger print dialog
                window.print();
            }).catch(err => {
                console.error('Failed to track download:', err);
                // Still allow print even if tracking fails
                window.print();
            });
        }

        // Load template and populate with data
        document.addEventListener('DOMContentLoaded', () => {
            const iframe = document.getElementById('resumeFrame');

            // Load template into iframe
            iframe.src = '/ATS/templates/' + templateName + '.html';

            iframe.onload = () => {
                // Wait a bit for template to fully load
                setTimeout(() => {
                    populateResumeData();
                }, 100);
            };
        });

        function populateResumeData() {
            const iframe = document.getElementById('resumeFrame');
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;

            const personalDetails = <?php echo json_encode($personalDetails, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const summary = <?php echo json_encode($summaryText, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const experience = <?php echo json_encode($experience, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const education = <?php echo json_encode($education, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            const skills = <?php echo json_encode($skills, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

            console.log('Populating resume with data:', { personalDetails, summary, experience, education, skills });

            // Field mapping for personal details
            const fieldMapping = {
                fullName: 'name',
                professionalTitle: 'title'
            };

            // Populate personal details
            Object.keys(personalDetails).forEach(key => {
                const value = personalDetails[key];
                if (!value) return;

                const dataField = fieldMapping[key] || key;
                if (!/^[a-zA-Z][\w-]*$/.test(dataField)) return;
                const elements = iframeDoc.querySelectorAll(`[data-field="${dataField}"]`);
                elements.forEach(el => {
                    el.textContent = value;
                });
            });

            // Populate summary
            const summaryEl = iframeDoc.querySelector('[data-field="summary"]');
            const summarySection = iframeDoc.querySelector('[data-section="summary"]');
            if (summaryEl && summary) {
                summaryEl.textContent = summary;
                if (summarySection) summarySection.style.display = 'block';
            }

            // Populate experience
            updateExperienceList(iframeDoc, experience);

            // Populate education
            updateEducationList(iframeDoc, education);

            // Populate skills
            updateSkillsList(iframeDoc, skills);

            // Auto-resize iframe
            resizeIframe();
        }

        function updateExperienceList(iframeDoc, experience) {
            const container = iframeDoc.querySelector('[data-field="experience-list"]');
            if (!container) return;

            if (!experience || experience.length === 0) return;

            container.replaceChildren();
            experience.forEach(item => {
                const entry = iframeDoc.createElement('div');
                entry.className = 'entry';

                // Handle different data formats
                const position = item.position || item.jobTitle || item.job_title || '';
                const company = item.company || item.company_name || '';
                const location = item.location || '';

                // Handle dates - support multiple formats
                let dateStr = '';
                if (item.dates) {
                    dateStr = item.dates;
                } else if (item.startDate && item.endDate) {
                    dateStr = `${item.startDate} - ${item.endDate}`;
                } else if (item.start_date && item.end_date) {
                    dateStr = `${item.start_date} - ${item.end_date}`;
                }

                const header = iframeDoc.createElement('div');
                header.className = 'entry-header';
                const titleLine = iframeDoc.createElement('div');
                titleLine.className = 'entry-title-line';
                titleLine.appendChild(createTextElement(iframeDoc, 'div', 'entry-title', position));
                titleLine.appendChild(createTextElement(iframeDoc, 'div', 'entry-date', dateStr));
                header.appendChild(titleLine);
                header.appendChild(createTextElement(
                    iframeDoc,
                    'div',
                    'entry-company',
                    company + (location ? ', ' + location : '')
                ));
                entry.appendChild(header);

                if (item.description) {
                    const points = item.description.split('\n').filter(p => p.trim());
                    if (points.length > 0) {
                        const description = iframeDoc.createElement('div');
                        description.className = 'entry-description';
                        const list = iframeDoc.createElement('ul');
                        points.forEach(point => {
                            list.appendChild(createTextElement(iframeDoc, 'li', '', point.trim()));
                        });
                        description.appendChild(list);
                        entry.appendChild(description);
                    }
                }

                container.appendChild(entry);
            });
        }

        function updateEducationList(iframeDoc, education) {
            const container = iframeDoc.querySelector('[data-field="education-list"]');
            if (!container) return;

            if (!education || education.length === 0) return;

            container.replaceChildren();
            education.forEach(item => {
                const entry = iframeDoc.createElement('div');
                entry.className = 'entry';

                const degree = item.degree || '';
                const institution = item.institution || '';
                const location = item.location || '';

                // Handle dates
                let dateStr = '';
                if (item.year) {
                    dateStr = item.year;
                } else if (item.startDate && item.endDate) {
                    dateStr = `${item.startDate} - ${item.endDate}`;
                } else if (item.start_date && item.end_date) {
                    dateStr = `${item.start_date} - ${item.end_date}`;
                }

                const header = iframeDoc.createElement('div');
                header.className = 'entry-header';
                const titleLine = iframeDoc.createElement('div');
                titleLine.className = 'entry-title-line';
                titleLine.appendChild(createTextElement(iframeDoc, 'div', 'entry-title', degree));
                titleLine.appendChild(createTextElement(iframeDoc, 'div', 'entry-date', dateStr));
                header.appendChild(titleLine);
                header.appendChild(createTextElement(
                    iframeDoc,
                    'div',
                    'entry-company',
                    institution + (location ? ', ' + location : '')
                ));
                entry.appendChild(header);

                container.appendChild(entry);
            });
        }

        function updateSkillsList(iframeDoc, skills) {
            const skillsContainer = iframeDoc.querySelector('[data-field="skills"]');
            if (!skillsContainer) return;

            skillsContainer.replaceChildren();

            // Handle array of skill categories (from modern editor)
            if (Array.isArray(skills) && skills.length > 0) {
                const firstItem = skills[0];

                // Check if it's skill categories with {category, items} structure
                if (firstItem && typeof firstItem === 'object' && firstItem.category) {
                    skills.forEach(skillCategory => {
                        if (skillCategory.category && skillCategory.items && skillCategory.items.length > 0) {
                            const skillCard = iframeDoc.createElement('div');
                            skillCard.className = 'skill-category';
                            skillCard.appendChild(createTextElement(
                                iframeDoc,
                                'div',
                                'skill-category-title',
                                skillCategory.category
                            ));
                            skillCard.appendChild(createTextElement(
                                iframeDoc,
                                'div',
                                'skill-items',
                                skillCategory.items.join(', ')
                            ));
                            skillsContainer.appendChild(skillCard);
                        }
                    });
                } else {
                    // Plain array of skill strings
                    skills.forEach(skill => {
                        const skillSpan = iframeDoc.createElement('span');
                        skillSpan.className = 'skill-item';
                        skillSpan.textContent = typeof skill === 'string' ? skill : skill.toString();
                        skillsContainer.appendChild(skillSpan);
                    });
                }
            } else if (typeof skills === 'string' && skills.trim()) {
                // Comma-separated string
                skillsContainer.textContent = skills;
            }
        }

        function createTextElement(doc, tagName, className, value) {
            const element = doc.createElement(tagName);
            if (className) element.className = className;
            element.textContent = value == null ? '' : String(value);
            return element;
        }

        function resizeIframe() {
            const iframe = document.getElementById('resumeFrame');
            if (iframe && iframe.contentWindow) {
                const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                const height = iframeDoc.body.scrollHeight;
                iframe.style.height = (height + 50) + 'px';
            }
        }

        // Handle print
        window.onbeforeprint = function() {
            const iframe = document.getElementById('resumeFrame');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.print();
            }
        };
    </script>
</body>
</html>
