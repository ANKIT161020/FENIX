<?php
include 'database.php';
session_start();

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Fetch the file details
    $query = "SELECT * FROM documents WHERE id='$file_id' AND user_id='$user_id'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $file = mysqli_fetch_assoc($result);
        
        // Get the file path
        $file_path = realpath('../' . $file['file_path']); // Get absolute path
        
        // Debugging: Check file path
        echo "File path to delete: " . $file_path . "<br>";

        // Delete the file from the server
        if ($file_path && file_exists($file_path)) {
            if (unlink($file_path)) {
                echo "File deleted successfully.<br>";
            } else {
                echo "Failed to delete the file.<br>";
            }
        } else {
            echo "File does not exist or path is incorrect.<br>";
        }

        // Delete the file record from the database
        $delete_query = "DELETE FROM documents WHERE id='$file_id'";
        if (mysqli_query($conn, $delete_query)) {
            echo "File record deleted from the database.";
            header('Location: ../index.php'); // Redirect to the dashboard
            exit();
        } else {
            echo "Error deleting file record from the database.";
        }
    } else {
        echo "File not found in the database.";
    }
}
?>
