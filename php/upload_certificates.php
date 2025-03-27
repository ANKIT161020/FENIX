<?php
// upload_certificates.php

include 'database.php';
session_start();

// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$notification = "";

// Process the upload when form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['certificates'])) {
    // Get the selected task from the hidden input (default is 'certificates')
    $task = isset($_POST['task']) ? $_POST['task'] : 'certificates';

    // 1. Get the teacher's user id from the session
    $faculty_id = $_SESSION['user_id'];

    // 2. Fetch the teacher's department (example, if needed)
    $stmt = $conn->prepare("SELECT department FROM users WHERE faculty_id = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        die("User not found.");
    }

    // 3. Set up upload directory
    $upload_dir = __DIR__ . "/../uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $uploaded_files = [];

    // 4. Loop through uploaded files
    foreach ($_FILES["certificates"]["tmp_name"] as $key => $tmp_name) {
        $file_name = basename($_FILES["certificates"]["name"][$key]);
        $file_tmp = $_FILES["certificates"]["tmp_name"][$key];

        // (Optional) Validate file type, e.g., only allow images/pdf

        // Generate a unique name to avoid overwriting existing files
        $unique_file_name = uniqid() . "_" . $file_name;
        $target_file = $upload_dir . $unique_file_name;

        // Move uploaded file to target location
        if (move_uploaded_file($file_tmp, $target_file)) {
            $uploaded_files[] = $target_file;
        }
    }

    // 5. Convert array of file paths to JSON for the Python script
    $json_files = json_encode($uploaded_files);

    // 6. Select the appropriate Python script based on the task selected
    $python_path = __DIR__ . "/../myenv/bin/python3";
    switch ($task) {
        case 'certificates':
            $script_path = "/opt/lampp/htdocs/fenix/process_certificates.py";
            break;
        case 'task2':
            $script_path = "/opt/lampp/htdocs/fenix/process_task2.py";
            break;
        case 'task3':
            $script_path = "/opt/lampp/htdocs/fenix/process_task3.py";
            break;
        default:
            $script_path = "/opt/lampp/htdocs/fenix/process_certificates.py";
            break;
    }
    // Capture stderr too (2>&1) so we get all output
    $command = "$python_path $script_path '$json_files' 2>&1";

    // 7. Execute the Python script and capture the output
    $raw_output = shell_exec($command);

    // Split output into lines and take the last non-empty line as the CSV path
    $output_lines = array_filter(explode("\n", trim($raw_output)));
    $csv_output = end($output_lines);

    // 8. Validate that the CSV path is valid
    if (!file_exists(trim($csv_output))) {
        // If the Python script returned an error or invalid path, show the full output for debugging
        die("Error processing certificates: <br><pre>" . htmlspecialchars($raw_output) . "</pre>");
    }

    // Construct a web-friendly path to your CSV
    $filename_only = basename($csv_output);
    // Make sure it starts with "/fenix"
    $web_path = "/fenix/downloads/" . $filename_only;

    // 9. Provide a download link
    $notification = "<p>Processing completed. <a href='$web_path' download>Download CSV</a></p>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Upload Certificates</title>
  <link rel="stylesheet" href="../css/upload_css.css">
  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
      background-color: #f0f2f5;
      color: #333;
      overflow-x: hidden;
    }
    .container {
      display: flex;
      height: 100vh;
      width: 100vw;
      background-color: #fff;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }
    /* Sidebar (minimal, for task buttons) */
    .sidebar {
      width: 250px;
      background-color: #ff4757;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 20px;
      overflow-y: auto;
      transition: width 0.3s ease;
    }
    .sidebar:hover {
      width: 280px;
    }
    .sidebar h3 {
      margin-bottom: 20px;
      font-size: 20px;
    }
    .sidebar .nav-btn {
      background-color: #ff4757;
      border: none;
      color: #fff;
      padding: 10px 15px;
      margin-bottom: 10px;
      text-align: left;
      font-size: 16px;
      cursor: pointer;
      border-radius: 8px;
      transition: background-color 0.3s, transform 0.3s;
    }
    .sidebar .nav-btn:hover {
      background-color: #e84118;
      transform: translateX(5px);
    }
    /* Active button style */
    .sidebar .nav-btn.active {
      background-color: #e84118;
      transform: translateX(5px);
    }
    /* Main Content Styles */
    .main-content {
      flex-grow: 1;
      padding: 30px;
      background-color: #f7f7f7;
      overflow-y: auto;
    }
    .header {
      margin-bottom: 30px;
    }
    .header h2 {
      font-size: 28px;
      color: #2c3e50;
    }
    /* File upload area */
    .upload-area {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .upload-area input[type="file"] {
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 5px;
      cursor: pointer;
    }
    .upload-btn {
      background-color: #4caf50;
      color: white;
      padding: 10px 15px;
      font-size: 14px;
      cursor: pointer;
      border: none;
      border-radius: 5px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: background-color 0.3s, transform 0.3s;
    }
    .upload-btn:hover {
      background-color: #45a049;
      transform: translateY(-2px);
    }
    /* Preview area */
    .preview-area {
      margin-top: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }
    .file-preview {
      border: 1px solid #ddd;
      padding: 10px;
      border-radius: 8px;
      background-color: #fff;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      width: 200px;
      text-align: center;
    }
    .file-preview img, .file-preview embed {
      max-width: 100%;
      max-height: 150px;
      margin-bottom: 10px;
    }
    .file-name {
      font-size: 14px;
      color: #333;
      word-wrap: break-word;
    }
    .notification {
      background-color: #ff4757;
      color: #fff;
      padding: 15px 25px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="container">
    <!-- Sidebar with task buttons -->
    <div class="sidebar">
      <h3>Tasks</h3>
      <button class="nav-btn active" onclick="setTask('certificates', this)">Certificates</button>
      <button class="nav-btn" onclick="setTask('task2', this)">Task 2</button>
      <button class="nav-btn" onclick="setTask('task3', this)">Task 3</button>
    </div>
    
    <!-- Main Content Section -->
    <div class="main-content">
      <div class="header">
        <h2>Upload Student Certificates</h2>
      </div>
      <form action="" method="post" enctype="multipart/form-data">
        <!-- Hidden input to hold the selected task -->
        <input type="hidden" name="task" id="task" value="certificates">
        <div class="upload-area">
          <input type="file" id="certificates" name="certificates[]" multiple required>
          <button type="submit" class="upload-btn">Upload &amp; Process</button>
        </div>
        <div id="preview-area" class="preview-area"></div>
      </form>

      <?php
      // Display notification if processing completed
      if (!empty($notification)) {
          echo "<div class='notification'>{$notification}</div>";
      }
      ?>
    </div>
  </div>

  <script>
    // Function to set task and mark the clicked button as active
    function setTask(task, btn) {
      // Set the hidden input value
      document.getElementById('task').value = task;
      // Remove active class from all nav buttons
      var buttons = document.querySelectorAll('.nav-btn');
      buttons.forEach(function(button) {
        button.classList.remove('active');
      });
      // Add active class to the clicked button
      btn.classList.add('active');
    }

    // JavaScript to preview selected multiple files
    document.getElementById('certificates').addEventListener('change', function(event) {
      const previewArea = document.getElementById('preview-area');
      previewArea.innerHTML = ''; // Clear previous previews
      const files = event.target.files;
      
      Array.from(files).forEach(file => {
        const fileURL = URL.createObjectURL(file);
        const fileElement = document.createElement('div');
        fileElement.className = 'file-preview';

        // Display file preview based on file type
        if (file.type.startsWith('image/')) {
          const img = document.createElement('img');
          img.src = fileURL;
          img.alt = file.name;
          fileElement.appendChild(img);
        } else if (file.type === 'application/pdf') {
          const embed = document.createElement('embed');
          embed.src = fileURL;
          embed.type = 'application/pdf';
          embed.width = "100%";
          embed.height = "150px";
          fileElement.appendChild(embed);
        } else {
          // Generic message if no preview is available
          const span = document.createElement('span');
          span.textContent = 'Preview not available';
          fileElement.appendChild(span);
        }
        // Always display the file name
        const nameEl = document.createElement('div');
        nameEl.className = 'file-name';
        nameEl.textContent = file.name;
        fileElement.appendChild(nameEl);
        
        previewArea.appendChild(fileElement);
      });
    });
  </script>
</body>
</html>
