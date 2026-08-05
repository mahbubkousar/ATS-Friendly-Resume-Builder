<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../includes/auth-rate-limit.php';
require_once '../includes/request-validation.php';

requireApiMethod('POST', 'message');

$retryAfter = consumeRegistrationRateLimit();
if ($retryAfter > 0) {
    http_response_code(429);
    header('Retry-After: ' . $retryAfter);
    echo json_encode(['success' => false, 'message' => 'Too many registration attempts. Please try again later.']);
    exit();
}

try {
    $input = readValidatedJsonBody(200000);
    $input['fullname'] = requiredStringField($input, 'fullname', 'Full name', 255);
    $input['email'] = strtolower(requiredStringField($input, 'email', 'Email', 255));
    $input['phone'] = requiredStringField($input, 'phone', 'Phone', 20);
    $input['address'] = requiredStringField($input, 'address', 'Address', 255);
    $input['city'] = requiredStringField($input, 'city', 'City', 100);
    $input['state'] = requiredStringField($input, 'state', 'State', 100);
    $input['zipcode'] = requiredStringField($input, 'zipcode', 'ZIP code', 20);
    $input['country'] = requiredStringField($input, 'country', 'Country', 100);

    if (!isset($input['password']) || !is_string($input['password'])) {
        throw new RequestValidationException('Password is required.');
    }
    if (strlen($input['password']) < 8 || strlen($input['password']) > 128) {
        throw new RequestValidationException('Password must be between 8 and 128 characters.');
    }

    $input['dob'] = dateField($input, 'dob', 'Date of birth');
    $input['professional-title'] = optionalStringField(
        $input,
        'professional-title',
        'Professional title',
        255
    );
    $input['bio'] = optionalStringField($input, 'bio', 'Professional summary', 10000);

    foreach (['education', 'experience'] as $collection) {
        if (isset($input[$collection])
            && (!is_array($input[$collection]) || count($input[$collection]) > 20)) {
            throw new RequestValidationException(
                ucfirst($collection) . ' must contain no more than 20 entries.'
            );
        }
    }

    foreach ($input['education'] ?? [] as $index => $education) {
        if (!is_array($education)) {
            throw new RequestValidationException('Invalid education entry.');
        }
        $input['education'][$index] = [
            'institution' => optionalStringField($education, 'institution', 'Institution', 255),
            'degree' => optionalStringField($education, 'degree', 'Degree', 255),
            'field' => optionalStringField($education, 'field', 'Field of study', 255),
            'startDate' => monthField($education, 'startDate', 'Education start date'),
            'endDate' => monthField($education, 'endDate', 'Education end date'),
            'gpa' => optionalStringField($education, 'gpa', 'GPA', 10),
        ];
    }

    foreach ($input['experience'] ?? [] as $index => $experience) {
        if (!is_array($experience)) {
            throw new RequestValidationException('Invalid experience entry.');
        }
        $input['experience'][$index] = [
            'company' => optionalStringField($experience, 'company', 'Company', 255),
            'title' => optionalStringField($experience, 'title', 'Job title', 255),
            'location' => optionalStringField($experience, 'location', 'Location', 255),
            'startDate' => monthField($experience, 'startDate', 'Experience start date'),
            'endDate' => monthField($experience, 'endDate', 'Experience end date'),
            'current' => booleanField($experience, 'current', 'Current position'),
            'description' => optionalStringField($experience, 'description', 'Description', 10000),
        ];
    }
} catch (RequestValidationException $e) {
    sendRequestValidationError($e);
}

if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
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

$conn->begin_transaction();

try {

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
    throw new RuntimeException('Unable to create user.');
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
            if (!$stmt->execute()) {
                throw new RuntimeException('Unable to save education entry.');
            }
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
            if (!$stmt->execute()) {
                throw new RuntimeException('Unable to save experience entry.');
            }
        }
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT user_id, full_name, email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    throw new RuntimeException('Unable to load newly created user.');
}

$conn->commit();
} catch (Throwable $exception) {
    $conn->rollback();
    error_log('Registration transaction failed: ' . $exception->getMessage());
    sendApiError('Registration failed. Please try again.', 500);
}

setUserSession($user['user_id'], $user['full_name'], $user['email']);

echo json_encode([
    'success' => true,
    'message' => 'Registration successful!',
    'redirect' => 'dashboard.php'
]);
?>
