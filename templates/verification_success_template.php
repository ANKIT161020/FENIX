<?php
function getVerificationSuccessHTML() {
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Email Verified</title>
        <style>
            /* General Reset */
            body, html {
                margin: 0;
                padding: 0;
                font-family: 'Arial', sans-serif;
                background-color: #f4f4f4;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                text-align: center;
            }
            .header {
                background-color: #28a745; /* Green success color */
                padding: 20px;
                color: #ffffff;
                text-align: center;
                font-size: 24px;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
            }
            .header h1 {
                margin: 0;
            }
            .content {
                padding: 30px;
                line-height: 1.5;
                font-size: 18px;
            }
            .content p {
                margin-bottom: 20px;
            }
            .success-icon {
                font-size: 50px;
                color: #28a745;
                margin-bottom: 20px;
            }
            .action-button {
                display: inline-block;
                padding: 12px 20px;
                background-color: #28a745;
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background-color 0.3s ease;
                margin-top: 20px;
            }
            .action-button:hover {
                background-color: #218838;
            }
            .footer {
                margin-top: 40px;
                padding: 20px;
                text-align: center;
                background-color: #f4f4f4;
                border-top: 1px solid #ddd;
            }
            .footer p {
                margin: 0;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Email Verified!</h1>
            </div>
            <div class='content'>
                <div class='success-icon'>✔️</div>
                <p>Your email has been verified successfully. You can now proceed to login and access all the features of your account.</p>
                <p>Thank you for verifying your email!</p>
                <a href='../templates/login.php' class='action-button'>Login to Your Account</a>
            </div>
            <div class='footer'>
                <p>Thank you for being with us!</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>
