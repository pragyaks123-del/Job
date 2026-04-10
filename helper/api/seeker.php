<?php
// helper/api/seeker.php - Complete backend for job seeker dashboard

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

// Check if user is logged in and is a seeker
if (!$user) {
    jsonError('Please login first', 401);
}
if ($user['role'] !== 'seeker') {
    jsonError('Access denied. Job seekers only.', 403);
}

$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'applied_jobs':
            handleGetAppliedJobs($pdo, $user);
            break;
            
        case 'saved_jobs':
            handleGetSavedJobs($pdo, $user);
            break;
            
        case 'save_job':
            handleSaveJob($pdo, $user);
            break;
            
        case 'unsave_job':
            handleUnsaveJob($pdo, $user);
            break;
            
        case 'apply':
            handleApplyForJob($pdo, $user);
            break;
            
        case 'stats':
            handleGetStats($pdo, $user);
            break;

        case 'notifications':
            handleGetNotifications($pdo, $user);
            break;

        case 'mark_notification_read':
            handleMarkNotificationRead($pdo, $user);
            break;
            
        default:
            jsonError('Invalid action', 404);
    }
} catch(Exception $e) {
    jsonError($e->getMessage(), 500);
}

// ========== FUNCTIONS ==========

function handleGetAppliedJobs($pdo, $user) {
    $stmt = $pdo->prepare("
        SELECT 
            a.*, 
            j.title, 
            j.location, 
            j.salary_min, 
            j.salary_max, 
            j.job_type,
            j.description,
            j.skills,
            u.name as employer_name
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        JOIN users u ON j.employer_id = u.id
        WHERE a.seeker_id = ?
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$user['id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    jsonResponse(['applications' => $applications]);
}

function handleGetSavedJobs($pdo, $user) {
    // Check if saved_jobs table exists, if not create it
    try {
        $stmt = $pdo->prepare("
            SELECT j.*, u.name as employer_name
            FROM saved_jobs sj
            JOIN jobs j ON sj.job_id = j.id
            JOIN users u ON j.employer_id = u.id
            WHERE sj.seeker_id = ?
            ORDER BY sj.saved_at DESC
        ");
        $stmt->execute([$user['id']]);
        $savedJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Table doesn't exist, return empty array
        $savedJobs = [];
    }
    
    jsonResponse(['saved_jobs' => $savedJobs]);
}

function handleSaveJob($pdo, $user) {
    $data = getJson();
    $jobId = $data['job_id'] ?? 0;
    
    if (!$jobId) {
        jsonError('Job ID required');
    }
    
    // Check if job exists
    $check = $pdo->prepare("SELECT id FROM jobs WHERE id = ?");
    $check->execute([$jobId]);
    if (!$check->fetch()) {
        jsonError('Job not found', 404);
    }
    
    // Check if already saved
    $check = $pdo->prepare("SELECT id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
    $check->execute([$user['id'], $jobId]);
    if ($check->fetch()) {
        jsonError('Job already saved');
    }
    
    // Create saved_jobs table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS saved_jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            seeker_id INT NOT NULL,
            job_id INT NOT NULL,
            saved_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_save (seeker_id, job_id),
            FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE
        )
    ");
    
    $stmt = $pdo->prepare("INSERT INTO saved_jobs (seeker_id, job_id) VALUES (?, ?)");
    $stmt->execute([$user['id'], $jobId]);
    
    jsonSuccess('Job saved successfully');
}

function handleUnsaveJob($pdo, $user) {
    $data = getJson();
    $jobId = $data['job_id'] ?? 0;
    
    if (!$jobId) {
        jsonError('Job ID required');
    }
    
    $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE seeker_id = ? AND job_id = ?");
    $stmt->execute([$user['id'], $jobId]);
    
    jsonSuccess('Job removed from saved');
}

function handleApplyForJob($pdo, $user) {
    $data = getJson();
    $jobId = $data['job_id'] ?? 0;
    $coverLetter = $data['cover_letter'] ?? '';
    
    if (!$jobId) {
        jsonError('Job ID required');
    }
    
    // Check if already applied
    $check = $pdo->prepare("SELECT id FROM applications WHERE seeker_id = ? AND job_id = ?");
    $check->execute([$user['id'], $jobId]);
    if ($check->fetch()) {
        jsonError('You have already applied for this job');
    }
    
    // Check if job exists and is open
    $check = $pdo->prepare("SELECT id, employer_id FROM jobs WHERE id = ? AND status = 'open'");
    $check->execute([$jobId]);
    $job = $check->fetch();
    if (!$job) {
        jsonError('Job not found or not accepting applications');
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO applications (job_id, seeker_id, cover_letter, status, applied_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([$jobId, $user['id'], $coverLetter]);
    
    // Create notification for employer
    $pdo->prepare("
        INSERT INTO notifications (user_id, type, message, related_id, created_at)
        VALUES (?, 'new_application', ?, ?, NOW())
    ")->execute([$job['employer_id'], "New application for job #{$jobId}", $jobId]);
    
    jsonSuccess('Application submitted successfully', ['application_id' => $pdo->lastInsertId()]);
}

function handleGetStats($pdo, $user) {
    // Get applied count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE seeker_id = ?");
    $stmt->execute([$user['id']]);
    $appliedCount = $stmt->fetch()['count'];
    
    // Get pending count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE seeker_id = ? AND status = 'pending'");
    $stmt->execute([$user['id']]);
    $pendingCount = $stmt->fetch()['count'];
    
    // Get saved count
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM saved_jobs WHERE seeker_id = ?");
        $stmt->execute([$user['id']]);
        $savedCount = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        $savedCount = 0;
    }
    
    jsonResponse([
        'stats' => [
            'applied' => (int)$appliedCount,
            'pending' => (int)$pendingCount,
            'saved' => (int)$savedCount
        ]
    ]);
}

function handleGetNotifications($pdo, $user) {
    // Create notifications table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type VARCHAR(50),
            message TEXT,
            related_id INT,
            is_read BOOLEAN DEFAULT FALSE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
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
    
    // Get unread count
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
        // Mark all as read
        $stmt = $pdo->prepare("
            UPDATE notifications SET is_read = TRUE 
            WHERE user_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$user['id']]);
    }
    
    jsonSuccess('Notifications updated');
}
?>
