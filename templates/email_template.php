<?php
function getVerificationEmailHTML($name, $verify_link) {
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Email Verification</title>
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
                margin: 30px auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 8px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                overflow: hidden;
            }
            .header {
                background-color: #ff4757;
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
            }
            .content p {
                margin-bottom: 20px;
            }
            .verify-button {
                display: inline-block;
                padding: 12px 20px;
                background-color: #ff4757;
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
                transition: background-color 0.3s ease;
            }
            .verify-button:hover {
                background-color: #e84118;
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
                <h1>Email Verification</h1>
            </div>
            <div class='content'>
                <p>Hi <strong>$name</strong>,</p>
                <p>Thank you for registering with us! Please verify your email address by clicking the button below.</p>
                <p style='text-align: center;'>
                    <a href='$verify_link' class='verify-button'>Verify Email Address</a>
                </p>
                <p>If the button above doesn't work, please copy and paste the following link into your web browser:</p>
                <p><a href='$verify_link'>$verify_link</a></p>
                <p>If you did not request this, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>Thank you!</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>
