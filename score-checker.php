<?php
require_once 'config/session.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATS Score Checker - ResumeSync</title>
    <meta name="description" content="Check your resume's ATS compatibility score for free. Get instant feedback and recommendations.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=17">
    <link rel="stylesheet" href="css/score-checker.css?v=4">
</head>
<body>
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none; color: inherit;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="score-checker.php" class="nav-link active">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link">ATS Converter</a>
                <?php
                $currentUser = getCurrentUser();
                if ($currentUser):
                ?>
                    <div class="nav-user-menu">
                        <button class="nav-user-trigger" id="navUserTrigger">
                            <div class="nav-user-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="nav-user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="nav-dropdown" id="navDropdown">
                            <a href="dashboard.php" class="dropdown-item">
                                <i class="fas fa-th-large"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="editor.php" class="dropdown-item">
                                <i class="fas fa-plus"></i>
                                <span>New Resume</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="api/logout.php" class="dropdown-item">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="nav-cta" style="text-decoration: none; display: inline-block; text-align: center;">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="checker-main">
        <div class="checker-container">
            <!-- Header Section -->
            <header class="checker-header">
                <div class="checker-header-content">
                    <h1 class="checker-title fade-in">Free ATS Score Checker</h1>
                    <p class="checker-subtitle fade-in-delay">Upload your resume and get instant ATS compatibility analysis powered by AI</p>
                </div>
                <button class="info-button" id="howWeCalculateBtn" title="How we calculate scores">
                    <i class="fas fa-info-circle"></i> How We Calculate
                </button>
            </header>

            <!-- Upload Section -->
            <section class="upload-section fade-in-delay-2" id="uploadSection">
                <div class="upload-card">
                    <div class="upload-toggle">
                        <button class="toggle-option active" id="fileUploadToggle">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Upload File</span>
                        </button>
                        <button class="toggle-option" id="pasteTextToggle">
                            <i class="fa-solid fa-paste"></i>
                            <span>Paste Text</span>
                        </button>
                    </div>

                    <!-- File Upload Option -->
                    <div class="upload-option" id="fileUploadOption">
                        <div class="file-drop-zone">
                            <input type="file" id="resumeFile" accept=".pdf,.doc,.docx" hidden>
                            <label for="resumeFile" class="drop-zone-label">
                                <i class="fa-solid fa-file-arrow-up"></i>
                                <h3>Drop your resume here</h3>
                                <p>or click to browse</p>
                                <span class="file-types">Supports PDF, DOC, DOCX (Max 5MB)</span>
                            </label>
                            <div class="file-selected" id="fileSelected">
                                <div class="file-icon">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <div class="file-details">
                                    <p class="file-name" id="selectedFileName"></p>
                                    <p class="file-size" id="selectedFileSize"></p>
                                </div>
                                <button class="remove-file" id="removeFileBtn">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Paste Text Option -->
                    <div class="upload-option hidden" id="pasteTextOption">
                        <textarea class="paste-textarea" id="resumeText" placeholder="Paste your resume text here..."></textarea>
                        <div class="character-count">
                            <span id="charCount">0</span> characters
                        </div>
                    </div>

                </div>
            </section>

            <!-- Job Description Section -->
            <section class="job-section fade-in-delay-3">
                <div class="job-card">
                    <div class="job-header">
                        <div>
                            <h2 class="job-title">Job Description </h2>
                            <p class="job-subtitle">Get more accurate keyword matching by adding the target job description</p>
                        </div>
                    </div>

                    <div class="job-input-toggle">
                        <button class="job-toggle-btn active" id="jobTextToggle">
                            <i class="fa-solid fa-keyboard"></i>
                            <span>Paste Text</span>
                        </button>
                        <button class="job-toggle-btn" id="jobFileToggle">
                            <i class="fa-solid fa-file-pdf"></i>
                            <span>Upload PDF</span>
                        </button>
                    </div>

                    <!-- Job Text Input -->
                    <div class="job-input-option" id="jobTextInput">
                        <textarea class="job-textarea" id="jobDescText" placeholder="Paste the job description here to get targeted keyword analysis..."></textarea>
                        <div class="character-count">
                            <span id="jobCharCount">0</span> characters
                        </div>
                    </div>

                    <!-- Job File Upload -->
                    <div class="job-input-option hidden" id="jobFileInput">
                        <div class="job-file-zone">
                            <input type="file" id="jobDescFile" accept=".pdf" hidden>
                            <label for="jobDescFile" class="job-file-label">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <h4>Upload Job Description PDF</h4>
                                <p>Click to browse or drag and drop</p>
                            </label>
                            <div class="job-file-selected" id="jobFileSelected">
                                <div class="file-icon">
                                    <i class="fa-solid fa-file-pdf"></i>
                                </div>
                                <div class="file-details">
                                    <p class="file-name" id="jobFileName"></p>
                                    <p class="file-size" id="jobFileSize"></p>
                                </div>
                                <button class="remove-file" id="removeJobFileBtn">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="analyze-button" id="analyzeBtn">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Analyze Resume</span>
                </button>
            </section>

            <!-- Results Section (Hidden Initially) -->
            <section class="results-section hidden" id="resultsSection">
                <!-- Hero Score Section -->
                <div class="results-hero">
                    <div class="score-container">
                        <div class="score-circle-wrapper">
                            <svg viewBox="0 0 200 200" class="score-svg">
                                <circle cx="100" cy="100" r="85" class="score-track"></circle>
                                <circle cx="100" cy="100" r="85" class="score-progress" id="scoreProgress"></circle>
                            </svg>
                            <div class="score-content">
                                <div class="score-number" id="overallScore">0</div>
                                <div class="score-label">ATS Score</div>
                            </div>
                        </div>
                        <div class="score-details">
                            <h2 class="score-status-title" id="scoreStatus">Analyzing...</h2>
                            <p class="score-description" id="scoreMessage">Your resume is being evaluated</p>
                            <div class="score-metrics">
                                <div class="metric-badge">
                                    <i class="fa-solid fa-check-circle"></i>
                                    <span>ATS Compatible</span>
                                </div>
                                <div class="metric-badge">
                                    <i class="fa-solid fa-file-lines"></i>
                                    <span>Structure Analyzed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Breakdown Section -->
                <div class="results-content">
                    <div class="breakdown-section">
                        <h3 class="section-heading">
                            <i class="fa-solid fa-chart-bar"></i>
                            Performance Breakdown
                        </h3>
                        <div class="metrics-grid">
                            <div class="metric-card" data-category="formatting">
                                <div class="metric-header">
                                    <i class="fa-solid fa-align-left"></i>
                                    <span class="metric-name">Formatting</span>
                                    <button class="metric-expand-btn" data-target="formattingDetails">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="metric-score" id="formattingScore">--</div>
                                <div class="metric-bar">
                                    <div class="metric-fill" id="formattingProgress"></div>
                                </div>
                                <div class="metric-details hidden" id="formattingDetails">
                                    <p class="details-empty">Analysis details will appear here</p>
                                </div>
                            </div>
                            <div class="metric-card" data-category="keywords">
                                <div class="metric-header">
                                    <i class="fa-solid fa-key"></i>
                                    <span class="metric-name">Keywords</span>
                                    <button class="metric-expand-btn" data-target="keywordsDetails">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="metric-score" id="keywordsScore">--</div>
                                <div class="metric-bar">
                                    <div class="metric-fill" id="keywordsProgress"></div>
                                </div>
                                <div class="metric-details hidden" id="keywordsDetails">
                                    <p class="details-empty">Analysis details will appear here</p>
                                </div>
                            </div>
                            <div class="metric-card" data-category="sections">
                                <div class="metric-header">
                                    <i class="fa-solid fa-list-check"></i>
                                    <span class="metric-name">Sections</span>
                                    <button class="metric-expand-btn" data-target="sectionsDetails">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="metric-score" id="sectionsScore">--</div>
                                <div class="metric-bar">
                                    <div class="metric-fill" id="sectionsProgress"></div>
                                </div>
                                <div class="metric-details hidden" id="sectionsDetails">
                                    <p class="details-empty">Analysis details will appear here</p>
                                </div>
                            </div>
                            <div class="metric-card" data-category="contact">
                                <div class="metric-header">
                                    <i class="fa-solid fa-address-card"></i>
                                    <span class="metric-name">Contact Info</span>
                                    <button class="metric-expand-btn" data-target="contactDetails">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="metric-score" id="contactScore">--</div>
                                <div class="metric-bar">
                                    <div class="metric-fill" id="contactProgress"></div>
                                </div>
                                <div class="metric-details hidden" id="contactDetails">
                                    <p class="details-empty">Analysis details will appear here</p>
                                </div>
                            </div>
                            <div class="metric-card" data-category="parsability">
                                <div class="metric-header">
                                    <i class="fa-solid fa-file-import"></i>
                                    <span class="metric-name">Parsability</span>
                                    <button class="metric-expand-btn" data-target="parsabilityDetails">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="metric-score" id="parsabilityScore">--</div>
                                <div class="metric-bar">
                                    <div class="metric-fill" id="parsabilityProgress"></div>
                                </div>
                                <div class="metric-details hidden" id="parsabilityDetails">
                                    <p class="details-empty">Analysis details will appear here</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Insights Section -->
                    <div class="insights-section">
                        <h3 class="section-heading">
                            <i class="fa-solid fa-lightbulb"></i>
                            Key Insights & Recommendations
                        </h3>
                        <div class="insights-grid">
                            <div class="insight-card positive">
                                <div class="insight-icon">
                                    <i class="fa-solid fa-circle-check"></i>
                                </div>
                                <div class="insight-content">
                                    <h4>What's Working</h4>
                                    <ul>
                                        <li>Clean, readable formatting</li>
                                        <li>Standard section headers used</li>
                                        <li>Contact information is clear</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="insight-card warning">
                                <div class="insight-icon">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </div>
                                <div class="insight-content">
                                    <h4>Areas to Improve</h4>
                                    <ul>
                                        <li>Add more relevant keywords from job description</li>
                                        <li>Use stronger action verbs in bullet points</li>
                                        <li>Quantify achievements with numbers</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="insight-card info">
                                <div class="insight-icon">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <div class="insight-content">
                                    <h4>Pro Tips</h4>
                                    <ul>
                                        <li>Include industry-specific certifications</li>
                                        <li>Keep your resume to 1-2 pages maximum</li>
                                        <li>Use PDF format for final submission</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Section -->
                <div class="cta-banner">
                    <div class="cta-content">
                        <h3>Ready to Build an ATS-Optimized Resume?</h3>
                        <p>Use our AI-powered builder to create a resume that gets past ATS and impresses recruiters</p>
                    </div>
                    <a href="login.php" class="cta-button-large">
                        <span>Start Building</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <button class="check-another-btn" id="checkAnotherBtn">
                    <i class="fa-solid fa-rotate-right"></i>
                    Check Another Resume
                </button>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <!-- Analysis Progress Modal -->
    <div class="modal-overlay active" id="analysisProgressModal" style="display: none;">
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

    <!-- How We Calculate Modal -->
    <div class="modal-overlay" id="calculationModal">
        <div class="calculation-modal">
            <div class="modal-header">
                <h2><i class="fas fa-calculator"></i> How We Calculate Your ATS Score</h2>
                <button class="modal-close" id="closeCalcModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <p class="modal-intro">Our ATS Score Checker evaluates your resume across 6 critical categories used by real Applicant Tracking Systems. The total score is out of <strong>100 points</strong>.</p>

                <div class="scoring-categories">
                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon formatting">
                                <i class="fas fa-align-left"></i>
                            </div>
                            <div>
                                <h3>Formatting <span class="points">25 points</span></h3>
                                <p class="category-desc">Clean, ATS-readable document structure</p>
                            </div>
                        </div>
                        <ul class="criteria-list">
                            <li><i class="fas fa-check"></i> Single column layout (no tables or multi-column sections)</li>
                            <li><i class="fas fa-check"></i> Standard fonts (Arial, Georgia, Calibri, Tahoma)</li>
                            <li><i class="fas fa-check"></i> No special characters, accents, or symbols</li>
                            <li><i class="fas fa-check"></i> Contact info in body text (not headers/footers)</li>
                            <li><i class="fas fa-check"></i> ALL CAPS for section headers only</li>
                            <li><i class="fas fa-check"></i> No underlined text</li>
                        </ul>
                    </div>

                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon keywords">
                                <i class="fas fa-key"></i>
                            </div>
                            <div>
                                <h3>Keywords <span class="points">25 points</span></h3>
                                <p class="category-desc">Relevant terms matching job requirements</p>
                            </div>
                        </div>
                        <ul class="criteria-list">
                            <li><i class="fas fa-check"></i> Keywords from job description included</li>
                            <li><i class="fas fa-check"></i> Both acronyms AND full terms (e.g., "CPA" and "Certified Public Accountant")</li>
                            <li><i class="fas fa-check"></i> Keywords used in context, not just listed</li>
                            <li><i class="fas fa-check"></i> Industry-specific terminology present</li>
                            <li><i class="fas fa-check"></i> Balance of specific vs general keywords</li>
                        </ul>
                    </div>

                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon structure">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <h3>Content Structure <span class="points">20 points</span></h3>
                                <p class="category-desc">Proper sections and organization</p>
                            </div>
                        </div>
                        <ul class="criteria-list">
                            <li><i class="fas fa-check"></i> Standard section headings (SUMMARY, EXPERIENCE, EDUCATION, SKILLS)</li>
                            <li><i class="fas fa-check"></i> Reverse chronological order</li>
                            <li><i class="fas fa-check"></i> Achievement-oriented bullet points</li>
                            <li><i class="fas fa-check"></i> Professional summary included</li>
                            <li><i class="fas fa-check"></i> Complete work history with no gaps</li>
                        </ul>
                    </div>

                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon contact">
                                <i class="fas fa-address-card"></i>
                            </div>
                            <div>
                                <h3>Contact Information <span class="points">10 points</span></h3>
                                <p class="category-desc">Properly formatted contact details</p>
                            </div>
                        </div>
                        <ul class="criteria-list">
                            <li><i class="fas fa-check"></i> Contact info in document body (not header/footer)</li>
                            <li><i class="fas fa-check"></i> Name on top line only (no credentials after name)</li>
                            <li><i class="fas fa-check"></i> No special punctuation in name</li>
                            <li><i class="fas fa-check"></i> Email and phone properly formatted</li>
                        </ul>
                    </div>

                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon experience">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <div>
                                <h3>Experience Format <span class="points">10 points</span></h3>
                                <p class="category-desc">Standardized date and job formatting</p>
                            </div>
                        </div>
                        <ul class="criteria-list">
                            <li><i class="fas fa-check"></i> Dates include months (MM/YYYY format)</li>
                            <li><i class="fas fa-check"></i> Dates positioned on the right after text</li>
                            <li><i class="fas fa-check"></i> Job title, company, location, and dates all present</li>
                            <li><i class="fas fa-check"></i> Consistent presentation order across all positions</li>
                        </ul>
                    </div>

                    <div class="category-item">
                        <div class="category-header">
                            <div class="category-icon technical">
                                <i class="fas fa-file-import"></i>
                            </div>
                            <div>
                                <h3>Technical Quality <span class="points">10 points</span></h3>
                                <p class="category-desc">Error-free and parser-friendly</p>
                            </div>
                        </div>
                        <ul class="criteria-list">
                            <li><i class="fas fa-check"></i> No spelling or grammatical errors</li>
                            <li><i class="fas fa-check"></i> Proper capitalization and punctuation</li>
                            <li><i class="fas fa-check"></i> No complex formatting (condensed/expanded text)</li>
                            <li><i class="fas fa-check"></i> Compatible with .doc format conversion</li>
                        </ul>
                    </div>
                </div>

                <div class="scoring-info">
                    <h3><i class="fas fa-lightbulb"></i> Scoring Interpretation</h3>
                    <div class="score-ranges">
                        <div class="score-range excellent">
                            <div class="range-label">90-100</div>
                            <div class="range-desc">
                                <strong>Excellent</strong> - Your resume is highly optimized for ATS systems
                            </div>
                        </div>
                        <div class="score-range good">
                            <div class="range-label">75-89</div>
                            <div class="range-desc">
                                <strong>Good</strong> - Strong ATS compatibility with minor improvements possible
                            </div>
                        </div>
                        <div class="score-range fair">
                            <div class="range-label">60-74</div>
                            <div class="range-desc">
                                <strong>Fair</strong> - Passes basic ATS requirements but needs optimization
                            </div>
                        </div>
                        <div class="score-range poor">
                            <div class="range-label">Below 60</div>
                            <div class="range-desc">
                                <strong>Needs Work</strong> - May be rejected by ATS systems
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ai-powered">
                    <i class="fas fa-wand-magic-sparkles"></i>
                    <p>Powered by <strong>Google Gemini AI</strong> for intelligent resume analysis and keyword extraction</p>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
    <script src="js/score-checker.js?v=9"></script>
    <script>
        // User menu dropdown toggle
        const navUserTrigger = document.getElementById('navUserTrigger');
        const navDropdown = document.getElementById('navDropdown');

        if (navUserTrigger && navDropdown) {
            navUserTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                navDropdown.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!navUserTrigger.contains(e.target) && !navDropdown.contains(e.target)) {
                    navDropdown.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
