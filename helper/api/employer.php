<?php
// helper/api/employer.php - Backend for employer dashboard

header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? 'http://localhost';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

require_once __DIR__ . '/../../backend/includes/database.php';
require_once __DIR__ . '/../../backend/includes/helpers.php';

startSession();
$user = getAuthUser();

if (!$user) {
    jsonError('Please login first', 401);
}
if ($user['role'] !== 'employer') {
    jsonError('Access denied. Employers only.', 403);
}

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'my_jobs':
            handleGetMyJobs($pdo, $user);
            break;
            
        case 'applications':
            handleGetApplications($pdo, $user);
            break;
            
        case 'update_application_status':
            handleUpdateApplicationStatus($pdo, $user);
            break;
            
        case 'notifications':
            handleGetNotifications($pdo, $user);
            break;
            
        case 'mark_notification_read':
            handleMarkNotificationRead($pdo, $user);
            break;
            
        case 'stats':
            handleGetStats($pdo, $user);
            break;
            
        default:
            jsonError('Invalid action', 404);
    }
} catch(Exception $e) {
    jsonError($e->getMessage(), 500);
}

function handleGetMyJobs($pdo, $user) {
    $stmt = $pdo->prepare("
        SELECT 
            j.*,
            (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
        FROM jobs j
        WHERE j.employer_id = ?
        ORDER BY j.created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(['jobs' => $jobs]);
}

function handleGetApplications($pdo, $user) {
    $jobId = $_GET['job_id'] ?? 0;
    
    $sql = "
        SELECT a.*, u.name, u.email, u.phone
        FROM applications a
        JOIN users u ON a.seeker_id = u.id
        JOIN jobs j ON a.job_id = j.id
        WHERE j.employer_id = ?
    ";
    $params = [$user['id']];
    
    if ($jobId) {
        $sql .= " AND a.job_id = ?";
        $params[] = $jobId;
    }
    
    $sql .= " ORDER BY a.applied_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(['applications' => $applications]);
}

function handleUpdateApplicationStatus($pdo, $user) {
    $data = getJson();
    $applicationId = $data['application_id'] ?? 0;
    $status = $data['status'] ?? '';
    
    $allowed = ['pending', 'accepted', 'rejected'];
    if (!in_array($status, $allowed)) {
        jsonError('Invalid status');
    }
    
    // Verify employer owns this application
    $check = $pdo->prepare("
        SELECT a.id, a.seeker_id, j.title
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ? AND j.employer_id = ?
    ");
    $check->execute([$applicationId, $user['id']]);
    $application = $check->fetch();
    
    if (!$application) {
        jsonError('Application not found', 404);
    }
    
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$status, $applicationId]);
    
    // Notify the seeker about status change
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, message, related_id, created_at)
        VALUES (?, 'application_status', ?, ?, NOW())
    ")->execute([
        $application['seeker_id'],
        "Your application for '{$application['title']}' has been {$status}",
        $applicationId
    ]);
    
    jsonSuccess('Status updated successfully');
}

function handleGetNotifications($pdo, $user) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50),
            message TEXT,
            related_id INT,
            is_read BOOLEAN DEFAULT FALSE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 50
    ");
    $stmt->execute([$user['id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM notifications 
        WHERE user_id = ? AND is_read = FALSE
    ");
    $stmt->execute([$user['id']]);
    $unreadCount = $stmt->fetch()['count'];
    
    jsonResponse([
        'notifications' => $notifications,
        'unread_count' => (int)$unreadCount
    ]);
}

function handleMarkNotificationRead($pdo, $user) {
    $data = getJson();
    $notificationId = $data['notification_id'] ?? 0;
    
    if ($notificationId) {
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = TRUE 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$notificationId, $user['id']]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = TRUE 
            WHERE user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$user['id']]);
    }
    
    jsonSuccess('Notifications updated');
}

function handleGetStats($pdo, $user) {
    // Total jobs
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM jobs WHERE employer_id = ?");
    $stmt->execute([$user['id']]);
    $totalJobs = $stmt->fetch()['count'];
    
    // Total applications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.employer_id = ?
    ");
    $stmt->execute([$user['id']]);
    $totalApplications = $stmt->fetch()['count'];
    
    // Pending applications
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE j.employer_id = ? AND a.status = 'pending'
    ");
    $stmt->execute([$user['id']]);
    $pendingApplications = $stmt->fetch()['count'];
    
    jsonResponse([
        'stats' => [
            'total_jobs' => (int)$totalJobs,
            'total_applications' => (int)$totalApplications,
            'pending_applications' => (int)$pendingApplications
        ]
    ]);
}
?>