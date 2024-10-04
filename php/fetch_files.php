<?php
include 'database.php';

// Fetch recent files for Quick Access
$query = "SELECT * FROM files";
$result = mysqli_query($conn, $query);

while($row = mysqli_fetch_assoc($result)) {
    echo "<div class='file-card'>";
    echo "<p>" . $row['file_name'] . "</p>";
    echo "</div>";
}
?>
