<?php
require_once 'config/session.php';
require_once 'config/database.php';
requireLogin();

$user = getCurrentUser();
$userName = htmlspecialchars($user['fullname'] ?? 'User');
$userId = $user['id'];

// Get user profile data
$conn = getDBConnection();
$userProfile = [];
$education = [];
$experience = [];
$resumes = [];
$applications = [];

if ($conn) {
    // Get user profile
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $userProfile = $result->fetch_assoc();
        } else {
            // If profile not found, use session data as fallback
            $userProfile = [
                'full_name' => $user['fullname'] ?? '',
                'email' => $user['email'] ?? '',
                'phone' => '',
                'date_of_birth' => null,
                'address_line1' => '',
                'city' => '',
                'state' => '',
                'zip_code' => '',
                'country' => '',
                'professional_title' => '',
                'professional_summary' => ''
            ];
        }
        $stmt->close();
    }

    // Get resumes
    $stmt = $conn->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY updated_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $resumes[] = $row;
        }
        $stmt->close();
    }

    // Get education
    $stmt = $conn->prepare("SELECT * FROM user_education WHERE user_id = ? ORDER BY start_date DESC");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $education[] = $row;
        }
        $stmt->close();
    }

    // Get experience
    $stmt = $conn->prepare("SELECT * FROM user_experience WHERE user_id = ? ORDER BY start_date DESC");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $experience[] = $row;
        }
        $stmt->close();
    }

    // Get job applications
    $stmt = $conn->prepare("SELECT * FROM job_applications WHERE user_id = ? ORDER BY application_date DESC");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
        $stmt->close();
    }

    // Get upcoming interview notifications (within next 7 days)
    $upcomingInterviews = [];
    $stmt = $conn->prepare("
        SELECT
            application_id,
            company_name,
            job_title,
            interview_date,
            interview_location,
            status
        FROM job_applications
        WHERE user_id = ?
        AND interview_date IS NOT NULL
        AND interview_date >= NOW()
        AND interview_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
        ORDER BY interview_date ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $upcomingInterviews[] = $row;

            // Auto-generate notification if it doesn't exist (check both read and unread)
            $checkNotifStmt = $conn->prepare("
                SELECT notification_id FROM notifications
                WHERE user_id = ?
                AND application_id = ?
                AND notification_type = 'interview_reminder'
                AND DATE(scheduled_for) = DATE(?)
            ");
            $checkNotifStmt->bind_param("iis", $userId, $row['application_id'], $row['interview_date']);
            $checkNotifStmt->execute();
            $notifResult = $checkNotifStmt->get_result();

            if ($notifResult->num_rows === 0) {
                // Create notification
                $interviewDateTime = new DateTime($row['interview_date']);
                $now = new DateTime();
                $interval = $now->diff($interviewDateTime);
                $daysUntil = $interval->days;

                $timeText = '';
                if ($daysUntil == 0) {
                    $timeText = 'today';
                } elseif ($daysUntil == 1) {
                    $timeText = 'tomorrow';
                } else {
                    $timeText = "in {$daysUntil} days";
                }

                $title = "Interview Scheduled {$timeText}";
                $message = "You have an interview with {$row['company_name']} for the position of {$row['job_title']} on " . $interviewDateTime->format('M j, Y \a\t g:i A');

                if (!empty($row['interview_location'])) {
                    $message .= " at {$row['interview_location']}";
                }

                $link = "dashboard.php#applications";

                $insertNotifStmt = $conn->prepare("
                    INSERT INTO notifications (user_id, application_id, notification_type, title, message, link, scheduled_for)
                    VALUES (?, ?, 'interview_reminder', ?, ?, ?, ?)
                ");
                $insertNotifStmt->bind_param(
                    "iissss",
                    $userId,
                    $row['application_id'],
                    $title,
                    $message,
                    $link,
                    $row['interview_date']
                );
                $insertNotifStmt->execute();
                $insertNotifStmt->close();
            }
            $checkNotifStmt->close();
        }
        $stmt->close();
    }

    // Get unread notification count
    $notificationCount = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $notificationCount = $row['count'];
        }
        $stmt->close();
    }

    // Get recent unread notifications for modal (limit 5)
    $recentNotifications = [];
    $stmt = $conn->prepare("
        SELECT n.*, ja.job_title, ja.company_name
        FROM notifications n
        LEFT JOIN job_applications ja ON n.application_id = ja.application_id
        WHERE n.user_id = ? AND n.is_read = 0
        ORDER BY n.created_at DESC
        LIMIT 5
    ");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $recentNotifications[] = $row;
        }
        $stmt->close();
    }
}

$resumeCount = count($resumes);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ResumeSync</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css?v=16">
    <link rel="stylesheet" href="css/dashboard.css?v=25">
</head>
<body class="dashboard-body">
    <nav class="floating-nav">
        <div class="nav-content">
            <a href="index.php" class="nav-logo" style="text-decoration: none;">ResumeSync</a>
            <div class="nav-links">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <a href="score-checker.php" class="nav-link">ATS Checker</a>
                <a href="ats-converter.php" class="nav-link">ATS Converter</a>
                <button class="notification-icon" id="notificationBtn" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notificationBadge" style="<?php echo $notificationCount > 0 ? '' : 'display: none;'; ?>"><?php echo $notificationCount; ?></span>
                </button>
                <a href="editor.php" class="nav-cta" style="text-decoration: none; display: inline-block; text-align: center;">Create Resume</a>
            </div>
        </div>
    </nav>

    <!-- Notification Modal -->
    <div class="notification-modal" id="notificationModal">
        <div class="notification-header">
            <h3>Notifications</h3>
            <button class="close-modal" id="closeNotificationModal">&times;</button>
        </div>
        <div class="notification-body" id="notificationBody">
            <?php if (count($recentNotifications) > 0): ?>
                <?php foreach ($recentNotifications as $notification):
                    // Determine icon based on notification type
                    $iconClass = 'fa-bell';
                    $iconColor = '#7c3aed';
                    if ($notification['notification_type'] === 'interview_reminder') {
                        $iconClass = 'fa-calendar-check';
                        $iconColor = '#7c3aed';
                    } elseif ($notification['notification_type'] === 'follow_up_reminder') {
                        $iconClass = 'fa-clock';
                        $iconColor = '#f59e0b';
                    } elseif ($notification['notification_type'] === 'status_update') {
                        $iconClass = 'fa-info-circle';
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
                ?>
                <div class="notification-item" data-notification-id="<?php echo $notification['notification_id']; ?>" data-application-id="<?php echo $notification['application_id'] ?? ''; ?>">
                    <div class="notification-icon-wrapper">
                        <i class="fas <?php echo $iconClass; ?>" style="color: <?php echo $iconColor; ?>;"></i>
                    </div>
                    <div class="notification-content">
                        <h4 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h4>
                        <p class="notification-text"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <p class="notification-time">
                            <i class="fas fa-clock"></i> <?php echo $timeAgo; ?>
                        </p>
                    </div>
                    <?php if (!empty($notification['application_id'])): ?>
                    <div class="notification-actions">
                        <button class="view-application-btn" onclick="markAsReadAndView(<?php echo $notification['notification_id']; ?>, <?php echo $notification['application_id']; ?>)">
                            <i class="fas fa-eye"></i> View
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 2rem; color: #718096;">
                    <i class="fas fa-bell-slash" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    <p>No new notifications</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="notification-footer">
            <a href="notifications.php" class="view-all-notifications">View All Notifications</a>
        </div>
    </div>
    <div class="notification-overlay" id="notificationOverlay"></div>

    <div class="dashboard-container">
        <aside class="sidebar">
            <h2 class="sidebar-title">Dashboard Menu</h2>
            <nav class="sidebar-nav">
                <a href="#overview" class="sidebar-link active">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3 4C3 3.44772 3.44772 3 4 3H7C7.55228 3 8 3.44772 8 4V7C8 7.55228 7.55228 8 7 8H4C3.44772 8 3 7.55228 3 7V4Z" fill="currentColor"/>
                        <path d="M3 13C3 12.4477 3.44772 12 4 12H7C7.55228 12 8 12.4477 8 13V16C8 16.5523 7.55228 17 7 17H4C3.44772 17 3 16.5523 3 16V13Z" fill="currentColor"/>
                        <path d="M13 3C12.4477 3 12 3.44772 12 4V7C12 7.55228 12.4477 8 13 8H16C16.5523 8 17 7.55228 17 7V4C17 3.44772 16.5523 3 16 3H13Z" fill="currentColor"/>
                        <path d="M12 13C12 12.4477 12.4477 12 13 12H16C16.5523 12 17 12.4477 17 13V16C17 16.5523 16.5523 17 16 17H13C12.4477 17 12 16.5523 12 16V13Z" fill="currentColor"/>
                    </svg>
                    <span>Overview</span>
                </a>
                <a href="#resumes" class="sidebar-link">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M4 4C4 2.89543 4.89543 2 6 2H14C15.1046 2 16 2.89543 16 4V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V4Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M7 7H13M7 10H13M7 13H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>My Resumes</span>
                </a>
                <a href="#templates" class="sidebar-link">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3 5C3 3.89543 3.89543 3 5 3H15C16.1046 3 17 3.89543 17 5V15C17 16.1046 16.1046 17 15 17H5C3.89543 17 3 16.1046 3 15V5Z" stroke="currentColor" stroke-width="2"/>
                        <path d="M7 7H13M7 10H13M7 13H11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Templates</span>
                </a>
                <a href="#applications" class="sidebar-link">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M9 2C8.44772 2 8 2.44772 8 3C8 3.55228 8.44772 4 9 4H11C11.5523 4 12 3.55228 12 3C12 2.44772 11.5523 2 11 2H9Z" fill="currentColor"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4 5C4 3.89543 4.89543 3 6 3C6 4.65685 7.34315 6 9 6H11C12.6569 6 14 4.65685 14 3C15.1046 3 16 3.89543 16 5V16C16 17.1046 15.1046 18 14 18H6C4.89543 18 4 17.1046 4 16V5ZM7 9C7 8.44772 7.44772 8 8 8H12C12.5523 8 13 8.44772 13 9C13 9.55228 12.5523 10 12 10H8C7.44772 10 7 9.55228 7 9ZM8 12C7.44772 12 7 12.4477 7 13C7 13.5523 7.44772 14 8 14H12C12.5523 14 13 13.5523 13 13C13 12.4477 12.5523 12 12 12H8Z" fill="currentColor"/>
                    </svg>
                    <span>Applications</span>
                </a>
                <a href="#analytics" class="sidebar-link">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M3 17V13M10 17V7M17 17V11" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Analytics</span>
                </a>
                <a href="#profile" class="sidebar-link">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <circle cx="10" cy="7" r="4" stroke="currentColor" stroke-width="2"/>
                        <path d="M3 18C3 14.134 6.13401 11 10 11C13.866 11 17 14.134 17 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Profile</span>
                </a>
                <a href="#settings" class="sidebar-link">
                    <svg class="sidebar-icon" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 6V10L13 13M19 10C19 14.9706 14.9706 19 10 19C5.02944 19 1 14.9706 1 10C1 5.02944 5.02944 1 10 1C14.9706 1 19 5.02944 19 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <main class="dashboard-main">
            <!-- Overview Tab -->
            <div id="overview" class="tab-content active">
                <header class="dashboard-header">
                    <h1 class="dashboard-greeting">Welcome back, <?php echo $userName; ?>!</h1>
                </header>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $resumeCount; ?></div>
                        <div class="stat-label">Resumes Created</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">0%</div>
                        <div class="stat-label">Avg ATS Score</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?php echo count($applications); ?></div>
                        <div class="stat-label">Applications Sent</div>
                    </div>
                </div>

                <section class="recent-resumes">
                    <h2 class="section-heading">Recent Resumes</h2>
                    <div class="resume-grid">
                        <?php if (empty($resumes)): ?>
                            <div style="text-align: center; padding: 3rem 2rem; color: #718096; grid-column: 1 / -1;">
                                <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #cbd5e0;"></i>
                                <h3 style="color: #4a5568; margin-bottom: 0.5rem;">No resumes yet</h3>
                                <p>Create your first resume to get started!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach (array_slice($resumes, 0, 3) as $resume): ?>
                                <div class="resume-card">
                                    <div class="resume-header">
                                        <div class="resume-icon">
                                            <i class="fas fa-file-alt"></i>
                                        </div>
                                        <span class="resume-status status-<?php echo strtolower($resume['status']); ?>"><?php echo ucfirst($resume['status']); ?></span>
                                    </div>
                                    <h3 class="resume-title"><?php echo htmlspecialchars($resume['resume_title']); ?></h3>
                                    <p class="resume-meta">
                                        <span><i class="far fa-calendar"></i> Updated <?php echo date('M j, Y', strtotime($resume['updated_at'])); ?></span>
                                        <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($resume['template_name']); ?></span>
                                    </p>
                                    <div class="resume-actions">
                                        <a href="editor-<?php echo $resume['template_name'] ?? 'classic'; ?>.php?id=<?php echo $resume['resume_id']; ?>" class="btn-edit" style="text-decoration: none;">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <button class="btn-download btn-download-resume" data-resume-id="<?php echo $resume['resume_id']; ?>">
                                            <i class="fas fa-download"></i> Download
                                        </button>
                                        <button class="btn-delete btn-delete-resume" data-resume-id="<?php echo $resume['resume_id']; ?>" data-resume-title="<?php echo htmlspecialchars($resume['resume_title']); ?>">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <a href="editor.php" class="btn-create-resume" style="text-decoration: none; display: inline-block;">
                        + Create New Resume
                    </a>
                </section>
            </div>

            <!-- My Resumes Tab -->
            <div id="resumes" class="tab-content">
                <header class="dashboard-header">
                    <h1 class="dashboard-greeting">My Resumes</h1>
                    <p class="section-description">Manage your resume collection</p>
                </header>
                <div class="resume-grid" style="margin-bottom: 2rem;">
                    <?php if (empty($resumes)): ?>
                        <div style="text-align: center; padding: 4rem 2rem; color: #718096; grid-column: 1 / -1;">
                            <i class="fas fa-file-alt" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #cbd5e0;"></i>
                            <h3 style="color: #4a5568; margin-bottom: 0.5rem;">No resumes created yet</h3>
                            <p>Start building your ATS-optimized resume</p>
                            <a href="editor.php" style="display: inline-block; margin-top: 1.5rem; background: #7c3aed; color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: 500;">Create Resume</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($resumes as $resume): ?>
                            <div class="resume-card">
                                <div class="resume-header">
                                    <div class="resume-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <span class="resume-status status-<?php echo strtolower($resume['status']); ?>"><?php echo ucfirst($resume['status']); ?></span>
                                </div>
                                <h3 class="resume-title"><?php echo htmlspecialchars($resume['resume_title']); ?></h3>
                                <p class="resume-meta">
                                    <span><i class="far fa-calendar"></i> Updated <?php echo date('M j, Y', strtotime($resume['updated_at'])); ?></span>
                                    <span><i class="fas fa-layer-group"></i> <?php echo htmlspecialchars($resume['template_name']); ?></span>
                                </p>

                                <div class="resume-actions">
                                    <a href="editor-<?php echo $resume['template_name'] ?? 'classic'; ?>.php?id=<?php echo $resume['resume_id']; ?>" class="btn-edit" style="text-decoration: none;">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button class="btn-download btn-download-resume" data-resume-id="<?php echo $resume['resume_id']; ?>">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                    <button class="btn-share btn-share-resume" data-resume-id="<?php echo $resume['resume_id']; ?>">
                                        <i class="fas fa-share-alt"></i> Share
                                    </button>
                                    <button class="btn-delete btn-delete-resume" data-resume-id="<?php echo $resume['resume_id']; ?>" data-resume-title="<?php echo htmlspecialchars($resume['resume_title']); ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="editor.php" class="btn-create-resume" style="text-decoration: none; display: inline-block;">
                    + Create New Resume
                </a>
            </div>

            <!-- Templates Tab -->
            <div id="templates" class="tab-content">
                <header class="dashboard-header">
                    <h1 class="dashboard-greeting">Templates</h1>
                    <p class="section-description">Choose from professional ATS-friendly templates</p>
                </header>
                <div class="templates-grid">
                    <div class="template-card" data-template="modern">
                        <div class="template-badge featured">Recommended</div>
                        <div class="template-preview">
                            <iframe src="templates/modern.html?v=3" style="width: 100%; height: 100%; border: none; pointer-events: none; transform: scale(0.85); transform-origin: top center;"></iframe>
                        </div>
                        <div class="template-info">
                            <div class="template-header">
                                <h3 class="template-name">Simple</h3>
                                <div class="template-tags">
                                    <span class="template-tag">ATS-Friendly</span>
                                    <span class="template-tag">Clean</span>
                                </div>
                            </div>
                            <p class="template-description">Clean, minimalist design with modern typography. Perfect for all industries and roles.</p>
                            <div class="template-features">
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Easy to Read</span>
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Modern Style</span>
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Universal</span>
                            </div>
                            <a href="editor-modern.php" class="btn-use-template" style="text-decoration: none;">
                                <i class="fas fa-arrow-right"></i> Use This Template
                            </a>
                        </div>
                    </div>
                    <div class="template-card" data-template="professional">
                        <div class="template-preview">
                            <iframe src="templates/professional.html?v=3" style="width: 100%; height: 100%; border: none; pointer-events: none; transform: scale(0.85); transform-origin: top center;"></iframe>
                        </div>
                        <div class="template-info">
                            <div class="template-header">
                                <h3 class="template-name">Professional</h3>
                                <div class="template-tags">
                                    <span class="template-tag">Finance</span>
                                    <span class="template-tag">Corporate</span>
                                </div>
                            </div>
                            <p class="template-description">Sophisticated serif design with boxed header. Ideal for finance, consulting, and formal corporate roles.</p>
                            <div class="template-features">
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Sophisticated</span>
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Premium Look</span>
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Formal</span>
                            </div>
                            <a href="editor-professional.php" class="btn-use-template" style="text-decoration: none;">
                                <i class="fas fa-arrow-right"></i> Use This Template
                            </a>
                        </div>
                    </div>
                    <div class="template-card" data-template="academic-standard">
                        <div class="template-badge academic">Academic</div>
                        <div class="template-preview">
                            <iframe src="templates/academic-standard.html?v=3" style="width: 100%; height: 100%; border: none; pointer-events: none; transform: scale(0.85); transform-origin: top center;"></iframe>
                        </div>
                        <div class="template-info">
                            <div class="template-header">
                                <h3 class="template-name">Academic</h3>
                                <div class="template-tags">
                                    <span class="template-tag">PhD</span>
                                    <span class="template-tag">Faculty</span>
                                </div>
                            </div>
                            <p class="template-description">Traditional academic CV for PhD candidates, postdocs, and tenure-track positions. Comprehensive publication and grant sections.</p>
                            <div class="template-features">
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Publications</span>
                                <span class="template-feature"><i class="fas fa-check-circle"></i> Grants & Funding</span>
                                <span class="template-feature"><i class="fas fa-check-circle"></i> References Included</span>
                            </div>
                            <a href="editor-academic-standard.php" class="btn-use-template" style="text-decoration: none;">
                                <i class="fas fa-arrow-right"></i> Use This Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications Tab -->
            <div id="applications" class="tab-content">
                <header class="dashboard-header">
                    <div>
                        <h1 class="dashboard-greeting">Job Applications</h1>
                        <p class="section-description">Track your job application status</p>
                    </div>
                    <button class="btn-primary" id="exportApplicationsBtn" style="background: var(--accent-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-file-pdf"></i> Export to PDF
                    </button>
                </header>

                <!-- Application Stats -->
                <div class="application-stats">
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(124, 58, 237, 0.1); color: var(--accent-color);">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo count($applications); ?></div>
                            <div class="stat-label">Total Applications</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo count(array_filter($applications, function($a) { return in_array($a['status'], ['Applied', 'In Review']); })); ?></div>
                            <div class="stat-label">In Progress</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo count(array_filter($applications, function($a) { return in_array($a['status'], ['Interview Scheduled', 'Interview Completed']); })); ?></div>
                            <div class="stat-label">Interviews</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value"><?php echo count(array_filter($applications, function($a) { return in_array($a['status'], ['Offer Received', 'Accepted']); })); ?></div>
                            <div class="stat-label">Offers</div>
                        </div>
                    </div>
                </div>

                <!-- Add Application Button -->
                <div style="margin-bottom: 2rem;">
                    <button class="btn-create-resume" id="addApplicationBtn">
                        <i class="fas fa-plus"></i> Add New Application
                    </button>
                </div>

                <!-- Applications List -->
                <div class="applications-container">
                    <?php if (empty($applications)): ?>
                        <div style="text-align: center; padding: 4rem 2rem; color: #718096;">
                            <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #cbd5e0;"></i>
                            <h3 style="color: #4a5568; margin-bottom: 0.5rem;">No applications tracked yet</h3>
                            <p>Start tracking your job applications to stay organized</p>
                        </div>
                    <?php else: ?>
                        <div class="applications-grid">
                            <?php foreach ($applications as $app): ?>
                                <div class="application-card" data-application-id="<?php echo $app['application_id']; ?>">
                                    <div class="application-header">
                                        <div class="application-company">
                                            <h3 class="application-job-title"><?php echo htmlspecialchars($app['job_title']); ?></h3>
                                            <p class="application-company-name">
                                                <i class="fas fa-building"></i> <?php echo htmlspecialchars($app['company_name']); ?>
                                            </p>
                                        </div>
                                        <div class="application-priority priority-<?php echo strtolower($app['priority']); ?>">
                                            <?php echo $app['priority']; ?>
                                        </div>
                                    </div>

                                    <div class="application-meta">
                                        <?php if ($app['job_location']): ?>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($app['job_location']); ?></span>
                                        <?php endif; ?>
                                        <span><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($app['job_type']); ?></span>
                                        <span><i class="far fa-calendar"></i> Applied <?php echo date('M j, Y', strtotime($app['application_date'])); ?></span>
                                        <?php if ($app['resume_id']):
                                            $usedResume = array_filter($resumes, fn($r) => $r['resume_id'] == $app['resume_id']);
                                            $usedResume = reset($usedResume);
                                            if ($usedResume):
                                        ?>
                                            <span style="color: #7c3aed;"><i class="fas fa-file-alt"></i> <?php echo htmlspecialchars($usedResume['resume_title']); ?></span>
                                        <?php endif; endif; ?>
                                    </div>

                                    <?php if ($app['salary_range']): ?>
                                        <div class="application-salary">
                                            <i class="fas fa-dollar-sign"></i> <?php echo htmlspecialchars($app['salary_range']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="application-status-section">
                                        <label class="status-label">Status:</label>
                                        <select class="application-status-select" data-application-id="<?php echo $app['application_id']; ?>">
                                            <option value="Applied" <?php echo $app['status'] === 'Applied' ? 'selected' : ''; ?>>Applied</option>
                                            <option value="In Review" <?php echo $app['status'] === 'In Review' ? 'selected' : ''; ?>>In Review</option>
                                            <option value="Interview Scheduled" <?php echo $app['status'] === 'Interview Scheduled' ? 'selected' : ''; ?>>Interview Scheduled</option>
                                            <option value="Interview Completed" <?php echo $app['status'] === 'Interview Completed' ? 'selected' : ''; ?>>Interview Completed</option>
                                            <option value="Offer Received" <?php echo $app['status'] === 'Offer Received' ? 'selected' : ''; ?>>Offer Received</option>
                                            <option value="Accepted" <?php echo $app['status'] === 'Accepted' ? 'selected' : ''; ?>>Accepted</option>
                                            <option value="Rejected" <?php echo $app['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                            <option value="Withdrawn" <?php echo $app['status'] === 'Withdrawn' ? 'selected' : ''; ?>>Withdrawn</option>
                                        </select>
                                    </div>

                                    <?php if ($app['notes']): ?>
                                        <div class="application-notes">
                                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars(substr($app['notes'], 0, 100)); ?><?php echo strlen($app['notes']) > 100 ? '...' : ''; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="application-actions">
                                        <button class="btn-edit btn-edit-application" data-application-id="<?php echo $app['application_id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn-secondary btn-view-timeline" data-application-id="<?php echo $app['application_id']; ?>">
                                            <i class="fas fa-stream"></i> Timeline
                                        </button>
                                        <?php if ($app['application_url']): ?>
                                            <a href="<?php echo htmlspecialchars($app['application_url']); ?>" target="_blank" class="btn-secondary" style="text-decoration: none;">
                                                <i class="fas fa-external-link-alt"></i> Posting
                                            </a>
                                        <?php endif; ?>
                                        <button class="btn-secondary btn-delete-application" data-application-id="<?php echo $app['application_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Analytics Tab -->
            <div id="analytics" class="tab-content">
                <header class="dashboard-header">
                    <h1 class="dashboard-greeting">Analytics</h1>
                    <p class="section-description">Track your resume performance</p>
                </header>
                <div style="text-align: center; padding: 4rem 2rem; color: #718096;">
                    <i class="fas fa-chart-line" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: #cbd5e0;"></i>
                    <h3 style="color: #4a5568; margin-bottom: 0.5rem;">No analytics data yet</h3>
                    <p>Create and share resumes to start tracking performance</p>
                </div>
            </div>

            <!-- Profile Tab -->
            <div id="profile" class="tab-content">
                <header class="dashboard-header">
                    <h1 class="dashboard-greeting">Your Profile</h1>
                    <p class="section-description">Manage your personal information</p>
                </header>

                <div class="profile-sections">
                    <!-- Personal Information -->
                    <div class="profile-section-card">
                        <div class="profile-section-header">
                            <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['full_name'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input" value="<?php echo htmlspecialchars($userProfile['email'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-input" value="<?php echo htmlspecialchars($userProfile['phone'] ?? 'Not provided'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="text" class="form-input" value="<?php echo isset($userProfile['date_of_birth']) && $userProfile['date_of_birth'] ? date('F j, Y', strtotime($userProfile['date_of_birth'])) : 'Not provided'; ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['address_line1'] ?? 'Not provided'); ?>" readonly>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['city'] ?? 'Not provided'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">State/Province</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['state'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Zip Code</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['zip_code'] ?? 'Not provided'); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['country'] ?? 'Not provided'); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Professional Title</label>
                            <input type="text" class="form-input" value="<?php echo htmlspecialchars($userProfile['professional_title'] ?? 'Not provided'); ?>" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Professional Summary</label>
                            <textarea class="form-textarea" rows="4" readonly><?php echo htmlspecialchars($userProfile['professional_summary'] ?? 'No summary provided'); ?></textarea>
                        </div>
                    </div>

                    <!-- Education -->
                    <div class="profile-section-card">
                        <div class="profile-section-header">
                            <h3><i class="fas fa-graduation-cap"></i> Education</h3>
                            <button class="btn-add" id="addEducationBtn"><i class="fas fa-plus"></i> Add Education</button>
                        </div>
                        <div class="profile-entries-list" id="educationList">
                            <?php if (empty($education)): ?>
                                <div class="empty-state-message" style="text-align: center; padding: 2rem; color: #718096;">
                                    <i class="fas fa-graduation-cap" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: #cbd5e0;"></i>
                                    <p>No education records added yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($education as $edu): ?>
                                    <div class="profile-entry">
                                        <div class="profile-entry-header">
                                            <div class="profile-entry-info">
                                                <h4><?php echo htmlspecialchars($edu['institution_name'] ?? ''); ?></h4>
                                                <p><?php echo htmlspecialchars($edu['degree'] ?? ''); ?><?php echo $edu['field_of_study'] ? ' in ' . htmlspecialchars($edu['field_of_study']) : ''; ?></p>
                                                <?php if ($edu['start_date'] || $edu['end_date']): ?>
                                                    <p class="entry-duration">
                                                        <?php echo $edu['start_date'] ? date('M Y', strtotime($edu['start_date'])) : ''; ?> -
                                                        <?php echo $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present'; ?>
                                                    </p>
                                                <?php endif; ?>
                                                <?php if ($edu['gpa']): ?>
                                                    <p style="font-size: 0.85rem; color: var(--text-light); margin-top: 0.25rem;">GPA: <?php echo htmlspecialchars($edu['gpa']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <button class="btn-delete-entry" onclick="deleteEducation(<?php echo $edu['education_id']; ?>)"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Work Experience -->
                    <div class="profile-section-card">
                        <div class="profile-section-header">
                            <h3><i class="fas fa-briefcase"></i> Work Experience</h3>
                            <button class="btn-add" id="addExperienceBtn"><i class="fas fa-plus"></i> Add Experience</button>
                        </div>
                        <div class="profile-entries-list" id="experienceList">
                            <?php if (empty($experience)): ?>
                                <div class="empty-state-message" style="text-align: center; padding: 2rem; color: #718096;">
                                    <i class="fas fa-briefcase" style="font-size: 2rem; margin-bottom: 1rem; display: block; color: #cbd5e0;"></i>
                                    <p>No work experience added yet</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($experience as $exp): ?>
                                    <div class="profile-entry">
                                        <div class="profile-entry-header">
                                            <div class="profile-entry-info">
                                                <h4><?php echo htmlspecialchars($exp['job_title'] ?? ''); ?></h4>
                                                <p><?php echo htmlspecialchars($exp['company_name'] ?? ''); ?><?php echo $exp['location'] ? ' • ' . htmlspecialchars($exp['location']) : ''; ?></p>
                                                <?php if ($exp['start_date'] || $exp['end_date']): ?>
                                                    <p class="entry-duration">
                                                        <?php echo $exp['start_date'] ? date('M Y', strtotime($exp['start_date'])) : ''; ?> -
                                                        <?php echo ($exp['current_position'] ?? false) ? 'Present' : ($exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : ''); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <button class="btn-delete-entry" onclick="deleteExperience(<?php echo $exp['experience_id']; ?>)"><i class="fas fa-trash"></i></button>
                                        </div>
                                        <?php if ($exp['description']): ?>
                                            <div class="profile-entry-description"><?php echo nl2br(htmlspecialchars($exp['description'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div id="settings" class="tab-content">
                <header class="dashboard-header">
                    <h1 class="dashboard-greeting">Settings</h1>
                    <p class="section-description">Manage your account preferences</p>
                </header>

                <div class="settings-sections">
                    <div class="settings-card">
                        <h3 class="settings-title">
                            <i class="fas fa-user"></i>
                            Account
                        </h3>
                        <div class="settings-options">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <span class="setting-name">Logged in as</span>
                                    <span class="setting-description"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <span class="setting-name">Logout</span>
                                    <span class="setting-description">Sign out of your account</span>
                                </div>
                                <a href="logout.php" class="btn-setting-action" style="background-color: #ef4444; color: white; text-decoration: none; padding: 8px 16px; border-radius: 8px; display: inline-block;">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 ResumeSync. All rights reserved.</p>
        </div>
    </footer>

    <!-- Add Education Modal -->
    <div class="modal" id="addEducationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Education</h2>
                <button class="close-modal" onclick="closeEducationModal()">&times;</button>
            </div>
            <form id="educationForm">
                <div class="form-group">
                    <label class="form-label">Institution</label>
                    <input type="text" class="form-input" name="institution" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Degree</label>
                    <input type="text" class="form-input" name="degree" placeholder="e.g., Bachelor of Science" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Field of Study</label>
                    <input type="text" class="form-input" name="field" placeholder="e.g., Computer Science">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="month" class="form-input" name="start_date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="month" class="form-input" name="end_date">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">GPA (optional)</label>
                    <input type="text" class="form-input" name="gpa" placeholder="e.g., 3.8">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEducationModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Education</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Experience Modal -->
    <div class="modal" id="addExperienceModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Work Experience</h2>
                <button class="close-modal" onclick="closeExperienceModal()">&times;</button>
            </div>
            <form id="experienceForm">
                <div class="form-group">
                    <label class="form-label">Job Title</label>
                    <input type="text" class="form-input" name="title" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Company</label>
                    <input type="text" class="form-input" name="company" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-input" name="location" placeholder="e.g., New York, NY">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="month" class="form-input" name="start_date">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="month" class="form-input" name="end_date" id="expEndDate">
                    </div>
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" name="is_current" id="isCurrentJob" style="width: auto;">
                        <span>I currently work here</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" name="description" rows="4" placeholder="Describe your responsibilities and achievements..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeExperienceModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Experience</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Application Modal -->
    <div class="modal" id="addApplicationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add Job Application</h2>
                <button type="button" class="close-modal" onclick="closeApplicationModal()">&times;</button>
            </div>
            <form id="applicationForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Company Name *</label>
                        <input type="text" class="form-input" name="company_name" required placeholder="e.g., Google">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Title *</label>
                        <input type="text" class="form-input" name="job_title" required placeholder="e.g., Software Engineer">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-input" name="job_location" placeholder="e.g., San Francisco, CA">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Type</label>
                        <select class="form-input" name="job_type">
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                            <option value="Freelance">Freelance</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Application Date *</label>
                        <input type="date" class="form-input" name="application_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Salary Range</label>
                        <input type="text" class="form-input" name="salary_range" placeholder="e.g., $80k - $120k">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select class="form-input" name="status" id="applicationStatus">
                            <option value="Applied" selected>Applied</option>
                            <option value="In Review">In Review</option>
                            <option value="Interview Scheduled">Interview Scheduled</option>
                            <option value="Interview Completed">Interview Completed</option>
                            <option value="Offer Received">Offer Received</option>
                            <option value="Accepted">Accepted</option>
                            <option value="Rejected">Rejected</option>
                            <option value="Withdrawn">Withdrawn</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Priority</label>
                        <select class="form-input" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Resume Used <span style="font-size: 0.875rem; color: #718096; font-weight: 400;">(Optional - for analytics)</span></label>
                    <select class="form-input" name="resume_id" id="resumeUsed">
                        <option value="">-- Not specified --</option>
                        <?php foreach ($resumes as $resume): ?>
                        <option value="<?php echo $resume['resume_id']; ?>">
                            <?php echo htmlspecialchars($resume['resume_title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Interview Details Section (conditionally shown) -->
                <div id="interviewDetailsSection" style="display: none; padding: 1.25rem; background: rgba(124, 58, 237, 0.05); border-radius: 12px; border: 2px dashed rgba(124, 58, 237, 0.2); margin-bottom: 1.25rem;">
                    <h3 style="font-size: 1rem; font-weight: 600; color: var(--accent-color); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-calendar-check"></i> Interview Details
                    </h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Interview Date & Time</label>
                            <input type="datetime-local" class="form-input" name="interview_date" id="interviewDate">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Interview Location/Platform</label>
                            <input type="text" class="form-input" name="interview_location" placeholder="e.g., Zoom, Office Address">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Interview Notes</label>
                        <textarea class="form-textarea" name="interview_notes" rows="3" placeholder="Add interview preparation notes, interviewer names, topics to discuss..."></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Job Posting URL</label>
                    <input type="url" class="form-input" name="application_url" placeholder="https://...">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Contact Person</label>
                        <input type="text" class="form-input" name="contact_person" placeholder="e.g., John Doe">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Contact Email</label>
                        <input type="email" class="form-input" name="contact_email" placeholder="recruiter@company.com">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-textarea" name="notes" rows="4" placeholder="Add any notes about this application..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeApplicationModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Application</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Application Details Modal -->
    <div class="modal" id="viewApplicationModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Application Details</h2>
                <button type="button" class="close-modal" onclick="closeViewApplicationModal()">&times;</button>
            </div>
            <div id="applicationDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Timeline Modal -->
    <div class="modal" id="timelineModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2><i class="fas fa-stream"></i> Application Timeline</h2>
                <button type="button" class="close-modal" onclick="closeTimelineModal()">&times;</button>
            </div>
            <div id="timelineContent" style="padding: 1.5rem;">
                <!-- Timeline will be loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- Share Resume Modal -->
    <div class="modal" id="shareModal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-share-alt"></i> Share Resume</h2>
                <button type="button" class="close-modal" onclick="closeShareModal()">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <p style="color: #6b7280; margin-bottom: 1.5rem;">Generate a public shareable link for your resume. Anyone with this link can view your resume.</p>

                <div id="shareContent">
                    <button id="generateLinkBtn" class="btn-primary" style="width: 100%; background: var(--accent-color); color: white; padding: 0.875rem; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 1rem;">
                        <i class="fas fa-link"></i> Generate Shareable Link
                    </button>
                </div>

                <div id="shareLinkSection" style="display: none;">
                    <div style="background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                        <label style="font-size: 0.875rem; font-weight: 600; color: #4b5563; display: block; margin-bottom: 0.5rem;">Public Link:</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="text" id="shareLink" readonly style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-family: monospace; font-size: 0.875rem; background: white;">
                            <button id="copyLinkBtn" class="btn-primary" style="background: var(--accent-color); color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; white-space: nowrap;">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>

                    <!-- Share Stats -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin-bottom: 1rem;">
                        <div style="background: #fef3c7; border-radius: 8px; padding: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #92400e;" id="viewCount">0</div>
                            <div style="font-size: 0.75rem; color: #78350f; margin-top: 0.25rem;">Views</div>
                        </div>
                        <div style="background: #dbeafe; border-radius: 8px; padding: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #1e40af;" id="downloadCount">0</div>
                            <div style="font-size: 0.75rem; color: #1e3a8a; margin-top: 0.25rem;">Downloads</div>
                        </div>
                        <div style="background: #d1fae5; border-radius: 8px; padding: 1rem; text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #065f46;" id="statusBadge">Public</div>
                            <div style="font-size: 0.75rem; color: #064e3b; margin-top: 0.25rem;">Status</div>
                        </div>
                    </div>

                    <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                        <button id="togglePublicBtn" class="btn-secondary" style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer; font-weight: 600; color: #4b5563;">
                            <i class="fas fa-eye-slash"></i> <span id="publicToggleText">Make Private</span>
                        </button>
                        <button id="openLinkBtn" class="btn-secondary" style="flex: 1; padding: 0.75rem; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer; font-weight: 600; color: #4b5563;">
                            <i class="fas fa-external-link-alt"></i> Open Link
                        </button>
                    </div>

                    <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 6px;">
                        <p style="font-size: 0.875rem; color: #1e40af; line-height: 1.6;">
                            <i class="fas fa-info-circle"></i> This link will remain active as long as the resume is set to public. You can revoke access anytime by making it private.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <!-- Notification Modal -->
    <div class="modal-overlay" id="notificationModal" style="display: none;">
        <div class="notification-modal" id="notificationModalContent">
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 id="notificationTitle">Success</h3>
            <p id="notificationMessage">Operation completed successfully</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-primary" id="notificationOkBtn">OK</button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmationModal" style="display: none;">
        <div class="notification-modal warning" id="confirmationModalContent">
            <div class="modal-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 id="confirmationTitle">Confirm Action</h3>
            <p id="confirmationMessage">Are you sure you want to proceed?</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-secondary" id="confirmationCancelBtn">Cancel</button>
                <button class="modal-btn modal-btn-primary" id="confirmationOkBtn">Confirm</button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="js/modal-utils.js?v=5"></script>
    <script src="js/app.js?v=14"></script>
    <script src="js/dashboard.js?v=19"></script>
    <script src="js/profile-manager.js?v=14"></script>
    <script src="js/application-tracker.js?v=3"></script>
    <script src="js/timeline-handler.js?v=1"></script>
</body>
</html>
