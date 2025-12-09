# Authentication System - Complete Guide

## Table of Contents
1. [System Overview](#system-overview)
2. [File Structure](#file-structure)
3. [Database Configuration](#database-configuration)
4. [Session Management](#session-management)
5. [Registration Flow](#registration-flow)
6. [Login Flow](#login-flow)
7. [Protected Routes](#protected-routes)
8. [Code Examples](#code-examples)

---

## System Overview

The authentication system uses **session-based authentication** with:
- Password hashing using PHP's `password_hash()` (bcrypt)
- MySQLi prepared statements for SQL injection prevention
- Session storage for user state
- Cookie-based "Remember Me" feature

### Security Features
- ✅ Bcrypt password hashing (cost factor 10)
- ✅ Prepared statements (no SQL injection)
- ✅ Session hijacking prevention
- ✅ XSS protection via `htmlspecialchars()`
- ✅ Input validation and sanitization

---

## File Structure

### Core Authentication Files

```
config/
├── database.php        # Database connection singleton
└── session.php         # Session helper functions

login.php               # Login page
register.php            # Registration page
logout.php              # Logout handler
api/register.php        # Registration API endpoint
```

---

## Database Configuration

**File**: `config/database.php`

### Code Implementation

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resumesync_db');

function getDBConnection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }

        $conn->set_charset('utf8mb4');  // UTF-8 support
    }

    return $conn;
}
```

### Key Features
- **Singleton Pattern**: Only one connection instance
- **Error Logging**: Errors logged, not displayed
- **UTF-8 Support**: Full Unicode character support
- **Static Variable**: Connection persists across calls

### How It Works
1. First call creates `mysqli` connection
2. Subsequent calls return existing connection
3. Connection stored in static variable
4. Returns `null` on failure (graceful degradation)

---

## Session Management

**File**: `config/session.php`

### Code Implementation

```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /ATS/login.php');
        exit();
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUser() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'fullname' => $_SESSION['user_fullname'] ?? null,
        'email' => $_SESSION['user_email'] ?? null
    ];
}

function setUserSession($userId, $fullname, $email) {
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_fullname'] = $fullname;
    $_SESSION['user_email'] = $email;
}

function destroyUserSession() {
    session_unset();
    session_destroy();
}
```

### Session Variables Stored
| Variable | Type | Purpose |
|----------|------|---------|
| `$_SESSION['user_id']` | Integer | Primary key from users table |
| `$_SESSION['user_fullname']` | String | User's display name |
| `$_SESSION['user_email']` | String | User's email address |

### Helper Functions Explained

#### `isLoggedIn()`
- **Returns**: `true` if user is authenticated
- **Usage**: Check authentication status
- **Logic**: Verifies `user_id` exists and is not empty

#### `requireLogin()`
- **Purpose**: Force authentication on protected pages
- **Action**: Redirects to login if not authenticated
- **Usage**: Call at top of protected PHP files

#### `getCurrentUser()`
- **Returns**: Array with user data
- **Usage**: Get current user information
- **Safe**: Returns null values if not logged in

---

## Registration Flow

### Frontend: `register.php`

**Key Code Sections**:

```php
// At the top of register.php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
```

### Registration Form

```html
<form id="registerForm" action="api/register.php" method="POST">
    <input type="text" name="full_name" required>
    <input type="email" name="email" required>
    <input type="password" name="password" required minlength="8">
    <button type="submit">Sign Up</button>
</form>
```

### Backend: `api/register.php`

```php
<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// 1. Sanitize and validate input
$fullName = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$phone = trim($_POST['phone'] ?? '');

// Validation
if (empty($fullName) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit();
}

// 2. Check if email exists
$conn = getDBConnection();
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit();
}

// 3. Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 4. Insert user
$stmt = $conn->prepare("
    INSERT INTO users (email, password_hash, full_name, phone)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("ssss", $email, $passwordHash, $fullName, $phone);

if ($stmt->execute()) {
    $userId = $stmt->insert_id;

    // 5. Create session
    setUserSession($userId, $fullName, $email);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'redirect' => 'dashboard.php'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
```

### Registration Process Flow

```
1. User fills registration form
   ↓
2. Form submitted to api/register.php
   ↓
3. Server validates input (required fields, email format)
   ↓
4. Check if email already exists (prepared statement)
   ↓
5. Hash password using password_hash() [bcrypt]
   ↓
6. Insert user into database
   ↓
7. Get new user_id (last insert ID)
   ↓
8. Create session with setUserSession()
   ↓
9. Return JSON response
   ↓
10. JavaScript redirects to dashboard
```

---

## Login Flow

### Frontend: `login.php`

```php
<?php
require_once 'config/database.php';
require_once 'config/session.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT user_id, full_name, email, password_hash
            FROM users WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                setUserSession($user['user_id'], $user['full_name'], $user['email']);

                // Remember me
                if (isset($_POST['remember']) && $_POST['remember'] === 'on') {
                    setcookie('remember_email', $email, time() + (86400 * 30), '/');
                }

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
```

### Login Form

```html
<form action="login.php" method="POST">
    <input type="email" name="email" required>
    <input type="password" name="password" required>
    <input type="checkbox" name="remember"> Remember me
    <button type="submit">Sign In</button>
</form>
```

### Login Process Flow

```
1. User enters email and password
   ↓
2. Form submits to login.php (POST)
   ↓
3. Server validates input
   ↓
4. Query database for user by email
   ↓
5. Verify password using password_verify()
   ↓
6. If valid: Create session + optional cookie
   ↓
7. Redirect to dashboard.php
   ↓
8. If invalid: Show error message
```

### Password Verification

```php
// password_verify() compares:
// - Plain text password from form
// - Hashed password from database
// Returns true/false

if (password_verify($password, $user['password_hash'])) {
    // Login successful
}
```

---

## Protected Routes

### Protecting a Page

Add this at the top of any protected PHP file:

```php
<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();  // Redirects to login if not authenticated

// Rest of your page code...
$user = getCurrentUser();
echo "Welcome, " . htmlspecialchars($user['fullname']);
?>
```

### Example: `dashboard.php`

```php
<?php
require_once 'config/database.php';
require_once 'config/session.php';

requireLogin();  // Must be logged in

$user = getCurrentUser();
$userId = $user['id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h1>
    <!-- Dashboard content -->
</body>
</html>
```

---

## Code Examples

### Example 1: Protect API Endpoint

```php
<?php
header('Content-Type: application/json');
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();  // Returns 401 if not logged in

$user = getCurrentUser();
$userId = $user['id'];

// Your API logic here
```

### Example 2: Check Login Status in JavaScript

```javascript
// The session is server-side, but you can check via API
fetch('api/check-auth.php')
    .then(response => response.json())
    .then(data => {
        if (!data.authenticated) {
            window.location.href = 'login.php';
        }
    });
```

### Example 3: Logout

**File**: `logout.php`

```php
<?php
require_once 'config/session.php';

destroyUserSession();

// Optional: Clear remember me cookie
setcookie('remember_email', '', time() - 3600, '/');

header('Location: login.php');
exit();
?>
```

---

## Security Best Practices Used

### 1. Password Hashing
```php
// NEVER store plain text passwords
$hash = password_hash($password, PASSWORD_DEFAULT);
// Uses bcrypt with automatic salt
```

### 2. Prepared Statements
```php
// Prevents SQL injection
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### 3. Output Escaping
```php
// Prevents XSS
echo htmlspecialchars($user['fullname'], ENT_QUOTES, 'UTF-8');
```

### 4. Session Security
```php
// Session hijacking prevention
session_regenerate_id(true);  // Can be added after login
```

---

## Testing the Authentication System

### Test Registration
1. Go to `http://localhost/ATS/register.php`
2. Fill in form with valid data
3. Should redirect to dashboard
4. Check database for new user record
5. Verify password is hashed (not plain text)

### Test Login
1. Go to `http://localhost/ATS/login.php`
2. Use registered credentials
3. Should redirect to dashboard
4. Session should persist across pages

### Test Protection
1. Try accessing `dashboard.php` without logging in
2. Should redirect to `login.php`
3. Log in and try again
4. Should work now

---

## Common Issues & Solutions

### Issue: Session not persisting
**Solution**: Check `session_start()` is called before any output

### Issue: Cannot connect to database
**Solution**: Verify XAMPP MySQL is running, check credentials in `database.php`

### Issue: Password verification fails
**Solution**: Ensure password_hash() and password_verify() use same password

### Issue: Headers already sent
**Solution**: Ensure no output before `header()` calls

---

## Viva Questions & Answers

**Q: How do you prevent SQL injection?**
A: Using prepared statements with parameter binding via MySQLi.

**Q: How are passwords stored?**
A: Hashed using `password_hash()` with bcrypt algorithm (cost factor 10).

**Q: What is session-based authentication?**
A: Server stores user state in `$_SESSION`, client receives session ID via cookie.

**Q: How do you protect routes?**
A: Call `requireLogin()` function which checks session and redirects if not authenticated.

**Q: What happens during login?**
A: Email verified → Password verified → Session created → User redirected to dashboard.
