<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'includes/auth-rate-limit.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) && is_string($_POST['email'])
        ? trim($_POST['email'])
        : '';
    $password = isset($_POST['password']) && is_string($_POST['password'])
        ? $_POST['password']
        : '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $retryAfter = consumeLoginRateLimit($email);
        if ($retryAfter > 0) {
            http_response_code(429);
            header('Retry-After: ' . $retryAfter);
            $error = 'Too many sign-in attempts. Please wait and try again.';
        }

        $conn = getDBConnection();
        if ($retryAfter === 0 && $conn) {
            $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password_hash'])) {
                    clearSuccessfulLoginLimit($email);
                    setUserSession($user['user_id'], $user['full_name'], $user['email']);

                    if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                        setcookie('remember_email', $email, [
                            'expires' => time() + (86400 * 30),
                            'path' => '/',
                            'secure' => isHttpsRequest(),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    } elseif (isset($_COOKIE['remember_email'])) {
                        setcookie('remember_email', '', [
                            'expires' => time() - 42000,
                            'path' => '/',
                            'secure' => isHttpsRequest(),
                            'httponly' => true,
                            'samesite' => 'Lax'
                        ]);
                    }

                    header('Location: dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid email or password.';
                }
            } else {
                $error = 'Invalid email or password.';
            }

            $stmt->close();
        } elseif ($retryAfter === 0) {
            $error = 'Database connection failed. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ResumeSync</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/auth.css">
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
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to continue to ResumeSync</p>
            </div>

            <?php if ($error): ?>
                <div style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" action="login.php" method="POST">
                <div class="form-group">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" id="email" name="email" class="auth-input" placeholder="Enter your email" value="<?php echo htmlspecialchars($_COOKIE['remember_email'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="auth-input" placeholder="Enter your password" required>
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" class="checkbox-input" <?php echo isset($_COOKIE['remember_email']) ? 'checked' : ''; ?>>
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-link">Forgot password?</a>
                </div>

                <button type="submit" class="auth-button">Sign In</button>

                <div class="auth-divider">
                    <span>or</span>
                </div>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php" class="auth-link">Sign up</a></p>
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
</body>
</html>
