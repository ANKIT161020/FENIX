<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Authentication</title>
    <link rel="stylesheet" href="../css/login_css.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <h2 id="form-title">Login</h2>

        <!-- Login Form -->
        <form id="login-form" action="../php/login.php" method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock input-icon"></i>
            </div>
            <button type="submit" class="auth-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
            <p>Don't have an account? <a href="#" onclick="toggleForm('register')">Register here</a></p>
        </form>

        <!-- Register Form (Initially Hidden) -->
        <form id="register-form" action="../php/register.php" method="POST" style="display: none;">
            <div class="input-group">
                <input type="text" name="name" placeholder="Full Name" required>
                <i class="fas fa-user input-icon"></i>
            </div>
            <div class="input-group">
                <input type="text" name="faculty_id" placeholder="Faculty ID" required>
                <i class="fas fa-id-card input-icon"></i>
            </div>
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
            <div class="input-group">
                <input type="email" name="email" pattern="[a-zA-Z0-9._%+-]+@somaiya\.edu" placeholder="Email (e.g., example@somaiya.edu)" required>
                <i class="fas fa-envelope input-icon"></i>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
                <i class="fas fa-lock input-icon"></i>
            </div>
            <button type="submit" class="auth-btn"><i class="fas fa-user-plus"></i> Register</button>
            <p>Already have an account? <a href="#" onclick="toggleForm('login')">Login here</a></p>
        </form>
    </div>

    <!-- JavaScript to toggle between forms -->
    <script>
        function toggleForm(formType) {
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            const formTitle = document.getElementById('form-title');

            if (formType === 'register') {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
                formTitle.innerHTML = '<i class="fas fa-user-plus"></i> Register';
            } else {
                registerForm.style.display = 'none';
                loginForm.style.display = 'block';
                formTitle.innerHTML = 'Login';
            }
        }
    </script>
</body>
</html>
