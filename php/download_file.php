<?php
include 'database.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if ($file_id) {
    // Fetch file details securely from the documents table
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();

    if ($file) {
        $file_path = '../uploads/' . $file['file_path'];
        if (file_exists($file_path)) {
            // Set headers to force download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
            header('Content-Length: ' . filesize($file_path));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            ob_clean();
            flush();
            readfile($file_path);
            exit();
        } else {
            echo "Error: The requested file does not exist on the server.";
        }
    } else {
        echo "Error: Invalid file ID or you do not have permission to download this file.";
    }
} else {
    echo "Error: No file ID provided.";
}
?>