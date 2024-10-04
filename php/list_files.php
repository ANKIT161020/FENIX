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
if (empty($department)) {
    echo "<p>No department selected.</p>";
    exit();
}

// SQL query to fetch files by department and user permissions
$query = "SELECT * FROM documents 
          WHERE department = ? 
          AND (read_access = 'all' 
               OR (read_access = 'department' AND department = (SELECT department FROM users WHERE faculty_id = ?)) 
               OR FIND_IN_SET(?, edit_access) 
               OR FIND_IN_SET(?, download_access))";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $department, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check for errors in query execution
if (!$result) {
    echo "Error fetching files: " . mysqli_error($conn);
    exit();
}

if ($result->num_rows > 0) {
    while ($file = $result->fetch_assoc()) {
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

        // View functionality: check if user has read access to view the file
        if ($file['read_access'] == 'all' || $file['department'] == $department || in_array($user_id, explode(',', $file['download_access']))) {
            echo "<a href='fenix/$file_path' target='_blank' class='file-options'><i class='fas fa-eye'></i></a>";
        }

        // Download functionality
        $download_access_list = explode(',', $file['download_access']);
        if ($file['read_access'] == 'all' || in_array($user_id, $download_access_list)) {
            // Link to download.php with file ID as a parameter
            echo "<a href='./php/download_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-download'></i></a>";
        }

        // Edit functionality
        $edit_access_list = explode(',', $file['edit_access']);
        if (in_array($user_id, $edit_access_list)) {
            echo "<a href='edit_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-edit'></i></a>";
        }
        
        echo "</div>";
    }
} else {
    echo "<p>No files found for this department.</p>";
}

$stmt->close();
?>
