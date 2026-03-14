<?php
// AJAX endpoint to check if a username exists (excluding current user)
session_start();
require_once 'db_connection.php';

if (!isset($_POST['username'])) {
    echo 'invalid';
    exit;
}

$username = trim($_POST['username']);
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
$stmt->bind_param('si', $username, $user_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo 'exists';
} else {
    echo 'ok';
}
$stmt->close();
