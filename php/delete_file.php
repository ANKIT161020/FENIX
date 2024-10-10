<?php
include 'database.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
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

    if ($file && $file['uploaded_by'] == $user_id) {
        // Proceed to delete the file
        $file_path = '../uploads/' . $file['file_path'];
        if (file_exists($file_path)) {
            unlink($file_path); // Delete the file from the server
        }

        // Delete the file record from the database
        $delete_stmt = $conn->prepare("DELETE FROM documents WHERE id = ?");
        $delete_stmt->bind_param("i", $file_id);
        if ($delete_stmt->execute()) {
            echo "File deleted successfully.";
        } else {
            echo "Error deleting file: " . $conn->error;
        }
        $delete_stmt->close();
    } else {
        echo "Error: You do not have permission to delete this file.";
    }
} else {
    echo "Error: No file ID provided.";
}
?>
