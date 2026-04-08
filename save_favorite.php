<?php
header('Content-Type: application/json');
require 'db_connect.php';

// Read and decode JSON body
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

// Guard: if body is empty or not valid JSON
if (!$data || !is_array($data)) {
    echo json_encode(["success" => false, "message" => "Invalid or empty request body."]);
    exit;
}

// Guard: required fields
if (!array_key_exists('job_id', $data) || !array_key_exists('user_id', $data)) {
    echo json_encode(["success" => false, "message" => "Missing job_id or user_id."]);
    exit;
}

$user_id    = $data['user_id'];
$job_id     = $data['job_id'];
$title      = $data['title']      ?? '';
$category   = $data['category']   ?? '';
$location   = $data['location']   ?? '';
$job_type   = $data['job_type']   ?? '';
$salary_max = $data['salary_max'] ?? 0;

try {
    // Check for duplicate
    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND job_id = ?");
    $check->execute([$user_id, $job_id]);

    if ($check->fetch()) {
        echo json_encode(["success" => false, "message" => "Job already saved to favorites!"]);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO favorites (user_id, job_id, title, category, location, job_type, salary_max)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$user_id, $job_id, $title, $category, $location, $job_type, $salary_max]);

    echo json_encode(["success" => true, "message" => "Job saved to favorites!"]);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(["success" => false, "message" => "Job already saved to favorites!"]);
    } else {
        echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
    }
}
?>