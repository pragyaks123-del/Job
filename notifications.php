<?php
header('Content-Type: application/json');
require 'db_connect.php';
require 'notification_helpers.php';

$role = $_GET['role'] ?? '';
$recipientId = (int) ($_GET['recipient_id'] ?? 0);

if (!$role || !$recipientId) {
    echo json_encode(["success" => false, "message" => "Missing notification context."]);
    exit;
}

if (!in_array($role, ['job_seeker', 'employer'], true)) {
    echo json_encode(["success" => false, "message" => "Invalid notification role."]);
    exit;
}

try {
    ensure_notifications_table($pdo);

    if ($role === 'job_seeker') {
        sync_job_notifications($pdo, $recipientId);
    }

    $listStmt = $pdo->prepare("
        SELECT id, title, message, link_url, event_type, is_read, created_at
        FROM notifications
        WHERE recipient_type = ? AND recipient_id = ?
        ORDER BY created_at DESC, id DESC
        LIMIT 20
    ");
    $listStmt->execute([$role, $recipientId]);
    $notifications = $listStmt->fetchAll(PDO::FETCH_ASSOC);

    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications
        WHERE recipient_type = ? AND recipient_id = ? AND is_read = 0
    ");
    $countStmt->execute([$role, $recipientId]);

    echo json_encode([
        "success" => true,
        "unread_count" => (int) $countStmt->fetchColumn(),
        "notifications" => $notifications
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
