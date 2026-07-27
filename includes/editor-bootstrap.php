<?php

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';

/**
 * Loads the authenticated user and resume data shared by every editor variant.
 *
 * @return array{
 *   resumeId: ?int,
 *   templateName: ?string,
 *   templateDisplayName: string,
 *   resumeData: ?array,
 *   personalDetails: array
 * }
 */
function loadEditorContext(?string $fixedTemplateName = null): array
{
    requireLogin();

    $user = getCurrentUser();
    $userId = (int) $user['id'];
    $connection = getDBConnection();
    if (!$connection) {
        throw new RuntimeException('Database service is unavailable.');
    }

    $resumeId = null;
    if (isset($_GET['id']) && $_GET['id'] !== '') {
        $validatedResumeId = filter_var($_GET['id'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($validatedResumeId === false) {
            http_response_code(400);
            exit('Invalid resume ID.');
        }
        $resumeId = (int) $validatedResumeId;
    }

    $templateName = $fixedTemplateName;
    if ($templateName === null && isset($_GET['template']) && $_GET['template'] !== '') {
        $requestedTemplate = is_string($_GET['template']) ? $_GET['template'] : '';
        if (!preg_match('/^[a-z0-9-]{1,100}$/', $requestedTemplate)) {
            http_response_code(400);
            exit('Invalid template.');
        }
        $templateName = $requestedTemplate;
    }

    $resumeData = null;
    if ($resumeId !== null) {
        $statement = $connection->prepare(
            'SELECT * FROM resumes WHERE resume_id = ? AND user_id = ?'
        );
        $statement->bind_param('ii', $resumeId, $userId);
        $statement->execute();
        $resumeData = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();

        if ($resumeData === null) {
            http_response_code(404);
            exit('Resume not found.');
        }

        if ($fixedTemplateName === null) {
            $templateName = $resumeData['template_name'];
        }
    }

    if ($resumeData !== null) {
        $personalDetails = json_decode($resumeData['personal_details'] ?? '', true);
        $personalDetails = is_array($personalDetails) ? $personalDetails : [];
    } else {
        $personalDetails = loadEditorUserDetails($connection, $userId);
    }

    return [
        'resumeId' => $resumeId,
        'templateName' => $templateName,
        'templateDisplayName' => loadTemplateDisplayName($connection, $templateName),
        'resumeData' => $resumeData,
        'personalDetails' => $personalDetails,
    ];
}

function loadEditorUserDetails(mysqli $connection, int $userId): array
{
    $statement = $connection->prepare(
        'SELECT full_name, email, phone, city, state, professional_title
         FROM users
         WHERE user_id = ?'
    );
    $statement->bind_param('i', $userId);
    $statement->execute();
    $userData = $statement->get_result()->fetch_assoc() ?: [];
    $statement->close();

    $city = trim((string) ($userData['city'] ?? ''));
    $state = trim((string) ($userData['state'] ?? ''));

    return [
        'fullName' => $userData['full_name'] ?? '',
        'email' => $userData['email'] ?? '',
        'phone' => $userData['phone'] ?? '',
        'location' => implode(', ', array_filter([$city, $state])),
        'professionalTitle' => $userData['professional_title'] ?? '',
        'linkedin' => '',
    ];
}

function loadTemplateDisplayName(mysqli $connection, ?string $templateName): string
{
    if ($templateName === null || $templateName === '') {
        return 'No Template Selected';
    }

    $statement = $connection->prepare(
        'SELECT template_display_name FROM templates WHERE template_name = ? LIMIT 1'
    );
    $statement->bind_param('s', $templateName);
    $statement->execute();
    $template = $statement->get_result()->fetch_assoc();
    $statement->close();

    return $template['template_display_name'] ?? ucfirst(str_replace('-', ' ', $templateName));
}
