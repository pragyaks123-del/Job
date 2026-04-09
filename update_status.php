<?php
header('Content-Type: application/json');
require 'db_connect.php';
require 'notification_helpers.php';

$data = json_decode(file_get_contents("php://input"), true);

$app_id     = $data['app_id']     ?? null;
$new_status = $data['new_status'] ?? null;
$allowed    = ['pending', 'reviewed', 'accepted', 'rejected'];

if (!$app_id || !$new_status || !in_array($new_status, $allowed)) {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

try {
    $appInfo = $pdo->prepare("
        SELECT a.user_id, a.job_id, j.title
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ?
        LIMIT 1
    ");
    $appInfo->execute([$app_id]);
    $application = $appInfo->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $app_id]);

    if ($stmt->rowCount() > 0) {
        if ($application) {
            create_notification(
                $pdo,
                'job_seeker',
                (int) $application['user_id'],
                'status_update_' . $new_status,
                'Application status updated',
                "Your application for {$application['title']} is now {$new_status}.",
                'my_applications.html',
                (int) $application['job_id'],
                (int) $app_id
            );
        }
        echo json_encode(["success" => true, "message" => "Status updated to '{$new_status}'."]);
    } else {
        echo json_encode(["success" => false, "message" => "Application not found."]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
