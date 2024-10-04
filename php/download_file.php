<?php
include 'database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}

if (isset($_GET['id'])) {
    $file_id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM files WHERE id='$file_id'";
    $result = mysqli_query($conn, $query);
    $file = mysqli_fetch_assoc($result);

    if ($file) {
        $file_path = $file['file_path'];

        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit();
        } else {
            echo "File not found.";
        }
    } else {
        echo "Invalid file ID.";
    }
}
?>
