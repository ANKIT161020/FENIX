<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['document'])) {
    $user_id = $_SESSION['user_id'];
    $file_name = basename($_FILES["document"]["name"]);
    $file_type = pathinfo($file_name, PATHINFO_EXTENSION);
    $file_size = $_FILES["document"]["size"];
    $file_tmp = $_FILES["document"]["tmp_name"];
    $upload_dir = "../uploads/";

    // Create uploads folder if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Creates the directory with full permissions
    }

    // Create a unique name to avoid overwriting
    $unique_file_name = uniqid() . "_" . $file_name;
    $target_file = $upload_dir . $unique_file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        // Insert file info into the database
        $query = "INSERT INTO documents (user_id, file_name, file_type, file_size, file_path) 
                  VALUES ('$user_id', '$file_name', '$file_type', '$file_size', '$target_file')";
        mysqli_query($conn, $query);
        header('Location: ../index.php'); // Redirect to the dashboard
        exit();
    } else {
        echo "Error uploading file.";
    }
}
?>
