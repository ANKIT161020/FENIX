<?php
include 'database.php';
include './modal_viewer.php';
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
$user_department = getUserDepartment($conn, $user_id);

// Fetch files based on permissions and department
$files = getFilesByDepartment($conn, $department, $user_department, $user_id);

// Render the files
if ($files->num_rows > 0) {
    while ($file = $files->fetch_assoc()) {
        echo buildFileRow($file, $user_department, $user_id);
    }
} else {
    echo "<p>No files found for this department.</p>";
}

// Helper function to fetch user's department
function getUserDepartment($conn, $user_id) {
    $query = "SELECT department FROM users WHERE faculty_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['department'];
}

// Helper function to fetch files based on department and permissions
function getFilesByDepartment($conn, $department, $user_department, $user_id) {
    $query = "SELECT * FROM documents 
              WHERE department = ? 
              AND (read_access = 'all' 
                   OR (read_access = 'department' AND department = ?) 
                   OR FIND_IN_SET(?, edit_access) 
                   OR FIND_IN_SET(?, download_access))";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $department, $user_department, $user_id, $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Helper function to build file rows
function buildFileRow($file, $user_department, $user_id) {
    $file_name = htmlspecialchars($file['file_name']);
    $file_type = htmlspecialchars($file['file_type']);
    $file_size = round($file['file_size'] / 1024, 2) . ' KB';
    $file_path = '../uploads/' . htmlspecialchars($file['file_path']);
    $file_date = date("F d Y", strtotime($file['created_at']));
    
    // Building file row
    $html = "<div class='file-row'>
                <span class='file-name'>$file_name</span>
                <span class='file-size'>$file_size</span>
                <span class='file-type'>$file_type</span>
                <span class='file-modified'>$file_date</span>";
                
    $html .= buildViewButton($file, $file_path, $file_type, $user_department, $user_id);
    $html .= buildDownloadButton($file, $user_department, $user_id);
    $html .= buildEditButton($file, $user_id);
    
    $html .= "</div>";

    return $html;
}

// Helper function to build view button
function buildViewButton($file, $file_path, $file_type, $user_department, $user_id) {
    $hasReadAccess = $file['read_access'] == 'all' || $file['department'] == $user_department || in_array($user_id, explode(',', $file['download_access']));
    
    if (!empty($file_path) && !empty($file_type) && $hasReadAccess) {
        return "<button onclick=\"openModal('fenix/$file_path', '$file_type'); return false;\" class='file-options'><i class='fas fa-eye'></i></button>";
    }
    
    return "<p>Error: Unable to preview this file.</p>";
}

// Helper function to build download button
function buildDownloadButton($file, $user_department, $user_id) {
    $download_access_list = explode(',', $file['download_access']);
    
    if ($file['department'] == $user_department || in_array($user_id, $download_access_list)) {
        // User is either from the same department or has explicit download access
        return "<a href='./php/download_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-download'></i></a>";
    } else {
        // If user doesn't have download access, show a lock icon
        return "<i class='fas fa-lock file-options' title='Download restricted'></i>";
    }
}

// Helper function to build edit button
function buildEditButton($file, $user_id) {
    $edit_access_list = explode(',', $file['edit_access']);
    
    if (in_array($user_id, $edit_access_list)) {
        return "<a href='edit_file.php?id=" . $file['id'] . "' class='file-options'><i class='fas fa-edit'></i></a>";
    }
    
    return '';
}
?>
