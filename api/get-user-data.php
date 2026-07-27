<?php
require_once __DIR__ . '/../includes/api-bootstrap.php';
require_once '../config/database.php';
require_once '../config/session.php';

requireApiUser();

$user = getCurrentUser();
$userId = $user['id'];

requireApiMethod('GET', 'message');

$type = $_GET['type'] ?? null;

if (!$type || !in_array($type, ['experience', 'education'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid type parameter']);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

try {
    if ($type === 'experience') {
        $stmt = $conn->prepare("SELECT id, user_id, company AS company_name, title AS job_title, location, start_date, end_date, description FROM work_experience WHERE user_id = ? ORDER BY start_date DESC");

        if ($stmt) {
            $stmt->bind_param("i", $userId);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $data = [];

                while ($row = $result->fetch_assoc()) {
                    $data[] = [
                        'job_title' => $row['job_title'] ?? '',
                        'company_name' => $row['company_name'] ?? '',
                        'location' => $row['location'] ?? '',
                        'start_date' => $row['start_date'] ?? '',
                        'end_date' => $row['end_date'] ?? '',
                        'description' => $row['description'] ?? ''
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to fetch experience']);
            }
            $stmt->close();
        } else {
            error_log('Education lookup query failed: ' . $conn->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to load education data']);
        }
    } elseif ($type === 'education') {
        $stmt = $conn->prepare("SELECT id, user_id, institution, degree, field, start_date, end_date FROM education WHERE user_id = ? ORDER BY start_date DESC");

        if ($stmt) {
            $stmt->bind_param("i", $userId);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $data = [];

                while ($row = $result->fetch_assoc()) {
                    $degreeText = trim(($row['degree'] ?? '') . ' ' . ($row['field'] ?? ''));

                    $data[] = [
                        'degree' => $degreeText,
                        'institution' => $row['institution'] ?? '',
                        'location' => '',
                        'start_date' => $row['start_date'] ?? '',
                        'end_date' => $row['end_date'] ?? ''
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'data' => $data
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to fetch education']);
            }
            $stmt->close();
        } else {
            error_log('Experience lookup query failed: ' . $conn->error);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Unable to load experience data']);
        }
    }
} catch (Exception $e) {
    error_log('User data lookup failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load user data']);
}
?>
