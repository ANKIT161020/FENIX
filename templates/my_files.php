<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Files</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="files-container">
    <h2>My Files</h2>
    <table>
        <tr>
            <th>File Name</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
        <?php include '../php/list_files.php'; ?>
    </table>
</div>
</body>
</html>
