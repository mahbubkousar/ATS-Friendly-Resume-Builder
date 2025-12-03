<?php
require_once 'config/session.php';
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get resume ID from URL
$resumeId = $_GET['id'] ?? null;
$templateName = 'academic-standard'; // Fixed template for this editor

// Load existing resume data if editing
$resumeData = null;
if ($resumeId) {
    $stmt = $conn->prepare("SELECT * FROM resumes WHERE resume_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resumeId, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $resumeData = $result->fetch_assoc();
    }
    $stmt->close();
}

// Get user data for new resume
$personalDetails = [];
if (!$resumeData) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $user['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
            $personalDetails = [
                'fullName' => $userData['full_name'] ?? '',
                'email' => $userData['email'] ?? '',
                'phone' => $userData['phone'] ?? '',
                'location' => ($userData['city'] ?? '') . ($userData['state'] ? ', ' . $userData['state'] : ''),
                'professionalTitle' => $userData['professional_title'] ?? '',
                'linkedin' => ''
            ];
        }
        $stmt->close();
    }
} else {
    $personalDetails = json_decode($resumeData['personal_details'], true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Standard Resume Editor - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=4">
    <link rel="stylesheet" href="css/editor.css?v=13">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="editor-body">
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none; color: inherit;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link">ATS Converter</a>
                <button class="nav-cta" id="saveResumeBtn">Save Resume</button>
                <button class="nav-cta download-btn" id="downloadBtn">Print / Download PDF</button>
            </div>
        </div>
    </nav>

    <div class="editor-container">
        <!-- Left Sidebar: Resume Form -->
        <aside class="editor-sidebar left-sidebar">
            <div class="editor-mode-switch">
                <a href="ai-editor.php" class="mode-switch-btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Try AI Editor</span>
                </a>
            </div>
            
            <h2 class="sidebar-section-title">Academic Standard Resume</h2>

            <!-- Resume Title -->
            <div class="form-section">
                <h3 class="form-section-title">Resume Title</h3>
                <input type="text" class="form-input" id="resumeTitle" placeholder="e.g., Software Engineer Resume" value="<?php echo htmlspecialchars($resumeData['resume_title'] ?? ''); ?>">
            </div>

            <!-- Template Display (Locked) -->
            <div class="form-section">
                <h3 class="form-section-title">Template</h3>
                <div class="locked-template-display">
                    <span class="template-icon"><i class="fa-solid fa-file-lines"></i></span>
                    <span id="currentTemplateName">Academic Standard</span>
                    <span class="locked-badge"><i class="fa-solid fa-lock"></i> Locked</span>
                </div>
            </div>

            <!-- Personal Details -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-user"></i> Personal Details</h3>
                <input type="text" class="form-input" id="fullName" placeholder="Full Name" value="<?php echo htmlspecialchars($personalDetails['fullName'] ?? ''); ?>">
                <input type="text" class="form-input" id="professionalTitle" placeholder="Professional Title (e.g., Assistant Professor of Psychology)" value="<?php echo htmlspecialchars($personalDetails['professionalTitle'] ?? ''); ?>">
                <input type="text" class="form-input" id="department" placeholder="Department & Institution" value="<?php echo htmlspecialchars($personalDetails['department'] ?? ''); ?>">
                <input type="email" class="form-input" id="email" placeholder="Email" value="<?php echo htmlspecialchars($personalDetails['email'] ?? ''); ?>">
                <input type="tel" class="form-input" id="phone" placeholder="Phone" value="<?php echo htmlspecialchars($personalDetails['phone'] ?? ''); ?>">
                <input type="text" class="form-input" id="location" placeholder="Location" value="<?php echo htmlspecialchars($personalDetails['location'] ?? ''); ?>">
                <input type="text" class="form-input" id="orcid" placeholder="ORCID (e.g., 0000-0001-2345-6789)" value="<?php echo htmlspecialchars($personalDetails['orcid'] ?? ''); ?>">
                <input type="text" class="form-input" id="googleScholar" placeholder="Google Scholar Profile URL" value="<?php echo htmlspecialchars($personalDetails['googleScholar'] ?? ''); ?>">
            </div>

            <!-- Professional Summary -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-align-left"></i> Professional Summary</h3>
                <textarea class="form-textarea" id="summary" placeholder="Write your professional summary..." rows="6"><?php echo htmlspecialchars($resumeData['summary_text'] ?? ''); ?></textarea>
            </div>

            <!-- Experience -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-briefcase"></i> Work Experience</h3>
                <div id="experienceContainer"></div>
                <button class="add-item-btn" id="addExperienceBtn">
                    <i class="fa-solid fa-plus"></i> Add Experience
                </button>
            </div>

            <!-- Education -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-graduation-cap"></i> Education</h3>
                <div id="educationContainer"></div>
                <button class="add-item-btn" id="addEducationBtn">
                    <i class="fa-solid fa-plus"></i> Add Education
                </button>
            </div>

            <!-- Research Interests -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-microscope"></i> Research Interests</h3>
                <textarea class="form-textarea" id="researchInterests" placeholder="Enter your research interests and areas of expertise..." rows="3"></textarea>
            </div>

            <!-- Publications -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-book"></i> Publications</h3>
                <div id="publicationsContainer"></div>
                <button class="add-item-btn" id="addPublicationBtn">
                    <i class="fa-solid fa-plus"></i> Add Publication
                </button>
            </div>

            <!-- Grants & Funding -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-hand-holding-dollar"></i> Grants & Funding</h3>
                <div id="grantsContainer"></div>
                <button class="add-item-btn" id="addGrantBtn">
                    <i class="fa-solid fa-plus"></i> Add Grant
                </button>
            </div>

            <!-- Teaching Experience -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-chalkboard-user"></i> Teaching Experience</h3>
                <div id="teachingContainer"></div>
                <button class="add-item-btn" id="addTeachingBtn">
                    <i class="fa-solid fa-plus"></i> Add Teaching Entry
                </button>
            </div>

            <!-- Skills & Expertise -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-star"></i> Skills & Expertise</h3>
                <textarea class="form-textarea" id="skills" placeholder="Enter your academic and technical skills&#10;&#10;Research Methods, Statistical Analysis, Python, R, LaTeX" rows="3"></textarea>
            </div>

            <!-- Professional Memberships -->
            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-users"></i> Professional Memberships</h3>
                <textarea class="form-textarea" id="memberships" placeholder="Enter professional society memberships&#10;&#10;• American Psychological Association (APA)&#10;• Society for Research in Child Development" rows="3"></textarea>
            </div>
        </aside>

        <!-- Center: Resume Preview -->
        <main class="resume-preview">
            <div class="template-header">
                <span class="template-label">Template: <span id="currentTemplateNameDisplay">Academic Standard</span></span>
            </div>

            <div class="resume-paper">
                <iframe id="resumePreview"></iframe>
            </div>
        </main>

        <!-- Right Sidebar: AI Analysis -->
        <aside class="editor-sidebar right-sidebar">
            <h2 class="sidebar-section-title"><i class="fa-solid fa-robot"></i> AI Analysis</h2>

            <div class="ats-score-display">
                <div class="score-circle">
                    <svg viewBox="0 0 100 100" class="score-svg">
                        <circle cx="50" cy="50" r="45" class="score-bg"></circle>
                        <circle cx="50" cy="50" r="45" class="score-fill" style="stroke-dashoffset: 282"></circle>
                    </svg>
                    <div class="score-text">--</div>
                </div>
                <p class="score-label">ATS Score</p>
                <p class="score-status">Not analyzed yet</p>
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Job Description</h3>

                <div class="input-toggle">
                    <button class="toggle-btn active" id="textToggle" type="button">
                        <i class="fa-solid fa-keyboard"></i> Text Input
                    </button>
                    <button class="toggle-btn" id="fileToggle" type="button">
                        <i class="fa-solid fa-file-pdf"></i> Upload PDF
                    </button>
                </div>

                <div class="input-option" id="textInput">
                    <textarea class="form-textarea" id="jobDescText" placeholder="Paste job description here..." rows="6"></textarea>
                </div>

                <div class="input-option hidden" id="fileInput">
                    <div class="file-upload-area">
                        <input type="file" id="jobDescFile" accept=".pdf" hidden>
                        <label for="jobDescFile" class="file-upload-label">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span class="upload-text">Click to upload or drag and drop</span>
                            <span class="upload-subtext">PDF files only (Max 10MB)</span>
                        </label>
                        <div class="file-info hidden" id="fileInfo">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span class="file-name" id="fileName"></span>
                            <button class="remove-file-btn" id="removeFile" type="button">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="analyze-btn" id="analyzeBtn">Analyze Match</button>
            </div>

            <div class="suggestions-section">
                <h3 class="form-section-title">Suggestions</h3>
                <div id="suggestionsContent">
                    <p class="empty-state">Analyze your resume against a job description to get suggestions.</p>
                </div>
            </div>
        </aside>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

<!-- MODALS -->
    <!-- Notification Modal -->
    <div class="modal-overlay" id="notificationModal" style="display: none;">
        <div class="notification-modal" id="notificationModalContent">
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="notificationTitle">Success</h3>
            <p id="notificationMessage">Operation completed successfully</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-primary" id="notificationOkBtn">OK</button>
            </div>
        </div>
    </div>

    <!-- Analysis Progress Modal -->
    <div class="modal-overlay" id="analysisProgressModal" style="display: none;">
        <div class="progress-modal">
            <div class="progress-icon">
                <i class="fas fa-wand-magic-sparkles fa-spin"></i>
            </div>
            <h3 class="progress-title">Analyzing Your Resume</h3>
            <p class="progress-stage" id="progressStage">Initializing analysis...</p>

            <div class="progress-bar-container">
                <div class="progress-bar-fill" id="progressBarFill"></div>
            </div>
            <div class="progress-percentage" id="progressPercentage">0%</div>

            <div class="progress-steps">
                <div class="progress-step" id="step1">
                    <i class="fas fa-file-pdf"></i>
                    <span>Extracting Text</span>
                </div>
                <div class="progress-step" id="step2">
                    <i class="fas fa-align-left"></i>
                    <span>Analyzing Format</span>
                </div>
                <div class="progress-step" id="step3">
                    <i class="fas fa-key"></i>
                    <span>Checking Keywords</span>
                </div>
                <div class="progress-step" id="step4">
                    <i class="fas fa-list-check"></i>
                    <span>Evaluating Structure</span>
                </div>
                <div class="progress-step" id="step5">
                    <i class="fas fa-chart-bar"></i>
                    <span>Generating Insights</span>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Pass resume data to JavaScript
        const resumeData = <?php echo json_encode([
            'id' => $resumeId,
            'resume_title' => $resumeData['resume_title'] ?? '',
            'template_name' => $templateName,
            'personal_details' => $personalDetails,
            'summary_text' => $resumeData['summary_text'] ?? '',
            'status' => $resumeData['status'] ?? 'draft',
            'experience' => $resumeData['experience'] ?? null,
            'education' => $resumeData['education'] ?? null,
            'skills' => $resumeData['skills'] ?? null,
            'researchInterests' => $resumeData['research_interests'] ?? '',
            'publications' => $resumeData['publications'] ?? null,
            'grants' => $resumeData['grants'] ?? null,
            'teaching' => $resumeData['teaching'] ?? null,
            'memberships' => $resumeData['memberships'] ?? ''
        ]); ?>;
    </script>
    <script src="js/navigation-fix.js"></script>
    <script src="js/modal-utils.js?v=5"></script>
    <script src="js/app.js?v=5"></script>
    <script src="js/editor-academic-standard.js?v=14"></script>
</body>
</html>
