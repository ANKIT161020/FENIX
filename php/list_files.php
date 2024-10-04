<?php
include 'database.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.html'); // Redirect to login if not authenticated
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch files uploaded by the logged-in user
$query = "SELECT * FROM files WHERE user_id='$user_id'";
$result = mysqli_query($conn, $query);

// Debug: Check query success
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Check if any files are found
if (mysqli_num_rows($result) > 0) {
    while ($file = mysqli_fetch_assoc($result)) {
        // Display file details in table rows
        echo "<tr>";
        echo "<td>" . htmlspecialchars($file['file_name']) . "</td>";
        echo "<td>" . htmlspecialchars($file['department']) . "</td>";
        echo "<td>
                <a href='../php/download_file.php?id=" . $file['id'] . "'>Download</a> |
                <a href='../php/delete_file.php?id=" . $file['id'] . "' onclick=\"return confirm('Are you sure you want to delete this file?');\">Delete</a>
              </td>";
        echo "</tr>";
    }
} else {
    // If no files are found
    echo "<tr><td colspan='3'>No files uploaded yet.</td></tr>";
}
?>
