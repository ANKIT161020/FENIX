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

// Fetch user's department securely
$user_department_query = "SELECT department FROM users WHERE faculty_id = ?";
$user_department_stmt = $conn->prepare($user_department_query);
$user_department_stmt->bind_param("s", $user_id);
$user_department_stmt->execute();
$user_department_result = $user_department_stmt->get_result();
$user_department_row = $user_department_result->fetch_assoc();
$user_department_stmt->close();

$user_department = $user_department_row['department'];

// SQL query to fetch files by department and user permissions
$query = "SELECT *, uploaded_by FROM documents 
          WHERE department = ? 
          AND (read_access = 'all' 
               OR (read_access = 'department' AND department = ?) 
               OR FIND_IN_SET(?, edit_access) 
               OR FIND_IN_SET(?, download_access))";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssss", $department, $user_department, $user_id, $user_id);
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
        $uploaded_by = $file['uploaded_by'];

        echo "<div class='file-row'>
                <span class='file-name'>$file_name</span>
                <span class='file-size'>$file_size</span>
                <span class='file-type'>$file_type</span>
                <span class='file-modified'>$file_date</span>";

        // View functionality
        if ($file['read_access'] == 'all' || $file['department'] == $user_department || in_array($user_id, explode(',', $file['download_access']))) {
            echo "<button onclick=\"openModal('fenix/$file_path', '$file_type'); return false;\" class='file-options'><i class='fas fa-eye'></i></button>";
        }

        // Download functionality
        if ($file['department'] == $user_department || in_array($user_id, explode(',', $file['download_access']))) {
            echo "<a href='./php/download_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-download'></i></a>";
        } else {
            echo "<i class='fas fa-lock file-options' title='Download restricted'></i>";
        }

        // Edit functionality
        if (in_array($user_id, explode(',', $file['edit_access']))) {
            echo "<a href='edit_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-edit'></i></a>";
        }

        // Delete functionality: Only visible if the user uploaded the file
        if ($uploaded_by == $user_id) {
            echo "<button onclick=\"deleteFile(" . $file['id'] . ")\" class='file-options'><i class='fas fa-trash-alt'></i></button>";
        }

        echo "</div>";
    }
} else {
    echo "<p>No files found for this department.</p>";
}

$stmt->close();
?>
<script>
function deleteFile(fileId) {
    if (confirm('Are you sure you want to delete this file?')) {
        window.location.href = './php/delete_file.php?id=' + fileId;
    }
}
</script>
