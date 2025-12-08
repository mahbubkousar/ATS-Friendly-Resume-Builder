<?php
require_once 'config/session.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth.css">
    <link rel="stylesheet" href="css/register.css">
</head>
<body class="auth-body">
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none; color: inherit;">ResumeSync</a>
            <div class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <!-- ATS Converter temporarily disabled -->
                <!--<a href="ats-converter.php" class="nav-link">ATS Converter</a>-->
            </div>
        </div>
    </nav>

    <div class="auth-container">
        <div class="auth-card multi-step-card">
            <div class="progress-container">
                <div class="progress-steps">
                    <div class="progress-step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Account</div>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Personal</div>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Education</div>
                    </div>
                    <div class="progress-line"></div>
                    <div class="progress-step" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Experience</div>
                    </div>
                </div>
            </div>

            <div class="auth-header">
                <h1 class="auth-title" id="stepTitle">Create Your Account</h1>
                <p class="auth-subtitle" id="stepSubtitle">Let's start with your basic information</p>
            </div>

            <div id="errorMessage" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; display: none;"></div>

            <form class="auth-form multi-step-form" id="registrationForm">
                <div class="form-step active" data-step="1">
                    <div class="form-group">
                        <label for="fullname" class="form-label">Full Name *</label>
                        <input type="text" id="fullname" name="fullname" class="auth-input" placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" class="auth-input" placeholder="Enter your email" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" id="password" name="password" class="auth-input" placeholder="Create a password (min. 8 characters)" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm-password" class="form-label">Confirm Password *</label>
                        <input type="password" id="confirm-password" name="confirm-password" class="auth-input" placeholder="Confirm your password" required>
                    </div>

                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" class="checkbox-input" id="terms" required>
                            <span>I agree to the <a href="#" class="inline-link">Terms of Service</a> and <a href="#" class="inline-link">Privacy Policy</a></span>
                        </label>
                    </div>
                </div>

                <div class="form-step" data-step="2">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="auth-input" placeholder="+1 (555) 123-4567" required>
                        </div>
                        <div class="form-group">
                            <label for="dob" class="form-label">Date of Birth</label>
                            <input type="date" id="dob" name="dob" class="auth-input">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address" class="form-label">Address *</label>
                        <input type="text" id="address" name="address" class="auth-input" placeholder="Street address" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="city" class="form-label">City *</label>
                            <input type="text" id="city" name="city" class="auth-input" placeholder="City" required>
                        </div>
                        <div class="form-group">
                            <label for="state" class="form-label">State/Province *</label>
                            <input type="text" id="state" name="state" class="auth-input" placeholder="State" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="zipcode" class="form-label">Zip Code *</label>
                            <input type="text" id="zipcode" name="zipcode" class="auth-input" placeholder="12345" required>
                        </div>
                        <div class="form-group">
                            <label for="country" class="form-label">Country *</label>
                            <input type="text" id="country" name="country" class="auth-input" placeholder="Country" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="professional-title" class="form-label">Professional Title</label>
                        <input type="text" id="professional-title" name="professional-title" class="auth-input" placeholder="e.g., Software Engineer, Product Manager">
                    </div>

                    <div class="form-group">
                        <label for="bio" class="form-label">Professional Summary</label>
                        <textarea id="bio" name="bio" class="auth-input auth-textarea" rows="3" placeholder="Brief description of your professional background"></textarea>
                    </div>
                </div>

                <div class="form-step" data-step="3">
                    <div class="optional-badge">
                        <i class="fas fa-info-circle"></i>
                        <span>This step is optional. You can skip or complete it later in your profile.</span>
                    </div>

                    <div id="educationList">
                        <div class="education-entry" data-index="0">
                            <div class="entry-header">
                                <h4>Education #1</h4>
                                <button type="button" class="btn-remove-entry" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Institution Name</label>
                                <input type="text" name="education[0][institution]" class="auth-input" placeholder="University/College name">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Degree</label>
                                    <input type="text" name="education[0][degree]" class="auth-input" placeholder="e.g., Bachelor of Science">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Field of Study</label>
                                    <input type="text" name="education[0][field]" class="auth-input" placeholder="e.g., Computer Science">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Start Date</label>
                                    <input type="month" name="education[0][startDate]" class="auth-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Date</label>
                                    <input type="month" name="education[0][endDate]" class="auth-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">GPA (Optional)</label>
                                <input type="text" name="education[0][gpa]" class="auth-input" placeholder="e.g., 3.8/4.0">
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-entry" id="addEducation">
                        <i class="fas fa-plus"></i> Add Another Education
                    </button>
                </div>

                <div class="form-step" data-step="4">
                    <div class="optional-badge">
                        <i class="fas fa-info-circle"></i>
                        <span>This step is optional. You can skip or complete it later in your profile.</span>
                    </div>

                    <div id="experienceList">
                        <div class="experience-entry" data-index="0">
                            <div class="entry-header">
                                <h4>Experience #1</h4>
                                <button type="button" class="btn-remove-entry" style="display: none;">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Company Name</label>
                                <input type="text" name="experience[0][company]" class="auth-input" placeholder="Company name">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Job Title</label>
                                    <input type="text" name="experience[0][title]" class="auth-input" placeholder="e.g., Software Engineer">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="experience[0][location]" class="auth-input" placeholder="City, State">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Start Date</label>
                                    <input type="month" name="experience[0][startDate]" class="auth-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">End Date</label>
                                    <input type="month" name="experience[0][endDate]" class="auth-input">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" class="checkbox-input" name="experience[0][current]">
                                    <span>I currently work here</span>
                                </label>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Description</label>
                                <textarea name="experience[0][description]" class="auth-input auth-textarea" rows="3" placeholder="Describe your responsibilities and achievements"></textarea>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn-add-entry" id="addExperience">
                        <i class="fas fa-plus"></i> Add Another Experience
                    </button>
                </div>

                <div class="form-navigation">
                    <button type="button" class="btn-nav btn-prev" id="prevBtn" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Previous
                    </button>
                    <button type="button" class="btn-nav btn-skip" id="skipBtn" style="display: none;">
                        Skip <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="button" class="btn-nav btn-next" id="nextBtn">
                        Next <i class="fas fa-arrow-right"></i>
                    </button>
                    <button type="submit" class="btn-nav btn-submit" id="submitBtn" style="display: none;">
                        Complete Registration <i class="fas fa-check"></i>
                    </button>
                </div>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php" class="auth-link">Sign in</a></p>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer visible">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/app.js"></script>
    <script src="js/register.js?v=2"></script>
</body>
</html>
