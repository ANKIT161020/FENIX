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

    // Check if file exists in the database
    if ($file) {
        // Check if user has permission to download
        $download_access_list = explode(',', $file['download_access']);
        if (
            // User is from the same department OR explicitly has download access
            ($file['department'] == $user['department']) 
            || in_array($user_id, $download_access_list)
        ) {
            // Proceed with file download...
            $file_path = '../uploads/' . $file['file_path'];
            
            if (file_exists($file_path)) {
                // Set headers to force download
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
                header('Content-Length: ' . filesize($file_path));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                
                // Clear output buffer
                ob_clean();
                flush();

                // Read the file and send it to the output buffer
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
