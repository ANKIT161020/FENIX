<?php
include 'database.php';
require_once 'Encrypter.php';
require_once 'Watermarker.php';
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

    // Fetch user details including department and email
    $user_stmt = $conn->prepare("SELECT department, email FROM users WHERE faculty_id = ?");
    $user_stmt->bind_param("s", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_stmt->close();

    if (!$user) {
        echo "Error: User information could not be retrieved.";
        exit();
    }

    if ($file) {
        // Check download permissions using your original rules
        $download_access_list = explode(',', $file['download_access']);
        
        // User is from the same department OR has explicit download access
        if ($file['department'] == $user['department'] || 
            $file['faculty_id'] == $user_id || 
            (!empty($file['download_access']) && strpos($file['download_access'], $user['email']) !== false)) {
            
            // Determine if watermark should be applied
            $apply_watermark = false;
            
            // Apply watermark if:
            // 1. User is not the file owner AND
            // 2. User is from a different department AND
            // 3. Watermark on download is enabled
            if ($file['faculty_id'] != $user_id && 
                $file['department'] != $user['department'] && 
                isset($file['watermark_on_download']) && 
                $file['watermark_on_download'] == 1) {
                $apply_watermark = true;
            }
            
            // Path to the encrypted file
            $encrypted_path = "../encrypted/" . $file['file_path'] . ".enc";
            
            if (file_exists($encrypted_path)) {
                // Create temporary file for decryption
                $temp_dir = sys_get_temp_dir();
                $temp_file = tempnam($temp_dir, 'dec_') . '.' . $file['file_type'];
                
                // Decrypt the file
                $encrypter = new Encrypter();
                $encrypter->setKey($file['encryption_key']);
                
                if ($encrypter->decrypt($encrypted_path, $temp_file)) {
                    $download_file = $temp_file;
                    
                    // Apply watermark if needed
                    if ($apply_watermark) {
                        $watermarked_file = tempnam($temp_dir, 'wm_') . '.' . $file['file_type'];
                        $watermarker = new Watermarker();
                        
                        if (strtolower($file['file_type']) === 'pdf') {
                            $watermarker->applyToPdf($temp_file, $watermarked_file);
                            $download_file = $watermarked_file;
                        } else if (in_array(strtolower($file['file_type']), ['jpg', 'jpeg', 'png', 'gif'])) {
                            $watermarker->applyToImage($temp_file, $watermarked_file);
                            $download_file = $watermarked_file;
                        }
                    }
                    
                    // Set headers to force download
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file['file_name']) . '"');
                    header('Content-Length: ' . filesize($download_file));
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    
                    // Clean output buffer
                    ob_clean();
                    flush();
                    
                    // Send file content to user
                    readfile($download_file);
                    
                    // Clean up temporary files
                    unlink($temp_file);
                    if (isset($watermarked_file) && file_exists($watermarked_file)) {
                        unlink($watermarked_file);
                    }
                    
                    exit();
                } else {
                    echo "Error: Could not decrypt the file.";
                }
            } else {
                echo "Error: The requested encrypted file does not exist on the server.";
            }
        } else {
            echo "Error: You do not have permission to download this file.";
        }
    } else {
        echo "Error: Invalid file ID or file not found.";
    }
} else {
    echo "Error: No file ID provided.";
}
?>
