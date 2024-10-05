<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Include the Composer autoload file

function sendVerificationEmail($recipientEmail, $recipientName, $subject, $bodyHTML, $bodyPlain) {
    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();                                    // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';                     // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                             // Enable SMTP authentication
        $mail->Username = 'aab2@somaiya.edu';           // SMTP username
        $mail->Password = 'urdhvthmftbiohen';            // SMTP password
        $mail->SMTPSecure = 'tls';                          // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                  // TCP port to connect to

        // Recipients
        $mail->setFrom('aab2@somaiya.edu', 'FENIX'); // Sender email and name
        $mail->addAddress($recipientEmail, $recipientName);  // Add recipient email and name
        $mail->addReplyTo('aab2@somaiya.edu', 'FENIX'); // Reply-to address

        // Content
        $mail->isHTML(true);                                // Set email format to HTML
        $mail->Subject = $subject;                          // Subject of the email
        $mail->Body    = $bodyHTML;                         // HTML version of the email content
        $mail->AltBody = $bodyPlain;                        // Plain text version of the email content

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
?>
