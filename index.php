<?php
include 'php/database.php';
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: templates/login.html');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_query = "SELECT name, email, department, avatar FROM users WHERE faculty_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Handle missing user data
if (!$user) {
    die("User not found. Please log in again.");
}

$user_department = $user['department'];

// Default avatar if not present
if (!$user['avatar']) {
    $user['avatar'] = 'default-avatar.png';
}

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
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <img src="<?php echo 'uploads/' . htmlspecialchars($user['avatar']); ?>" alt="User Avatar" class="avatar">
                <p class="username"><?php echo htmlspecialchars($user['name']); ?></p>
                <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <nav class="nav-menu">
                <!-- User's department on top, with active state set initially -->
                <a href="#" class="nav-link active" onclick="showFilesByDepartment('<?php echo htmlspecialchars($user_department); ?>', this)" id="user-department">
                    <i class="fas fa-folder"></i> <?php echo htmlspecialchars($user_department); ?>
                </a>
                <!-- Other departments -->
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
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <h1><?php echo htmlspecialchars($user_department); ?> Files</h1>
                <div class="new-buttons">
                    <!-- Redirect to Upload Page -->
                    <a href="./templates/upload.html" class="new-btn"><i class="fas fa-file-upload"></i> Upload Document</a>
                </div>
            </header>
            
            <!-- Section to display files -->
            <section class="all-files">
                <div class="file-filters">
                    <button class="filter-btn active" onclick="showFilesByDepartment('<?php echo htmlspecialchars($user_department); ?>')">View All</button>
                    <button class="filter-btn" onclick="showFilesByType('doc')">Documents</button>
                    <button class="filter-btn" onclick="showFilesByType('xls')">Spreadsheets</button>
                    <button class="filter-btn" onclick="showFilesByType('pdf')">PDFs</button>
                    <button class="filter-btn" onclick="showFilesByType('image')">Images</button>
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
                
                <!-- File list populated dynamically -->
                <div id="file-list" class="file-list"></div>
            </section>
        </div>
    </div>
    
    <script src="js/script.js"></script>
    <script>
        // JavaScript function to fetch and display files based on department
        function showFilesByDepartment(department, element) {
            // Remove active state from all sidebar items
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            // Set active state to clicked item
            if (element) element.classList.add('active');

            // Send AJAX request to fetch files by department
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `./php/list_files.php?department=${department}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('file-list').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }

        // On page load, show user's department files by default
        window.onload = function() {
            const userDept = '<?php echo htmlspecialchars($user_department); ?>';
            // Set the active state to the user's department in the sidebar
            document.getElementById('user-department').classList.add('active');
            showFilesByDepartment(userDept, document.getElementById('user-department'));
        };
    </script>
</body>
</html>
