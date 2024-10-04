<?php
include 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login if not authenticated
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$query = "SELECT username, email, avatar FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Set default avatar if not present
if (!$user['avatar']) {
    $user['avatar'] = 'default-avatar.png';
}
?>
