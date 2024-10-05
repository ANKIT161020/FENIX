<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'database.php';
include 'send_email.php'; // Include your mail configuration file
include '../templates/email_template.php';
include '../templates/register_success_template.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $faculty_id = mysqli_real_escape_string($conn, $_POST['faculty_id']);
    $department = mysqli_real_escape_string($conn, $_POST['department']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

     // Check if email already exists in the database
    $checkQuery = "SELECT * FROM users WHERE email = '$email'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Email already exists
        echo "This email is already registered. Please use a different email or login.";
    }else{
    
    // Generate a unique token for email verification
    $verification_token = bin2hex(random_bytes(16)); // Generate a 32 character token

    // Insert user info into the database
    $query = "INSERT INTO users (faculty_id, name, department, email, password, verification_token) 
              VALUES ('$faculty_id', '$name', '$department', '$email', '$password', '$verification_token')";
    
    if (mysqli_query($conn, $query)) {
        // Create the verification link
        $verify_link = "localhost/fenix/php/verify_email.php?token=$verification_token";

        // Set up email content
        $subject = "Verify Your Email Address";
        $bodyHTML = getVerificationEmailHTML($name, $verify_link);
        $bodyPlain = "Hi $name, Please click the link below to verify your email address: $verify_link";

        // Send verification email
        sendVerificationEmail($email, $name, $subject, $bodyHTML, $bodyPlain);
        
        // Assuming registration is successful:
        echo getRegisterSuccessHTML();
        exit();
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
}
?>
