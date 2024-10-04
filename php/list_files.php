<?php
include 'database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit();
}

$user_id = $_SESSION['user_id'];
$department = $_GET['department'] ?? '';

// Validate department parameter
if (!$department) {
    echo "<p>No department selected.</p>";
    exit();
}

// SQL query to fetch files by department and user permissions
$query = "SELECT * FROM documents 
          WHERE department = '$department' 
          AND (read_access = 'all' OR FIND_IN_SET('$user_id', edit_access) OR FIND_IN_SET('$user_id', download_access))";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($file = mysqli_fetch_assoc($result)) {
        $file_name = htmlspecialchars($file['file_name']);
        $file_type = htmlspecialchars($file['file_type']);
        $file_size = round($file['file_size'] / 1024, 2) . ' KB';
        $file_path = '../uploads/' . htmlspecialchars($file['file_path']);
        $file_date = date("F d Y", strtotime($file['created_at']));

        echo "<div class='file-row'>
                <span class='file-name'>$file_name</span>
                <span class='file-size'>$file_size</span>
                <span class='file-type'>$file_type</span>
                <span class='file-modified'>$file_date</span>";

        // Check permissions and show icons
        if (in_array($user_id, explode(',', $file['download_access'])) || $file['read_access'] == 'all') {
            echo "<a href='$file_path' download class='file-options'><i class='fas fa-download'></i></a>";
        }
        if (in_array($user_id, explode(',', $file['edit_access']))) {
            echo "<a href='edit_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-edit'></i></a>";
        }
        echo "</div>";
    }
} else {
    echo "<p>No files found for this department.</p>";
}
?>
