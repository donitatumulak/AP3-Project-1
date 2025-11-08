<?php
session_start();
require_once '../config/Database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
    exit();
}

$database = new Database();
$db = $database->connect();

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
    exit();
}

// Fetch current password
$stmt = $db->prepare("SELECT user_password FROM user WHERE user_id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($current_password, $user['user_password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
    exit();
}

$new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
$update = $db->prepare("UPDATE user SET user_password = :password WHERE user_id = :id");
$update->bindParam(':password', $new_hashed);
$update->bindParam(':id', $user_id);

if ($update->execute()) {
    session_destroy();
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password.']);
}
