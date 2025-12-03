<?php
require_once '../config/database.php';

$conn = getDBConnection();
if (!$conn) {
    error_log("Notification generation failed: Database connection error");
    exit();
}

$now = new DateTime();

$intervals = [
    ['days' => 7, 'message' => 'Interview in 7 days'],
    ['days' => 3, 'message' => 'Interview in 3 days'],
    ['days' => 1, 'message' => 'Interview tomorrow'],
    ['hours' => 2, 'message' => 'Interview in 2 hours']
];

foreach ($intervals as $interval) {
    $targetTime = clone $now;

    if (isset($interval['days'])) {
        $targetTime->modify("+{$interval['days']} days");
        $timeWindow = 'DATE(interview_date) = DATE(?)';
    } else {
        $targetTime->modify("+{$interval['hours']} hours");
        $timeWindow = 'interview_date BETWEEN ? AND DATE_ADD(?, INTERVAL 15 MINUTE)';
    }

    $targetTimeStr = $targetTime->format('Y-m-d H:i:s');

    if (isset($interval['days'])) {
        $stmt = $conn->prepare("
            SELECT ja.*, u.full_name, u.email
            FROM job_applications ja
            JOIN users u ON ja.user_id = u.user_id
            WHERE ja.interview_date IS NOT NULL
            AND $timeWindow
            AND ja.status IN ('Interview Scheduled')
            AND NOT EXISTS (
                SELECT 1 FROM notifications n
                WHERE n.application_id = ja.application_id
                AND n.notification_type = 'interview_reminder'
                AND DATE(n.created_at) = CURDATE()
                AND n.message LIKE '%{$interval['message']}%'
            )
        ");
        $stmt->bind_param("s", $targetTimeStr);
    } else {
        $stmt->bind_param("ss", $targetTimeStr, $targetTimeStr);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($app = $result->fetch_assoc()) {
        $interviewDate = new DateTime($app['interview_date']);

        $title = "{$interval['message']}: {$app['job_title']}";
        $message = "You have an interview scheduled with {$app['company_name']} for the position of {$app['job_title']} on " . $interviewDate->format('M j, Y \a\t g:i A');

        if ($app['interview_location']) {
            $message .= " at {$app['interview_location']}";
        }

        $link = "dashboard.php#applications";

        $notifStmt = $conn->prepare("
            INSERT INTO notifications (user_id, application_id, notification_type, title, message, link, scheduled_for)
            VALUES (?, ?, 'interview_reminder', ?, ?, ?, ?)
        ");

        $notifStmt->bind_param(
            "iissss",
            $app['user_id'],
            $app['application_id'],
            $title,
            $message,
            $link,
            $app['interview_date']
        );

        $notifStmt->execute();
        $notifStmt->close();

        error_log("Notification created for user {$app['user_id']}: {$title}");
    }

    $stmt->close();
}

$stmt = $conn->prepare("
    SELECT ja.*, u.full_name, u.email
    FROM job_applications ja
    JOIN users u ON ja.user_id = u.user_id
    WHERE ja.follow_up_date = CURDATE()
    AND ja.status NOT IN ('Rejected', 'Withdrawn', 'Accepted')
    AND NOT EXISTS (
        SELECT 1 FROM notifications n
        WHERE n.application_id = ja.application_id
        AND n.notification_type = 'follow_up_reminder'
        AND DATE(n.created_at) = CURDATE()
    )
");

$stmt->execute();
$result = $stmt->get_result();

while ($app = $result->fetch_assoc()) {
    $title = "Follow-up reminder: {$app['job_title']}";
    $message = "It's time to follow up on your application to {$app['company_name']} for the {$app['job_title']} position.";
    $link = "dashboard.php#applications";

    $notifStmt = $conn->prepare("
        INSERT INTO notifications (user_id, application_id, notification_type, title, message, link)
        VALUES (?, ?, 'follow_up_reminder', ?, ?, ?)
    ");

    $notifStmt->bind_param(
        "iisss",
        $app['user_id'],
        $app['application_id'],
        $title,
        $message,
        $link
    );

    $notifStmt->execute();
    $notifStmt->close();

    error_log("Follow-up notification created for user {$app['user_id']}: {$title}");
}

$stmt->close();

echo "Notification generation completed successfully\n";
?>
