<?php
require_once '../config/session.php';
require_once '../config/database.php';

requireLogin();

$user = getCurrentUser();
$userId = $user['id'];
$conn = getDBConnection();

$stmt = $conn->prepare("SELECT * FROM job_applications WHERE user_id = ? ORDER BY application_date DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$applications = [];
while ($row = $result->fetch_assoc()) {
    $applications[] = $row;
}
$stmt->close();

if (empty($applications)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No applications found']);
    exit();
}

header('Content-Type: text/html; charset=utf-8');

$totalApps = count($applications);
$statuses = array_count_values(array_column($applications, 'status'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applications Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background: white;
            color: #333;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #7c3aed;
            padding-bottom: 15px;
        }
        h1 {
            color: #7c3aed;
            font-size: 28px;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
        }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .summary h2 {
            color: #7c3aed;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .stat-item {
            padding: 10px;
            background: white;
            border-left: 4px solid #7c3aed;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .applications {
            margin-top: 30px;
        }
        .applications h2 {
            color: #7c3aed;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .app-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .app-header {
            margin-bottom: 12px;
        }
        .app-title {
            font-size: 18px;
            font-weight: bold;
            color: #1a202c;
            margin-bottom: 5px;
        }
        .app-company {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 8px;
        }
        .app-meta {
            margin-bottom: 15px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-applied { background: #dbeafe; color: #1e40af; }
        .status-in-review { background: #fef3c7; color: #92400e; }
        .status-interview-scheduled { background: #fef3c7; color: #92400e; }
        .status-interview-completed { background: #e0e7ff; color: #3730a3; }
        .status-offer-received { background: #d1fae5; color: #065f46; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-withdrawn { background: #f3f4f6; color: #6b7280; }
        .app-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-top: 15px;
        }
        .detail-item {
            font-size: 14px;
        }
        .detail-label {
            color: #6b7280;
            font-weight: 600;
            margin-right: 5px;
        }
        .detail-value {
            color: #1f2937;
        }
        .priority-high { color: #dc2626; font-weight: bold; }
        .priority-medium { color: #f59e0b; }
        .priority-low { color: #10b981; }
        .notes {
            margin-top: 15px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 6px;
            font-size: 13px;
            line-height: 1.6;
            color: #4b5563;
        }
        @media print {
            body { padding: 20px; }
            .app-card { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Job Applications Report</h1>
        <div class="subtitle"><?php echo htmlspecialchars($user['fullname'] ?? 'User'); ?> • Generated on <?php echo date('F j, Y'); ?></div>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <div class="stats">
            <div class="stat-item">
                <div class="stat-label">Total Applications</div>
                <div class="stat-value"><?php echo $totalApps; ?></div>
            </div>
            <?php foreach ($statuses as $status => $count): ?>
            <div class="stat-item">
                <div class="stat-label"><?php echo htmlspecialchars($status); ?></div>
                <div class="stat-value"><?php echo $count; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="applications">
        <h2>Application Details</h2>

        <?php foreach ($applications as $app):
            $statusClass = strtolower(str_replace(' ', '-', $app['status']));
        ?>
        <div class="app-card">
            <div class="app-header">
                <div class="app-title"><?php echo htmlspecialchars($app['job_title']); ?></div>
                <div class="app-company"><?php echo htmlspecialchars($app['company_name']); ?></div>
                <div class="app-meta">
                    <span class="status-badge status-<?php echo $statusClass; ?>">
                        <?php echo htmlspecialchars($app['status']); ?>
                    </span>
                </div>
            </div>

            <div class="app-details">
                <div class="detail-item">
                    <span class="detail-label">Location:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($app['job_location'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Job Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($app['job_type'] ?? 'N/A'); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Applied Date:</span>
                    <span class="detail-value"><?php echo date('M j, Y', strtotime($app['application_date'])); ?></span>
                </div>
                <?php if (!empty($app['salary_range'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Salary Range:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($app['salary_range']); ?></span>
                </div>
                <?php endif; ?>
                <div class="detail-item">
                    <span class="detail-label">Priority:</span>
                    <span class="detail-value priority-<?php echo strtolower($app['priority'] ?? 'medium'); ?>">
                        <?php echo htmlspecialchars($app['priority'] ?? 'Medium'); ?>
                    </span>
                </div>
                <?php if (!empty($app['contact_person'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Contact:</span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($app['contact_person']); ?>
                        <?php if (!empty($app['contact_email'])): ?>
                            (<?php echo htmlspecialchars($app['contact_email']); ?>)
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($app['interview_date'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Interview Date:</span>
                    <span class="detail-value"><?php echo date('M j, Y g:i A', strtotime($app['interview_date'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($app['interview_location'])): ?>
                <div class="detail-item">
                    <span class="detail-label">Interview Location:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($app['interview_location']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($app['notes'])): ?>
            <div class="notes">
                <strong>Notes:</strong><br>
                <?php echo nl2br(htmlspecialchars($app['notes'])); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
