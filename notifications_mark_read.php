<?php
header('Content-Type: application/json');
require 'db_connect.php';
require 'notification_helpers.php';

$data = json_decode(file_get_contents("php://input"), true) ?? [];

$role = $data['role'] ?? '';
$recipientId = (int) ($data['recipient_id'] ?? 0);
$notificationId = isset($data['notification_id']) ? (int) $data['notification_id'] : null;

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

    if ($notificationId) {
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE id = ? AND recipient_type = ? AND recipient_id = ?
        ");
        $stmt->execute([$notificationId, $role, $recipientId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE notifications
            SET is_read = 1
            WHERE recipient_type = ? AND recipient_id = ?
        ");
        $stmt->execute([$role, $recipientId]);
    }

    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
