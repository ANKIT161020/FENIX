<?php
include 'database.php';
require_once 'Encrypter.php';
require_once 'Watermarker.php'; // Include the Watermarker class
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$user_id = $_SESSION['user_id'];
$file_id = $_GET['id'];

// Get user info
$stmt = $conn->prepare("SELECT department, email FROM users WHERE faculty_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(403);
    exit('User not found');
}

// Get file info
$stmt = $conn->prepare("SELECT * FROM documents WHERE id = ?");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$file = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$file) {
    http_response_code(404);
    exit('File not found');
}

// Permission check (view or download access)
$can_view = false;

// File owner always has access without watermark
if ($file['faculty_id'] === $user_id) {
    $can_view = true;
    $apply_watermark = false;
}
// Check if read_access is set to college-wide
elseif ($file['read_access'] === 'all') {
    $can_view = true;
    // Apply watermark if from different department
    $apply_watermark = ($file['department'] !== $user['department']);
}
// Check if user is from the same department and read_access is department
elseif ($file['read_access'] === 'department' && $file['department'] === $user['department']) {
    $can_view = true;
    $apply_watermark = true; // Same department, no watermark
}
// Check if user has edit access
elseif (!empty($file['edit_access']) && strpos($file['edit_access'], $user['email']) !== false) {
    $can_view = true;
    $apply_watermark = true; // Edit access, no watermark
}
// Check if user has download access
elseif (!empty($file['download_access']) && strpos($file['download_access'], $user['email']) !== false) {
    $can_view = true;
    // Apply watermark if from different department
    $apply_watermark = ($file['department'] !== $user['department']);
}

// Add debug output to help troubleshoot
if (!$can_view) {
    error_log("Access denied for user {$user['email']} to file {$file_id}. User department: {$user['department']}, File department: {$file['department']}, Read access: {$file['read_access']}");
    http_response_code(403);
    exit('No permission to view this file');
}

// Decrypt the file to a temp location
$encrypted_path = "../encrypted/" . $file['file_path'] . ".enc";
$temp_dir = sys_get_temp_dir();
$temp_file = tempnam($temp_dir, 'dec_') . '.' . $file['file_type'];

$encrypter = new Encrypter();
$encrypter->setKey($file['encryption_key']);
if (!$encrypter->decrypt($encrypted_path, $temp_file)) {
    http_response_code(500);
    exit('Failed to decrypt file');
}

// Apply watermark if needed
$display_file = $temp_file;
if ($apply_watermark) {
    $watermarked_file = tempnam($temp_dir, 'wm_') . '.' . $file['file_type'];
    $watermarker = new Watermarker();
    
    // Add custom watermark text
    $watermark_text = "Viewed by: {$user['email']} on " . date('Y-m-d H:i:s');
    
    // Apply appropriate watermark based on file type
    $success = false;
    if (strtolower($file['file_type']) === 'pdf') {
        $success = $watermarker->applyToPdf($temp_file, $watermarked_file);
    } else if (in_array(strtolower($file['file_type']), ['jpg', 'jpeg', 'png', 'gif'])) {
        $success = $watermarker->applyToImage($temp_file, $watermarked_file);
    }
    
    if ($success) {
        $display_file = $watermarked_file;
    }
}

// Set headers and stream the file
$mime_types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif'
];
$content_type = $mime_types[strtolower($file['file_type'])] ?? 'application/octet-stream';

header('Content-Type: ' . $content_type);
header('Content-Length: ' . filesize($display_file));
header('Cache-Control: no-store, no-cache, must-revalidate');
readfile($display_file);

// Clean up
unlink($temp_file);
if (isset($watermarked_file) && file_exists($watermarked_file)) {
    unlink($watermarked_file);
}
exit;
?>
