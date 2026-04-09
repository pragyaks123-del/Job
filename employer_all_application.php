<?php
header('Content-Type: application/json');
require 'db_connect.php';

$employer_id = $_GET['employer_id'] ?? 1;

try {
    $query = "
        SELECT
            a.id,
            a.user_id,
            a.status,
            a.cover_letter,
            a.resume_path,
            a.applied_at,
            j.id    AS job_id,
            j.title AS job_title,
            COALESCE(u.name,  CONCAT('Applicant #', a.user_id)) AS applicant_name,
            COALESCE(u.email, '')                                AS applicant_email
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE j.employer_id = ?
        ORDER BY a.applied_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$employer_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
