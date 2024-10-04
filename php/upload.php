<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    // Collect user and document details
    $faculty_id = $_SESSION['user_id']; // Assuming user_id corresponds to faculty_id
    $department = $_POST['department']; // Department field from form
    $file_name = basename($_FILES["document"]["name"]);
    $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_size = $_FILES["document"]["size"];
    $file_tmp = $_FILES["document"]["tmp_name"];
    $upload_dir = "../uploads/";

    // Access control from form input
    $read_access = $_POST['read_access']; // 'all' or 'department'

    // Ensure edit_access and download_access are handled properly
    $edit_access = isset($_POST['edit_access']) && !empty($_POST['edit_access']) 
                   ? implode(',', (array) $_POST['edit_access']) 
                   : ''; // Comma-separated list of email addresses or empty string
    
    $download_access = isset($_POST['download_access']) && !empty($_POST['download_access']) 
                       ? implode(',', (array) $_POST['download_access']) 
                       : ''; // Comma-separated list of email addresses or empty string

    // Check for upload errors
    if ($_FILES["document"]["error"] !== UPLOAD_ERR_OK) {
        die("Upload failed with error code " . $_FILES["document"]["error"]);
    }

    // Create uploads folder if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Creates the directory with full permissions
    }

    // Create a unique file name to avoid overwriting
    $unique_file_name = uniqid() . "_" . $file_name;
    $target_file = $upload_dir . $unique_file_name;

    // Move the file to the uploads directory
    if (move_uploaded_file($file_tmp, $target_file)) {
        // Insert file info into the database
        $query = "INSERT INTO documents (faculty_id, department, file_name, file_path, file_type, file_size, read_access, edit_access, download_access) 
                  VALUES ('$faculty_id', '$department', '$file_name', '$unique_file_name', '$file_type', '$file_size', '$read_access', '$edit_access', '$download_access')";
        
        if (mysqli_query($conn, $query)) {
            // Successful upload and insertion
            echo "File uploaded and data inserted successfully.";
            // Uncomment this for redirect
            // header('Location: ../index.php');
            exit();
        } else {
            echo "Database error: " . mysqli_error($conn);
        }
    } else {
        echo "Error uploading file.";
    }
}
?>
