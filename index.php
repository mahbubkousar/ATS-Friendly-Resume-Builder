<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ResumeSync - ATS Friendly Resume Builder</title>
    <meta name="description" content="Create ATS-optimized resumes that get past automated screening systems and land more interviews.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=8">
</head>
<body class="homepage">
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none; color: inherit;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link">ATS Converter</a>
                <button class="nav-cta" id="navCtaBtn">Start Free</button>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container">
                <h1 class="hero-title fade-in">
                    Build Resumes That Get Past<br>
                    <span class="highlight">Applicant Tracking Systems</span>
                </h1>
                <p class="hero-subtitle fade-in-delay">
                    Create ATS-optimized resumes with clean formatting and live preview
                </p>
                <button class="cta-button fade-in-delay-2" id="getStartedBtn">
                    <span class="button-text">Start Building</span>
                    <span class="button-icon">→</span>
                </button>
            </div>
        </section>

        <section class="features" id="features">
            <div class="container">
                <h2 class="features-main-title">Powerful Features to Land Your Dream Job</h2>
                <p class="features-subtitle">Everything you need to create an ATS-optimized resume that stands out</p>
                <div class="feature-grid">
                    <div class="feature-card" data-feature="1">
                        <div class="feature-number">01</div>
                        <h3>AI-Powered Analysis</h3>
                        <p>Get instant ATS compatibility scores and intelligent suggestions powered by Google Gemini AI to optimize your resume</p>
                    </div>
                    <div class="feature-card" data-feature="2">
                        <div class="feature-number">02</div>
                        <h3>Keyword Optimization</h3>
                        <p>Automatically identify missing keywords from job descriptions and improve your match score with targeted suggestions</p>
                    </div>
                    <div class="feature-card" data-feature="3">
                        <div class="feature-number">03</div>
                        <h3>ATS-Friendly Templates</h3>
                        <p>Clean, professional templates designed for perfect parsing by Applicant Tracking Systems with semantic HTML structure</p>
                    </div>
                    <div class="feature-card" data-feature="4">
                        <div class="feature-number">04</div>
                        <h3>Live Preview</h3>
                        <p>See your resume update in real-time as you type. What you see is exactly what recruiters will receive</p>
                    </div>
                    <div class="feature-card" data-feature="5">
                        <div class="feature-number">05</div>
                        <h3>Content Improvement Tips</h3>
                        <p>Get AI-driven recommendations on using stronger action verbs, quantifying achievements, and enhancing impact</p>
                    </div>
                    <div class="feature-card" data-feature="6">
                        <div class="feature-number">06</div>
                        <h3>Multiple Resume Management</h3>
                        <p>Create, save, and manage multiple versions of your resume tailored for different job applications</p>
                    </div>
                    <div class="feature-card" data-feature="7">
                        <div class="feature-number">07</div>
                        <h3>Job Description Matching</h3>
                        <p>Upload job descriptions as PDF or paste text to analyze your resume compatibility and get tailored suggestions</p>
                    </div>
                    <div class="feature-card" data-feature="8">
                        <div class="feature-number">08</div>
                        <h3>PDF & HTML Export</h3>
                        <p>Download your polished resume as a clean, universally compatible PDF or HTML file ready for submission</p>
                    </div>
                    <div class="feature-card" data-feature="9">
                        <div class="feature-number">09</div>
                        <h3>Interactive Builder</h3>
                        <p>Guided step-by-step forms make it easy to input your information without any design skills required</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>
