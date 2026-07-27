<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/auth-rate-limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$retryAfter = consumeRegistrationRateLimit();
if ($retryAfter > 0) {
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    echo json_encode(['success' => false, 'message' => 'Too many registration attempts. Please try again later.']);
    exit();
}

$maximumRequestBytes = 200000;
$contentLength = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
if ($contentLength > $maximumRequestBytes) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Request body is too large']);
    exit();
}
$rawInput = file_get_contents('php://input', false, null, 0, $maximumRequestBytes + 1);
if ($rawInput === false || strlen($rawInput) > $maximumRequestBytes) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'Request body is too large']);
    exit();
}
$input = json_decode($rawInput, true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Request body must contain valid JSON']);
    exit();
}

$required = ['fullname', 'email', 'password', 'phone', 'address', 'city', 'state', 'zipcode', 'country'];
foreach ($required as $field) {
    if (!isset($input[$field]) || !is_string($input[$field]) || trim($input[$field]) === '') {
        echo json_encode(['success' => false, 'message' => "Field '{$field}' is required"]);
        exit();
    }
}

if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

if (strlen($input['password']) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $input['email']);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    $stmt->close();
    exit();
}
$stmt->close();

$hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash, phone, date_of_birth, address_line1, city, state, zip_code, country, professional_title, professional_summary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$dob = !empty($input['dob']) ? $input['dob'] : null;
$professionalTitle = $input['professional-title'] ?? null;
$bio = $input['bio'] ?? null;

$stmt->bind_param(
    "ssssssssssss",
    $input['fullname'],
    $input['email'],
    $hashedPassword,
    $input['phone'],
    $dob,
    $input['address'],
    $input['city'],
    $input['state'],
    $input['zipcode'],
    $input['country'],
    $professionalTitle,
    $bio
);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
    $stmt->close();
    exit();
}

$userId = $stmt->insert_id;
$stmt->close();

if (!empty($input['education']) && is_array($input['education'])) {
    $stmt = $conn->prepare("INSERT INTO user_education (user_id, institution_name, degree, field_of_study, start_date, end_date, gpa) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($input['education'] as $edu) {
        if (!empty($edu['institution']) || !empty($edu['degree'])) {
            $institution = $edu['institution'] ?? null;
            $degree = $edu['degree'] ?? null;
            $field = $edu['field'] ?? null;
            $startDate = !empty($edu['startDate']) ? $edu['startDate'] . '-01' : null;
            $endDate = !empty($edu['endDate']) ? $edu['endDate'] . '-01' : null;
            $gpa = $edu['gpa'] ?? null;

            $stmt->bind_param(
                "issssss",
                $userId,
                $institution,
                $degree,
                $field,
                $startDate,
                $endDate,
                $gpa
            );
            $stmt->execute();
        }
    }
    $stmt->close();
}

if (!empty($input['experience']) && is_array($input['experience'])) {
    $stmt = $conn->prepare("INSERT INTO user_experience (user_id, company_name, job_title, location, start_date, end_date, current_position, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($input['experience'] as $exp) {
        if (!empty($exp['company']) || !empty($exp['title'])) {
            $company = $exp['company'] ?? null;
            $title = $exp['title'] ?? null;
            $location = $exp['location'] ?? null;
            $startDate = !empty($exp['startDate']) ? $exp['startDate'] . '-01' : null;
            $endDate = !empty($exp['endDate']) ? $exp['endDate'] . '-01' : null;
            $isCurrent = isset($exp['current']) && $exp['current'] ? 1 : 0;
            $description = $exp['description'] ?? null;

            $stmt->bind_param(
                "isssssss",
                $userId,
                $company,
                $title,
                $location,
                $startDate,
                $endDate,
                $isCurrent,
                $description
            );
            $stmt->execute();
        }
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT user_id, full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

setUserSession($user['user_id'], $user['full_name'], $user['email']);

echo json_encode([
    'success' => true,
    'message' => 'Registration successful!',
    'redirect' => 'dashboard.php'
]);
?>
