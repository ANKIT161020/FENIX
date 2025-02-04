<?php
include 'database.php';
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['certificates'])) {
    $faculty_id = $_SESSION['user_id'];

    // Fetch the user's department securely
    $stmt = $conn->prepare("SELECT department FROM users WHERE faculty_id = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        die("User not found.");
    }

    $user_department = $user['department'];
    $upload_dir = "../uploads/";
    $uploaded_files = [];

    // Ensure uploads directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    foreach ($_FILES["certificates"]["tmp_name"] as $key => $tmp_name) {
        $file_name = basename($_FILES["certificates"]["name"][$key]);
        $file_tmp = $_FILES["certificates"]["tmp_name"][$key];
        $unique_file_name = uniqid() . "_" . $file_name;
        $target_file = $upload_dir . $unique_file_name;

        if (move_uploaded_file($file_tmp, $target_file)) {
            $uploaded_files[] = $target_file;
        }
    }

    // Convert uploaded file paths to JSON for Python script
    $json_files = json_encode($uploaded_files);
$command = "python3 ../process_certificates.py '$json_files' 2>&1";
$csv_output = shell_exec($command);

if (!file_exists(trim($csv_output))) {
    die("Error processing certificates: " . htmlspecialchars($csv_output));
}

    echo "<p>Processing completed. <a href='$csv_output' download>Download CSV</a></p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Certificates</title>
    <link rel="stylesheet" href="../css/upload_css.css">
</head>
<body>
    <h2>Upload Student Certificates</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="certificates[]" multiple required>
        <button type="submit">Upload & Process</button>
    </form>
</body>
</html>
