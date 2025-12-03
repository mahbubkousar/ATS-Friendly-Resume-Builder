<?php
require_once 'config/session.php';
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();
$conn = getDBConnection();

// Get resume ID or template from URL
$resumeId = $_GET['id'] ?? null;
$templateName = $_GET['template'] ?? null;

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
    <title>AI Resume Editor - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=9">
    <link rel="stylesheet" href="css/editor.css?v=16">
    <link rel="stylesheet" href="css/ai-editor.css?v=2">
</head>
<body class="ai-editor-body">
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

    <!-- Template Selection Modal -->
    <div class="template-selection-modal" id="templateSelectionModal">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2>Choose Your Resume Template</h2>
                <p>Select a template for your AI-powered resume. You won't be able to change it later.</p>
            </div>
            <div class="template-grid">
                <?php
                // Show basic templates for AI editor
                $aiTemplates = [
                    ['name' => 'modern', 'display' => 'Modern', 'category' => 'professional'],
                    ['name' => 'professional', 'display' => 'Professional', 'category' => 'professional'],
                    ['name' => 'academic-standard', 'display' => 'Academic Standard', 'category' => 'academic']
                ];

                foreach ($aiTemplates as $template) {
                    $tName = htmlspecialchars($template['name']);
                    $tDisplay = htmlspecialchars($template['display']);
                    $tCategory = htmlspecialchars($template['category']);

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
                ?>
            </div>
        </div>
    </div>

    <div class="ai-editor-container">
        <!-- Left Panel: Chat Interface -->
        <aside class="chat-panel">
            <div class="chat-header">
                <div class="chat-header-content">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <div>
                        <h2>AI Resume Assistant</h2>
                        <p>Chat to build your resume</p>
                    </div>
                </div>
                <button class="new-chat-btn" id="newChatBtn" title="Start over with new resume">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset</span>
                </button>
            </div>

            <div class="template-indicator" id="templateIndicator" style="display: none;">
                <i class="fa-solid fa-file-lines"></i>
                <span id="templateNameDisplay">Loading...</span>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="message assistant-message">
                    <div class="message-avatar">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>Hi! I'm your AI resume assistant powered by Google Gemini. I'll help you build an ATS-optimized resume through conversation.</p>
                        <p>Let's start with some basics. <strong>What's your full name and the job title you're targeting?</strong></p>
                    </div>
                </div>
            </div>

            <div class="chat-input-container">
                <form class="chat-input-form" id="chatForm">
                    <textarea
                        class="chat-input"
                        id="chatInput"
                        placeholder="Type your message..."
                        rows="1"
                    ></textarea>
                    <button type="submit" class="send-btn" id="sendBtn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
                <div class="quick-actions">
                    <button class="quick-action-btn" data-action="add-experience">
                        <i class="fa-solid fa-briefcase"></i>
                        Add Experience
                    </button>
                    <button class="quick-action-btn" data-action="add-education">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Add Education
                    </button>
                    <button class="quick-action-btn" data-action="add-skills">
                        <i class="fa-solid fa-star"></i>
                        Add Skills
                    </button>
                </div>
            </div>
        </aside>

        <!-- Right Panel: Live Preview -->
        <section class="preview-panel">
            <div class="preview-header">
                <h3 class="preview-title">
                    <i class="fa-solid fa-eye"></i>
                    Live Preview
                </h3>
                <div class="preview-actions">
                    <button class="preview-action-btn" id="zoomOutBtn" title="Zoom Out">
                        <i class="fa-solid fa-minus"></i>
                    </button>
                    <span class="zoom-level" id="zoomLevel">100%</span>
                    <button class="preview-action-btn" id="zoomInBtn" title="Zoom In">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    <button class="preview-action-btn" id="refreshBtn" title="Refresh Preview">
                        <i class="fa-solid fa-rotate-right"></i>
                    </button>
                </div>
            </div>

            <div class="preview-content" id="previewContent">
                <div class="resume-paper">
                    <iframe id="resumePreview"></iframe>
                </div>
            </div>
        </section>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

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
            'skills' => $resumeData['skills'] ?? null
        ]); ?>;

        const userId = <?php echo $user['id']; ?>;
    </script>
    <script src="config/gemini-config.php"></script>
    <script src="js/navigation-fix.js"></script>
    <script src="js/modal-utils.js?v=5"></script>
    <script src="js/ai-editor.js?v=3"></script>
</body>
</html>
