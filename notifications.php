<?php
require_once 'config/session.php';
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();
$userId = $user['id'];

// Get all notifications
$conn = getDBConnection();
$notifications = [];
$unreadCount = 0;

if ($conn) {
    // Get all notifications
    $stmt = $conn->prepare("
        SELECT n.*, ja.job_title, ja.company_name
        FROM notifications n
        LEFT JOIN job_applications ja ON n.application_id = ja.application_id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
        if (!$row['is_read']) {
            $unreadCount++;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=10">
    <link rel="stylesheet" href="css/dashboard.css?v=10">
    <link rel="stylesheet" href="css/notifications.css?v=12">
</head>
<body class="dashboard-body">
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link">ATS Converter</a>
                <button class="notification-icon active" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" style="<?php echo $unreadCount > 0 ? '' : 'display: none;'; ?>"><?php echo $unreadCount; ?></span>
                </button>
                <a href="editor.php" class="nav-cta" style="text-decoration: none; display: inline-block; text-align: center;">Create Resume</a>
            </div>
        </div>
    </nav>

    <div class="notifications-container">
        <header class="notifications-header">
            <div class="notifications-title-section">
                <h1 class="notifications-title">Notifications</h1>
                <p class="notifications-subtitle">Stay updated with your resume activity</p>
            </div>
            <div class="notifications-actions">
                <button class="btn-mark-all-read" id="markAllReadBtn">
                    <i class="fas fa-check-double"></i> Mark all as read
                </button>
                <button class="btn-clear-all" id="clearAllBtn">
                    <i class="fas fa-trash-alt"></i> Clear all
                </button>
            </div>
        </header>

        <div class="notifications-filter">
            <button class="filter-tab active" data-filter="all">All <span class="filter-count"><?php echo count($notifications); ?></span></button>
            <button class="filter-tab" data-filter="unread">Unread <span class="filter-count"><?php echo $unreadCount; ?></span></button>
            <button class="filter-tab" data-filter="interview_reminder">Interviews <span class="filter-count"><?php echo count(array_filter($notifications, fn($n) => $n['notification_type'] === 'interview_reminder')); ?></span></button>
            <button class="filter-tab" data-filter="follow_up_reminder">Follow-ups <span class="filter-count"><?php echo count(array_filter($notifications, fn($n) => $n['notification_type'] === 'follow_up_reminder')); ?></span></button>
            <button class="filter-tab" data-filter="status_update">Updates <span class="filter-count"><?php echo count(array_filter($notifications, fn($n) => $n['notification_type'] === 'status_update')); ?></span></button>
        </div>

        <div class="notifications-list" id="notificationsList">
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification):
                    // Determine icon and color based on type
                    $iconClass = 'fa-bell';
                    $iconBg = '#ede9fe';
                    $iconColor = '#7c3aed';

                    if ($notification['notification_type'] === 'interview_reminder') {
                        $iconClass = 'fa-calendar-check';
                        $iconBg = '#ede9fe';
                        $iconColor = '#7c3aed';
                    } elseif ($notification['notification_type'] === 'follow_up_reminder') {
                        $iconClass = 'fa-clock';
                        $iconBg = '#fef3c7';
                        $iconColor = '#f59e0b';
                    } elseif ($notification['notification_type'] === 'status_update') {
                        $iconClass = 'fa-info-circle';
                        $iconBg = '#dbeafe';
                        $iconColor = '#3b82f6';
                    }

                    // Calculate time ago
                    $createdAt = new DateTime($notification['created_at']);
                    $now = new DateTime();
                    $interval = $now->diff($createdAt);

                    if ($interval->days > 0) {
                        $timeAgo = $interval->days . ' day' . ($interval->days > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->h > 0) {
                        $timeAgo = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
                    } elseif ($interval->i > 0) {
                        $timeAgo = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
                    } else {
                        $timeAgo = 'Just now';
                    }

                    $unreadClass = !$notification['is_read'] ? 'unread' : '';
                ?>
                <div class="notification-card <?php echo $unreadClass; ?>"
                     data-type="<?php echo $notification['notification_type']; ?>"
                     data-notification-id="<?php echo $notification['notification_id']; ?>"
                     data-application-id="<?php echo $notification['application_id'] ?? ''; ?>">
                    <div class="notification-icon-wrapper" style="background: <?php echo $iconBg; ?>;">
                        <i class="fas <?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>;"></i>
                    </div>
                    <div class="notification-content">
                        <h4 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h4>
                        <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <span class="notification-time"><?php echo $timeAgo; ?></span>
                    </div>
                    <div class="notification-actions">
                        <?php if (!$notification['is_read']): ?>
                        <button class="btn-mark-read" onclick="markAsRead(<?php echo $notification['notification_id']; ?>)" title="Mark as read">
                            <i class="fas fa-check"></i>
                        </button>
                        <?php else: ?>
                        <button class="btn-mark-unread" onclick="markAsUnread(<?php echo $notification['notification_id']; ?>)" title="Mark as unread">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 4rem 2rem;">
                    <i class="fas fa-bell-slash" style="font-size: 3rem; color: #cbd5e0; margin-bottom: 1rem; display: block;"></i>
                    <h3 style="color: #4a5568; margin-bottom: 0.5rem;">No notifications</h3>
                    <p style="color: #718096;">You're all caught up! Check back later for updates.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <script src="js/app.js?v=5"></script>
    <script src="js/notifications.js?v=7"></script>
</body>
</html>
