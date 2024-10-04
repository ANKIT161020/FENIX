<?php
include 'php/database.php'; // Database connection
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: templates/login.html'); // Redirect to login if not authenticated
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info from the database
$query = "SELECT username, email, avatar FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Default avatar if not present
if (!$user['avatar']) {
    $user['avatar'] = 'default-avatar.png';
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
                <p class="username"><?php echo htmlspecialchars($user['username']); ?></p>
                <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <nav class="nav-menu">
                <a href="#" class="nav-link active"><i class="fas fa-home"></i> Home</a>
                <a href="#" class="nav-link"><i class="fas fa-project-diagram"></i> All Projects</a>
                <a href="#" class="nav-link"><i class="fas fa-folder"></i> Project Files</a>
                <a href="php/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <header class="header">
                <h1>Project Files</h1>
                <div class="new-buttons">
                    <!-- File Upload Form -->
                    <form action="php/upload.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="document" accept=".docx, .xlsx, .pdf, .png, .jpg" required>
                        <button type="submit" class="new-btn"><i class="fas fa-file-upload"></i> Upload Document</button>
                    </form>
                </div>
            </header>
            
            <!-- Section to display files -->
            <section class="all-files">
                <div class="file-filters">
                    <button class="filter-btn active">View All</button>
                    <button class="filter-btn">Documents</button>
                    <button class="filter-btn">Spreadsheets</button>
                    <button class="filter-btn">PDFs</button>
                    <button class="filter-btn">Images</button>
                    <input type="text" class="search-input" placeholder="Search...">
                </div>
                
                <!-- PHP code to list all files from the 'documents' table -->
                <div class="file-list">
                    <?php
                    // Fetch user files
                    $query = "SELECT * FROM documents WHERE user_id='$user_id'";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($file = mysqli_fetch_assoc($result)) {
                            $file_name = htmlspecialchars($file['file_name']);
                            $file_type = htmlspecialchars($file['file_type']);
                            $file_size = round($file['file_size'] / 1024, 2) . ' KB';
                            $file_path = 'uploads/' . htmlspecialchars($file['file_path']);
                            $file_date = date("F d Y", strtotime($file['created_at']));

                            echo "<div class='file-row'>
                                    <span class='file-name'>$file_name</span>
                                    <span class='file-size'>$file_size</span>
                                    <span class='file-type'>$file_type</span>
                                    <span class='file-modified'>$file_date</span>
                                    <a href='$file_path' download class='file-options'><i class='fas fa-download'></i></a>
                                    <a href='php/delete_file.php?id=" . $file['id'] . "' onclick=\"return confirm('Are you sure you want to delete this file?');\" class='file-options'><i class='fas fa-trash'></i></a>
                                  </div>";
                        }
                    } else {
                        echo "<p>No files found.</p>";
                    }
                    ?>
                </div>
            </section>
        </div>
    </div>
    
    <script src="js/script.js"></script>
</body>
</html>
