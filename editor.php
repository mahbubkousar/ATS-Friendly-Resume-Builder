<?php
require_once 'config/session.php';
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get resume ID or template from URL
$resumeId = $_GET['id'] ?? null;
$templateName = $_GET['template'] ?? null; // Don't default to 'classic' - let user choose via modal

// Load existing resume data if editing
$resumeData = null;
if ($resumeId) {
    $stmt = $conn->prepare("SELECT * FROM resumes WHERE resume_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $resumeId, $user['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $resumeData = $result->fetch_assoc();
        $templateName = $resumeData['template_name'];
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
    <title>Resume Editor - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css?v=9">
    <link rel="stylesheet" href="css/editor.css?v=16">
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
                <button class="nav-cta download-btn" id="downloadBtn">Download PDF</button>
            </div>
        </div>
    </nav>

    <!-- Template Selection Modal (shows on first load) -->
    <div class="template-selection-modal" id="templateSelectionModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Choose Your Resume Template</h2>
                <p>Select a template that best fits your needs. You won't be able to change it later.</p>
            </div>
            <div class="template-grid">
                <?php
                // Fetch templates for modal
                $conn2 = getDBConnection();
                $templatesModalQuery = "SELECT template_name, template_display_name, template_category FROM templates WHERE is_active = 1 ORDER BY template_category, template_id";
                $templatesModalResult = $conn2->query($templatesModalQuery);

                if ($templatesModalResult && $templatesModalResult->num_rows > 0) {
                    while ($template = $templatesModalResult->fetch_assoc()) {
                        $tName = htmlspecialchars($template['template_name']);
                        $tDisplay = htmlspecialchars($template['template_display_name']);
                        $tCategory = htmlspecialchars($template['template_category'] ?? 'professional');

                        echo '<div class="template-card" data-template="' . $tName . '" data-category="' . $tCategory . '">';
                        echo '  <div class="template-preview-thumb">';
                        echo '    <iframe src="templates/' . $tName . '.html?v=3" loading="lazy"></iframe>';
                        echo '  </div>';
                        echo '  <div class="template-info">';
                        echo '    <h3>' . $tDisplay . '</h3>';
                        echo '    <span class="template-category-badge">' . ucfirst($tCategory) . '</span>';
                        echo '  </div>';
                        echo '  <button class="select-template-btn" data-template="' . $tName . '">Select This Template</button>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <div class="editor-container">
        <!-- Left Sidebar: Resume Information -->
        <aside class="editor-sidebar left-sidebar">
            <div class="editor-mode-switch">
                <a href="ai-editor.php" class="mode-switch-btn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Try AI Editor</span>
                </a>
            </div>
            <h2 class="sidebar-section-title">Resume Information</h2>

            <div class="form-section">
                <h3 class="form-section-title">Resume Title</h3>
                <input type="text" class="form-input" id="resumeTitle" placeholder="e.g., Software Engineer Resume" value="<?php echo htmlspecialchars($resumeData['resume_title'] ?? ''); ?>">
            </div>

            <div class="form-section">
                <h3 class="form-section-title">Template</h3>
                <div class="locked-template-display">
                    <span class="template-icon"><i class="fa-solid fa-file-lines"></i></span>
                    <span id="currentTemplateName"><?php
                        if ($templateName) {
                            // Get display name for current template
                            $displayQuery = "SELECT template_display_name FROM templates WHERE template_name = ? LIMIT 1";
                            $stmt = $conn->prepare($displayQuery);
                            $stmt->bind_param("s", $templateName);
                            $stmt->execute();
                            $displayResult = $stmt->get_result();
                            if ($displayResult && $displayResult->num_rows > 0) {
                                $displayRow = $displayResult->fetch_assoc();
                                echo htmlspecialchars($displayRow['template_display_name']);
                            } else {
                                echo htmlspecialchars(ucfirst($templateName));
                            }
                            $stmt->close();
                        } else {
                            echo 'No Template Selected';
                        }
                    ?></span>
                    <span class="locked-badge"><i class="fa-solid fa-lock"></i> Locked</span>
                </div>
                <!-- Hidden input to store template name -->
                <input type="hidden" id="templateSelect" value="<?php echo htmlspecialchars($templateName ?? ''); ?>">
            </div>

            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-user"></i> Personal Details</h3>
                <input type="text" class="form-input" id="fullName" placeholder="Full Name" value="<?php echo htmlspecialchars($personalDetails['fullName'] ?? ''); ?>">
                <input type="text" class="form-input" id="professionalTitle" placeholder="Professional Title" value="<?php echo htmlspecialchars($personalDetails['professionalTitle'] ?? ''); ?>">
                <input type="email" class="form-input" id="email" placeholder="Email" value="<?php echo htmlspecialchars($personalDetails['email'] ?? ''); ?>">
                <input type="tel" class="form-input" id="phone" placeholder="Phone" value="<?php echo htmlspecialchars($personalDetails['phone'] ?? ''); ?>">
                <input type="text" class="form-input" id="location" placeholder="Location" value="<?php echo htmlspecialchars($personalDetails['location'] ?? ''); ?>">
                <input type="text" class="form-input" id="linkedin" placeholder="LinkedIn URL" value="<?php echo htmlspecialchars($personalDetails['linkedin'] ?? ''); ?>">
            </div>

            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-align-left"></i> Professional Summary</h3>
                <textarea class="form-textarea" id="summary" placeholder="Write your professional summary..." rows="6"><?php echo htmlspecialchars($resumeData['summary_text'] ?? ''); ?></textarea>
            </div>

            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-briefcase"></i> Experience</h3>
                <div id="experienceContainer">
                    <!-- Experience items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addExperienceBtn">+ Add Experience</button>
            </div>

            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-graduation-cap"></i> Education</h3>
                <div id="educationContainer">
                    <!-- Education items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addEducationBtn">+ Add Education</button>
            </div>

            <div class="form-section">
                <h3 class="form-section-title"><i class="fa-solid fa-star"></i> Skills</h3>
                <textarea class="form-textarea" id="skills" placeholder="Enter skills separated by commas..." rows="4"></textarea>
            </div>

            <!-- Technical Template: Projects Section -->
            <div class="form-section template-specific" id="projectsSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-code"></i> Projects</h3>
                <div id="projectsContainer">
                    <!-- Project items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addProjectBtn">+ Add Project</button>
            </div>

            <!-- Executive Template: Key Achievements Section -->
            <div class="form-section template-specific" id="achievementsSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-trophy"></i> Key Achievements</h3>
                <textarea class="form-textarea" id="achievements" placeholder="Enter key achievements (one per line)..." rows="6"></textarea>
            </div>

            <!-- Executive Template: Board Memberships Section -->
            <div class="form-section template-specific" id="boardSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-users"></i> Board Memberships</h3>
                <div id="boardContainer">
                    <!-- Board items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addBoardBtn">+ Add Board Membership</button>
            </div>

            <!-- Creative Template: Portfolio Section -->
            <div class="form-section template-specific" id="portfolioSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-images"></i> Portfolio Highlights</h3>
                <div id="portfolioContainer">
                    <!-- Portfolio items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addPortfolioBtn">+ Add Portfolio Item</button>
            </div>

            <!-- Academic Templates: Research Interests Section -->
            <div class="form-section template-specific" id="researchInterestsSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-microscope"></i> Research Interests</h3>
                <textarea class="form-textarea" id="researchInterests" placeholder="Enter research interests..." rows="3"></textarea>
            </div>

            <!-- Academic Templates: Publications Section -->
            <div class="form-section template-specific" id="publicationsSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-book"></i> Publications</h3>
                <div id="publicationsContainer">
                    <!-- Publication items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addPublicationBtn">+ Add Publication</button>
            </div>

            <!-- Academic Templates: Grants & Funding Section -->
            <div class="form-section template-specific" id="grantsSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-hand-holding-dollar"></i> Grants & Funding</h3>
                <div id="grantsContainer">
                    <!-- Grant items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addGrantBtn">+ Add Grant</button>
            </div>

            <!-- Academic Templates: Teaching Section -->
            <div class="form-section template-specific" id="teachingSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-chalkboard-user"></i> Teaching Experience</h3>
                <div id="teachingContainer">
                    <!-- Teaching items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addTeachingBtn">+ Add Teaching Entry</button>
            </div>

            <!-- Academic Templates: References Section -->
            <div class="form-section template-specific" id="referencesSection" style="display: none;">
                <h3 class="form-section-title"><i class="fa-solid fa-user-tie"></i> References</h3>
                <div id="referencesContainer">
                    <!-- Reference items will be added here dynamically -->
                </div>
                <button class="add-btn" id="addReferenceBtn">+ Add Reference</button>
            </div>
        </aside>

        <!-- Center: Resume Preview -->
        <main class="resume-preview">
            <div class="template-header">
                <span class="template-label">Template: <span id="currentTemplateName"><?php echo ucfirst($templateName); ?></span></span>
                <button class="change-template-btn" id="changeTemplateBtn">Change Template</button>
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

                <!-- Input Type Toggle -->
                <div class="input-toggle">
                    <button class="toggle-btn active" id="textToggle" type="button">
                        <i class="fa-solid fa-keyboard"></i> Text Input
                    </button>
                    <button class="toggle-btn" id="fileToggle" type="button">
                        <i class="fa-solid fa-file-pdf"></i> Upload PDF
                    </button>
                </div>

                <!-- Text Input Option -->
                <div class="input-option" id="textInput">
                    <textarea class="form-textarea" id="jobDescText" placeholder="Paste job description here..." rows="6"></textarea>
                </div>

                <!-- File Upload Option -->
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

                <button class="analyze-btn">Analyze Match</button>
            </div>

            <div class="suggestions-section">
                <h3 class="form-section-title">Suggestions</h3>
                <p class="empty-state">Analyze your resume against a job description to get suggestions.</p>
            </div>
        </aside>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Pass resume data to JavaScript
        const resumeData = <?php echo json_encode([
            'id' => $resumeId,
            'resume_title' => $resumeData['resume_title'] ?? '',
            'template_name' => $templateName ?? null,
            'personal_details' => $personalDetails,
            'summary_text' => $resumeData['summary_text'] ?? '',
            'status' => $resumeData['status'] ?? 'draft'
        ]); ?>;
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="js/app.js?v=5"></script>
    <script src="js/editor.js?v=23"></script>
</body>
</html>
