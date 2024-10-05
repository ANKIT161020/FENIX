<?php
include 'database.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$file_id = isset($_GET['id']) ? intval($_GET['id']) : null; // Ensure the file ID is valid

if ($file_id) {
    // Fetch file details securely from the documents table
    $stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $file = $result->fetch_assoc();
    $stmt->close();

    // Fetch user details including department securely
    $user_stmt = $conn->prepare("SELECT department FROM users WHERE faculty_id = ?");
    $user_stmt->bind_param("s", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();

    // Check if user data was found
    if (!$user) {
        echo "Error: User information could not be retrieved.";
        exit();
    }

    // Check if file exists in the database
    if ($file) {
        // Debugging: Print departments for comparison
        // echo "User Department: " . $user['department'] . "<br>";
        // echo "File Department: " . $file['department'] . "<br>";

        // Fetch download access list
        $download_access_list = explode(',', $file['download_access']);

        // Check download permissions
        if (trim($file['department']) == trim($user['department'])) {
            // User is from the same department
            $has_permission = true;
        } elseif (in_array($user_id, $download_access_list)) {
            // User is in download access list
            $has_permission = true;
        } else {
            $has_permission = false;
        }

        // If user has permission, proceed with download
        if ($has_permission) {
            $file_path = '../uploads/' . $file['file_path'];
            
            if (file_exists($file_path)) {
                // Clear output buffer to prevent any previous output
                ob_end_clean();
                // Set headers to force download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
                header('Content-Length: ' . filesize($file_path));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                
                // Send file content to user
                readfile($file_path);
                exit();
            } else {
                echo "Error: The requested file does not exist on the server.";
            }
        } else {
            echo "Error: You do not have permission to download this file.";
            exit();
        }
    } else {
        echo "Error: Invalid file ID or you do not have permission to download this file.";
    }
} else {
    echo "Error: No file ID provided.";
}
?>
