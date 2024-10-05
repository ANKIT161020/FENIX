<?php
function getLoginSuccessHTML() {
    return "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Login Successful</title>
        <style>
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
                background-color: #28a745;
                padding: 20px;
                color: #ffffff;
                font-size: 24px;
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
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
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Login Successful!</h1>
            </div>
            <div class='content'>
                <div class='success-icon'>✔️</div>
                <p>You have successfully logged in. Welcome back!</p>
                <p>Enjoy exploring your dashboard.</p>
                <a href='../index.php' class='action-button'>Go to Dashboard</a>
            </div>
            <div class='footer'>
                <p>Welcome back to our platform!</p>
            </div>
        </div>
    </body>
    </html>
    ";
}
?>
