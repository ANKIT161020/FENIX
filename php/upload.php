<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'database.php';
require_once 'Encrypter.php'; // Include the Encrypter class
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    // Collect user and document details
    $faculty_id = $_SESSION['user_id'];

    // Fetch the user's department securely
    $stmt = $conn->prepare("SELECT department FROM users WHERE faculty_id = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $user_department = $user['department'];
    $stmt->close();

    // Ensure user uploads only to their department
    $department = $user_department;

    $file_name = basename($_FILES["document"]["name"]);
    $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_size = $_FILES["document"]["size"];
    $file_tmp = $_FILES["document"]["tmp_name"];
    $upload_dir = "../uploads/";
    $encrypted_dir = "../encrypted/"; // Directory for encrypted files

    // Access control from form input
    $read_access = isset($_POST['read_access']) ? $_POST['read_access'] : 'department';

    // Ensure edit_access and download_access are handled properly
    $edit_access = isset($_POST['edit_access']) && !empty($_POST['edit_access']) 
                   ? htmlspecialchars($_POST['edit_access'], ENT_QUOTES, 'UTF-8') 
                   : ''; // Comma-separated list of email addresses or empty string

    $download_access = isset($_POST['download_access']) && !empty($_POST['download_access']) 
                       ? htmlspecialchars($_POST['download_access'], ENT_QUOTES, 'UTF-8') 
                       : ''; // Comma-separated list of email addresses or empty string
                       
    // Get watermark preference for downloads
    $watermark_on_download = isset($_POST['watermark_on_download']) ? 1 : 0;

    // Check for upload errors
    if ($_FILES["document"]["error"] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code " . $_FILES["document"]["error"]);
    }

    // Create uploads and encrypted folders if they don't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Creates the directory with full permissions
    }
    
    if (!is_dir($encrypted_dir)) {
        mkdir($encrypted_dir, 0777, true); // Creates the directory for encrypted files
    }

    // Create a unique file name to avoid overwriting
    $unique_file_name = uniqid() . "_" . $file_name;
    $target_file = $upload_dir . $unique_file_name;
    $encrypted_file = $encrypted_dir . $unique_file_name . ".enc";

    // Move the file to the uploads directory
    if (move_uploaded_file($file_tmp, $target_file)) {
        // Create a new encrypter instance
        $encrypter = new Encrypter();
        
        // Encrypt the file
        if ($encrypter->encrypt($target_file, $encrypted_file)) {
            // Get the encryption key for storage
            $encryption_key = $encrypter->getKey();
            
            // Insert file info into the database securely
            $stmt = $conn->prepare("INSERT INTO documents (faculty_id, department, file_name, file_path, file_type, file_size, read_access, edit_access, download_access, encryption_key, watermark_on_download) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssssi", $faculty_id, $department, $file_name, $unique_file_name, $file_type, $file_size, $read_access, $edit_access, $download_access, $encryption_key, $watermark_on_download);
            
            if ($stmt->execute()) {
                // Delete the unencrypted file after encryption
                unlink($target_file);
                
                // Successful upload and insertion
                echo "File uploaded, encrypted, and data inserted successfully.";
                // Uncomment this for redirect
                // header('Location: ../index.php');
                exit();
            } else {
                echo "Database error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error encrypting file.";
            unlink($target_file); // Clean up the original file
        }
    } else {
        echo "Error uploading file.";
    }
}
?>
