<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="../css/login_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <h2><i class="fas fa-user-plus"></i> Register</h2>
        <form action="../php/register.php" method="POST">
            <!-- Full Name -->
            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <!-- Faculty ID -->
            <div class="input-group">
                <input type="text" name="faculty_id" placeholder="Faculty ID" required>
                <i class="fas fa-id-card input-icon"></i>
            </div>
            
            <!-- Department -->
            <div class="input-group">
                <select name="department" required>
                    <option value="">Select Department</option>
                    <option value="TnP Cell">TnP Cell</option>
                    <option value="Exam Cell">Exam Cell</option>
                    <option value="Sports Club">Sports Club</option>
                    <!-- Add more departments/clubs as needed -->
                </select>
                <i class="fas fa-building input-icon"></i>
            </div>

            <!-- Email with @somaiya.edu -->
            <div class="input-group">
                <input type="email" name="email" pattern="[a-zA-Z0-9._%+-]+@somaiya\.edu" placeholder="Email (e.g., example@somaiya.edu)" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            
            <!-- Password -->
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock input-icon"></i>
            </div>
            
            <button type="submit" class="login-btn"><i class="fas fa-user-plus"></i> Register</button>
        </form>
        <p>Already have an account? <a href="./login.php">Login here</a></p>
    </div>
</body>
</html>
