<?php
function ensure_notifications_table(PDO $pdo): void
{
    static $initialized = false;
    if ($initialized) {
        return;
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            recipient_type VARCHAR(20) NOT NULL,
            recipient_id INT NOT NULL,
            event_type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            link_url VARCHAR(255) DEFAULT NULL,
            related_job_id INT DEFAULT NULL,
            related_application_id INT DEFAULT NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_notifications_recipient (recipient_type, recipient_id, is_read, created_at)
        )
    ");

    $initialized = true;
}

function notification_exists(
    PDO $pdo,
    string $recipientType,
    int $recipientId,
    string $eventType,
    ?int $relatedJobId,
    ?int $relatedApplicationId
): bool {
    ensure_notifications_table($pdo);

    $stmt = $pdo->prepare("
        SELECT id
        FROM notifications
        WHERE recipient_type = ?
          AND recipient_id = ?
          AND event_type = ?
          AND COALESCE(related_job_id, 0) = COALESCE(?, 0)
          AND COALESCE(related_application_id, 0) = COALESCE(?, 0)
        LIMIT 1
    ");
    $stmt->execute([$recipientType, $recipientId, $eventType, $relatedJobId, $relatedApplicationId]);

    return (bool) $stmt->fetchColumn();
}

function create_notification(
    PDO $pdo,
    string $recipientType,
    int $recipientId,
    string $eventType,
    string $title,
    string $message,
    ?string $linkUrl = null,
    ?int $relatedJobId = null,
    ?int $relatedApplicationId = null,
    bool $dedupe = false
): void {
    ensure_notifications_table($pdo);

    if ($dedupe && notification_exists($pdo, $recipientType, $recipientId, $eventType, $relatedJobId, $relatedApplicationId)) {
        return;
    }

    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            recipient_type,
            recipient_id,
            event_type,
            title,
            message,
            link_url,
            related_job_id,
            related_application_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $recipientType,
        $recipientId,
        $eventType,
        $title,
        $message,
        $linkUrl,
        $relatedJobId,
        $relatedApplicationId
    ]);
}

function sync_job_notifications(PDO $pdo, int $userId): void
{
    ensure_notifications_table($pdo);

    $stmt = $pdo->query("SELECT id, title, location FROM jobs ORDER BY id DESC");
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($jobs as $job) {
        $title = $job['title'] ?? 'New Job';
        $location = trim((string) ($job['location'] ?? ''));
        $message = $location !== ''
            ? "A new job was posted in {$location}."
            : "A new job was posted.";
        $linkUrl = 'apply.html?id=' . (int) $job['id'] . '&title=' . rawurlencode($title);

        create_notification(
            $pdo,
            'job_seeker',
            $userId,
            'new_job',
            $title,
            $message,
            $linkUrl,
            (int) $job['id'],
            null,
            true
        );
    }
}
?>
