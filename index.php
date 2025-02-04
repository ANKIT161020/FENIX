<?php
include 'php/database.php';
session_start();
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: templates/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_query = "SELECT name, email, department, avatar FROM users WHERE faculty_id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found. Please log in again.");
}

$user_department = $user['department'];
$user_initials = strtoupper($user['name'][0]);

// Fetch all unique departments
$dept_query = "SELECT DISTINCT department FROM documents";
$dept_result = mysqli_query($conn, $dept_query);
$departments = [];
while ($dept = mysqli_fetch_assoc($dept_result)) {
    $departments[] = $dept['department'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centralized Data Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/styles.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <?php if ($user['avatar'] && $user['avatar'] != 'default-avatar.png'): ?>
                    <img src="<?php echo 'uploads/' . htmlspecialchars($user['avatar']); ?>" alt="User Avatar" class="avatar">
                <?php else: ?>
                    <div class="avatar-placeholder"><?php echo htmlspecialchars($user_initials); ?></div>
                <?php endif; ?>
                
                <p class="username"><?php echo htmlspecialchars($user['name']); ?></p>
                <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="department">Department: <?php echo htmlspecialchars($user['department']); ?></p>
            </div>
            <nav class="nav-menu">
                <a href="#" class="nav-link active" onclick="showFilesByDepartment('<?php echo htmlspecialchars($user_department); ?>', this)" id="user-department">
                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($user_department); ?>
                </a>
                <?php foreach ($departments as $department): ?>
                    <?php if ($department != $user_department): ?>
                        <a href="#" class="nav-link" onclick="showFilesByDepartment('<?php echo htmlspecialchars($department); ?>', this)" id="<?php echo htmlspecialchars($department); ?>">
                            <i class="fas fa-folder"></i> <?php echo htmlspecialchars($department); ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a href="php/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>

        <!-- Modal Structure -->
        <div id="fileModal">
            <button onclick="closeModal()">Close</button>
            <div id="modalContent">
                <!-- File content will be injected here by openModal -->
            </div>
            
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <h1><?php echo htmlspecialchars($user_department); ?> Files</h1>
                <div class="new-buttons">
                    <a href="./templates/upload.php" class="new-btn"><i class="fas fa-file-upload"></i> Upload Document</a>
                </div>
            </header>
            <section class="all-files">
                <div class="file-filters">
                    <button class="filter-btn active" onclick="showFilesByDepartment('<?php echo htmlspecialchars($user_department); ?>')">View All</button>
                    <button class="filter-btn" onclick="showFilesByType('doc')">Documents</button>
                    <button class="filter-btn" onclick="showFilesByType('xls')">Spreadsheets</button>
                    <button class="filter-btn" onclick="showFilesByType('pdf')">PDFs</button>
                    <button class="filter-btn" onclick="showFilesByType('image')">Images</button> 
                    <!-- Upload Certificates Button (Only for Teachers) -->
                    <?php if (!empty($user_department)): ?>
                      <a href="./php/upload_certificates.php" class="filter-btn upload-btn">
                        <i class="fas fa-upload"></i> Upload Certificates
                      </a>
                    <?php endif; ?>
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
                <div id="file-list" class="file-list"></div>
            </section>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script src="./js/modal_viewer.js"></script>
</body>
</html>
