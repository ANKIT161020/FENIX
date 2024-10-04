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
$user_query = "SELECT name, email, department, avatar FROM users WHERE faculty_id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Handle missing user data
if (!$user) {
    die("User not found. Please log in again.");
}

$user_department = $user['department'];

// Get initials from user's name
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    >
    <link rel="stylesheet" href="./css/styles.css">
    
    <style>
        /* Container for user info */
        .user-info {
            text-align: center;
            margin-bottom: 20px;
            padding: 20px;
            background: #f0f4f8;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* Avatar styles */
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 4px solid #ffffff;
            transition: transform 0.3s ease-in-out;
        }

        .avatar:hover {
            transform: scale(1.1);
        }

        /* Custom avatar with initials */
        .avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #3e4a61;
            color: #fff;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
            border: 4px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        /* Styles for user details */
        .username {
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .email, .department {
            font-size: 16px;
            color: #7f8c8d;
            margin: 3px 0;
        }

        .department {
            font-style: italic;
            color: #34495e;
        }

        /* Additional shadow effect */
        .user-info:hover {
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        /* Sidebar navigation */
        .nav-menu .nav-link {
            padding: 10px 15px;
            margin: 8px 0;
            display: block;
            color: #2c3e50;
            text-decoration: none;
            font-size: 16px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .nav-menu .nav-link.active {
            background-color: #2980b9;
            color: #ffffff;
        }

        .nav-menu .nav-link:hover {
            background-color: #3498db;
            color: #ffffff;
        }

        /* Main content styles */
        .main-content {
            padding: 20px;
        }

        .new-buttons a {
            display: inline-block;
            padding: 10px 15px;
            background-color: #2980b9;
            color: #ffffff;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .new-buttons a:hover {
            background-color: #3498db;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="user-info">
                <?php if ($user['avatar'] && $user['avatar'] != 'default-avatar.png'): ?>
                    <!-- Display the avatar if available -->
                    <img src="<?php echo 'uploads/' . htmlspecialchars($user['avatar']); ?>" alt="User Avatar" class="avatar">
                <?php else: ?>
                    <!-- Display a circle with the initials if no avatar -->
                    <div class="avatar-placeholder"><?php echo htmlspecialchars($user_initials); ?></div>
                <?php endif; ?>
                
                <!-- Display user's name and other details -->
                <p class="username"><?php echo htmlspecialchars($user['name']); ?></p>
                <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
                <p class="department">Department: <?php echo htmlspecialchars($user['department']); ?></p>
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
                    <a href="./templates/upload.php" class="new-btn"><i class="fas fa-file-upload"></i> Upload Document</a>
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
