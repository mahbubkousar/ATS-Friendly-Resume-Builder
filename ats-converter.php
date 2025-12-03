<?php
require_once 'config/session.php';
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATS Resume Converter - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=8">
    <link rel="stylesheet" href="css/ats-converter.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none; color: inherit;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link active">ATS Converter</a>
                <a href="logout.php" class="nav-cta">Logout</a>
            </div>
        </div>
    </nav>

    <div class="converter-container">
        <!-- Step 1: Upload Section -->
        <div class="upload-section" id="uploadSection">
            <div class="converter-header">
                <h1>ATS Resume Converter</h1>
                <p>Transform your existing resume into an ATS-friendly format optimized for your target job</p>
            </div>

            <div class="upload-card">
                <div class="upload-step">
                    <div class="step-number">1</div>
                    <h3>Upload Your Current Resume</h3>
                    <div class="file-upload-area" id="resumeUploadArea">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Drag & drop your resume here or click to browse</p>
                        <span class="file-types">Supports: PDF, DOCX, DOC, TXT</span>
                        <input type="file" id="resumeFile" accept=".pdf,.docx,.doc,.txt" hidden>
                    </div>
                    <div class="uploaded-file" id="resumeFileInfo" style="display: none;">
                        <i class="fa-solid fa-file-check"></i>
                        <span class="file-name"></span>
                        <button class="remove-file-btn" onclick="removeResumeFile()">
                            <i class="fa-solid fa-times"></i>
                        </button>
                    </div>
                </div>

                <div class="upload-step">
                    <div class="step-number">2</div>
                    <h3>Enter Job Description</h3>
                    <textarea
                        id="jobDescription"
                        placeholder="Paste the complete job description here. Our AI will analyze it to select the best template and optimize your resume for this specific role..."
                        rows="8"
                    ></textarea>
                    <div class="job-desc-hint">
                        <i class="fa-solid fa-lightbulb"></i>
                        <span>Include the full job posting for best results</span>
                    </div>
                </div>

                <button class="convert-btn" id="startConvertBtn" disabled>
                    <i class="fa-solid fa-rocket"></i>
                    Start ATS Conversion
                </button>
            </div>
        </div>

        <!-- Step 2: Processing Section -->
        <div class="processing-section" id="processingSection" style="display: none;">
            <div class="processing-header">
                <h2>Converting Your Resume</h2>
                <p>Please wait while our AI optimizes your resume for ATS systems</p>
            </div>

            <!-- Activity Log -->
            <div class="activity-log-card">
                <div class="activity-log-header">
                    <i class="fa-solid fa-list-check"></i>
                    <h3>Processing Steps</h3>
                </div>
                <div class="activity-log-content" id="activityLog">
                    <div class="activity-item">
                        <i class="fa-solid fa-circle-notch fa-spin"></i>
                        <span>Starting conversion process...</span>
                    </div>
                </div>
            </div>

            <div class="progress-steps">
                <div class="progress-step" id="step1" data-step="1">
                    <div class="step-icon">
                        <i class="fa-solid fa-file-lines"></i>
                        <div class="step-spinner"></div>
                    </div>
                    <h4>Extracting Resume Data</h4>
                    <p class="step-status">Analyzing your current resume...</p>
                </div>

                <div class="progress-step" id="step2" data-step="2">
                    <div class="step-icon">
                        <i class="fa-solid fa-briefcase"></i>
                        <div class="step-spinner"></div>
                    </div>
                    <h4>Analyzing Job Description</h4>
                    <p class="step-status">Waiting...</p>
                </div>

                <div class="progress-step" id="step3" data-step="3">
                    <div class="step-icon">
                        <i class="fa-solid fa-palette"></i>
                        <div class="step-spinner"></div>
                    </div>
                    <h4>Selecting Best Template</h4>
                    <p class="step-status">Waiting...</p>
                </div>

                <div class="progress-step" id="step4" data-step="4">
                    <div class="step-icon">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <div class="step-spinner"></div>
                    </div>
                    <h4>Optimizing Content</h4>
                    <p class="step-status">Waiting...</p>
                </div>
            </div>
        </div>

        <!-- Step 3: Preview & Save Section -->
        <div class="preview-section" id="previewSection" style="display: none;">
            <div class="preview-header">
                <div>
                    <h2>Your ATS-Optimized Resume</h2>
                    <p>Review your converted resume and save it to your dashboard</p>
                </div>
                <button class="btn-secondary" onclick="startOver()">
                    <i class="fa-solid fa-rotate-left"></i>
                    Convert Another
                </button>
            </div>

            <div class="preview-container">
                <div class="preview-info">
                    <div class="template-selected">
                        <i class="fa-solid fa-circle-check"></i>
                        <div>
                            <strong>Selected Template</strong>
                            <span id="selectedTemplateName">Modern Professional</span>
                        </div>
                    </div>
                    <div class="ats-score">
                        <div class="score-circle">
                            <span class="score-value" id="atsScore">95</span>
                            <span class="score-label">ATS Score</span>
                        </div>
                    </div>
                </div>

                <div class="resume-preview-frame">
                    <iframe id="convertedPreview"></iframe>
                </div>

                <div class="save-section">
                    <div class="save-input-group">
                        <label for="resumeName">Resume Name</label>
                        <input
                            type="text"
                            id="resumeName"
                            placeholder="e.g., Software Engineer Resume - Google"
                            value="ATS Optimized Resume"
                        >
                    </div>
                    <button class="save-btn" id="saveConvertedBtn">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Save to Dashboard
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Missing Information Modal -->
    <div class="modal-overlay" id="missingInfoModal" style="display: none;">
        <div class="missing-info-modal">
            <div class="modal-header">
                <h3><i class="fa-solid fa-circle-exclamation"></i> Additional Information Needed</h3>
                <p>Please provide the following missing details to complete your resume</p>
            </div>
            <div class="modal-body" id="missingInfoFields">
                <!-- Dynamic fields will be inserted here -->
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="skipMissingInfo()">Skip for Now</button>
                <button class="btn-primary" onclick="submitMissingInfo()">Continue</button>
            </div>
        </div>
    </div>

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

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <script>
        const userId = <?php echo $user['id']; ?>;
    </script>
    <script src="js/navigation-fix.js"></script>
    <script src="js/modal-utils.js?v=5"></script>
    <script src="js/ats-converter.js?v=<?php echo time(); ?>"></script>
</body>
</html>
