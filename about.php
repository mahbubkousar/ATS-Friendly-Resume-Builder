<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - ResumeSync</title>
    <meta name="description" content="Learn about ResumeSync - AI-powered ATS-friendly resume builder">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/about.css">
</head>
<body>
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none; color: inherit;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link">ATS Converter</a>
                <a href="login.php" class="nav-cta" style="text-decoration: none; display: inline-block; text-align: center;">Get Started</a>
            </div>
        </div>
    </nav>

    <main class="about-main">
        <div class="about-container">
            <!-- Hero Section -->
            <section class="about-hero">
                <h1 class="about-title fade-in">About ResumeSync</h1>
                <p class="about-subtitle fade-in-delay">Building the future of ATS-optimized resume creation</p>
            </section>

            <!-- About Section -->
            <section class="about-section fade-in-delay-2">
                <div class="section-card">
                    <div class="section-icon">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <h2 class="section-title">What We Do</h2>
                    <p class="section-text">
                        ResumeSync is an AI-powered platform that helps job seekers overcome the challenges of Applicant Tracking Systems (ATS).
                        We analyze job descriptions and optimize your resume for ATS compatibility, ensuring your resume is formatted correctly,
                        contains relevant keywords, and highlights your strengths in a way that both automated systems and human recruiters can appreciate.
                    </p>
                </div>
            </section>

            <!-- Pricing Section -->
            <section class="pricing-section fade-in-delay-3">
                <h2 class="pricing-title">Simple, Transparent Pricing</h2>
                <p class="pricing-subtitle">Choose the plan that works best for you</p>
                <div class="pricing-grid">
                    <div class="pricing-card">
                        <div class="plan-header">
                            <h3 class="plan-name">Free</h3>
                            <div class="plan-price">
                                <span class="price">$0</span>
                                <span class="period">/month</span>
                            </div>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fa-solid fa-check"></i> 1 Resume</li>
                            <li><i class="fa-solid fa-check"></i> Basic ATS Score Check</li>
                            <li><i class="fa-solid fa-check"></i> Standard Templates</li>
                            <li><i class="fa-solid fa-check"></i> PDF Export</li>
                        </ul>
                        <a href="register.php" class="plan-button">Get Started</a>
                    </div>

                    <div class="pricing-card featured">
                        <div class="featured-badge">Popular</div>
                        <div class="plan-header">
                            <h3 class="plan-name">Pro</h3>
                            <div class="plan-price">
                                <span class="price">$100M</span>
                                <span class="period">/month</span>
                            </div>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fa-solid fa-check"></i> Unlimited Resumes</li>
                            <li><i class="fa-solid fa-check"></i> Advanced ATS Analysis</li>
                            <li><i class="fa-solid fa-check"></i> AI-Powered Suggestions</li>
                            <li><i class="fa-solid fa-check"></i> All Export Formats</li>
                            <li><i class="fa-solid fa-check"></i> Priority Support</li>
                        </ul>
                        <a href="register.php" class="plan-button featured-button">Start Free Trial</a>
                    </div>

                    <div class="pricing-card">
                        <div class="plan-header">
                            <h3 class="plan-name">Enterprise</h3>
                            <div class="plan-price">
                                <span class="price">Custom</span>
                            </div>
                        </div>
                        <ul class="plan-features">
                            <li><i class="fa-solid fa-check"></i> Everything in Pro</li>
                            <li><i class="fa-solid fa-check"></i> Team Collaboration</li>
                            <li><i class="fa-solid fa-check"></i> Custom Branding</li>
                            <li><i class="fa-solid fa-check"></i> API Access</li>
                            <li><i class="fa-solid fa-check"></i> Dedicated Support</li>
                        </ul>
                        <a href="register.php" class="plan-button">Contact Sales</a>
                    </div>
                </div>
            </section>

            <!-- CTA Section -->
            <section class="about-cta fade-in-delay-5">
                <div class="cta-card">
                    <h2>Ready to Build Your ATS-Optimized Resume?</h2>
                    <p>Join thousands of job seekers who have improved their chances of landing interviews</p>
                    <a href="login.php" class="cta-button">
                        <span>Get Started Free</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </section>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/app.js"></script>
</body>
</html>
