<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../php/database.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html');
    exit();
}

// Fetch the user's department from the database
$faculty_id = $_SESSION['user_id'];
$query = "SELECT department FROM users WHERE faculty_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle missing user data
if ($user) {
    $user_department = htmlspecialchars($user['department'], ENT_QUOTES, 'UTF-8');
} else {
    die("User department not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
    <link rel="stylesheet" href="../css/upload_css.css">
</head>
<body>
    <div class="upload-container">
        <h2>Upload File</h2>
        <form action="../php/upload.php" method="POST" enctype="multipart/form-data">
            <!-- File Input -->
            <label for="file">Choose a file:</label><br>
            <input type="file" name="document" id="file" accept=".docx, .xlsx, .pdf, .png, .jpg" required><br><br>
            
            <!-- Display Department (read-only) -->
            <label for="department">Your Department:</label><br>
            <input type="text" name="department" id="department" value="<?php echo $user_department; ?>" readonly><br><br>
            
            <!-- Read Access Option -->
            <label for="read_access">Who Can See This File?</label><br>
            <select name="read_access" id="read_access" required>
                <option value="all">All Departments</option>
                <option value="department">Only My Department</option>
            </select><br><br>
            
            <!-- Edit Access (Comma-separated emails) -->
            <label for="edit_access">Who Can Edit? (Enter Faculty Emails separated by commas):</label><br>
            <input type="text" name="edit_access" id="edit_access" placeholder="e.g., user1@somaiya.edu, user2@somaiya.edu"><br><br>
            
            <!-- Download Access (Comma-separated emails) -->
            <label for="download_access">Who Can Download? (Enter Faculty Emails separated by commas):</label><br>
            <input type="text" name="download_access" id="download_access" placeholder="e.g., user1@somaiya.edu, user2@somaiya.edu"><br><br>
            
            <!-- Submit Button -->
            <button type="submit">Upload</button>
        </form>
    </div>
</body>
</html>
