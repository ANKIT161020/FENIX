<?php
include 'database.php';
include '../templates/verification_success_template.php';

// Get the token from the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Find the user with the matching token
    $query = "SELECT * FROM users WHERE verification_token='$token'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        // User found, update the is_verified status
        $update_query = "UPDATE users SET is_verified=TRUE, verification_token=NULL WHERE verification_token='$token'";
        if (mysqli_query($conn, $update_query)) {
           // Display the success template
            echo getVerificationSuccessHTML();
        } else {
            echo "Failed to verify email.";
        }
    } else {
        echo "Invalid or expired token.";
    }
} else {
    echo "No token provided.";
}
?>
