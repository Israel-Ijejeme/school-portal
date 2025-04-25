<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// If already logged in and is teacher, redirect to dashboard
if (SessionManager::isLoggedIn() && SessionManager::isTeacher()) {
    header('Location: dashboard.php');
    exit;
}

// Handle login form submission
if (Utility::isPostRequest()) {
    $email = Utility::sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate form data
    $errors = [];
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!Utility::validateEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    }
    
    // If no validation errors, attempt login
    if (empty($errors)) {
        $user = new User();
        
        if ($user->login($email, $password)) {
            // Check if user is teacher
            if ($user->isTeacher()) {
                // Redirect to teacher dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'You are not authorized to access the teacher area';
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Login - School Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-login {
            padding: 12px;
            font-weight: bold;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            color: #198754;
        }
        .role-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #198754;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Display errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger mx-auto" style="max-width: 400px;">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="login-container">
            <div class="logo">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h4 class="text-center mb-4">Teacher Login</h4>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-login">Sign In</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register</a></p>
                <a href="../index.php" class="text-decoration-none">Back to Home</a>
            </div>
        </div>
    </div>
    
    <footer class="bg-light text-center text-lg-start mt-auto">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © 2023 School Dashboard - Nigerian Education System
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 