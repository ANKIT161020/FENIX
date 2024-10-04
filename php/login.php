<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Debug: Check session is set
        echo "Logged in as user ID: " . $_SESSION['user_id'];

        header('Location: ../index.php'); // Redirect to dashboard
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>
