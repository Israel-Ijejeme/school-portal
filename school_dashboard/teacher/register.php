<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Department.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// If already logged in and is teacher, redirect to dashboard
if (SessionManager::isLoggedIn() && SessionManager::isTeacher()) {
    header('Location: dashboard.php');
    exit;
}

// Get departments for dropdown
$departmentObj = new Department();
$departments = $departmentObj->getAllDepartments();

// Handle registration form submission
if (Utility::isPostRequest()) {
    $full_name = Utility::sanitizeInput($_POST['full_name']);
    $email = Utility::sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $department_id = (int)$_POST['department_id'];
    $age = (int)$_POST['age'];
    
    // Validate form data
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = 'Full name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!Utility::validateEmail($email)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if ($department_id <= 0) {
        $errors[] = 'Please select a department';
    }
    
    if ($age <= 0) {
        $errors[] = 'Please enter a valid age';
    }
    
    // If no validation errors, attempt registration
    if (empty($errors)) {
        $user = new User();
        
        // Teacher user type ID is 2
        $user_type_id = 2;
        
        if ($user->register($full_name, $email, $password, $user_type_id, $department_id, $age)) {
            // Set success message and redirect to login
            SessionManager::setFlash('success', 'Registration successful! You can now login.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Registration failed. Email may already be in use.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Registration - School Dashboard</title>
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
        .register-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-register {
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
            <div class="alert alert-danger mx-auto" style="max-width: 600px;">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="register-container">
            <div class="logo">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h4 class="text-center mb-4">Teacher Registration</h4>
            
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo isset($full_name) ? $full_name : ''; ?>" required>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-select" id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo isset($department_id) && $department_id == $dept['id'] ? 'selected' : ''; ?>>
                                    <?php echo $dept['name']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" min="18" max="100" value="<?php echo isset($age) ? $age : ''; ?>" required>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" class="btn btn-success btn-register">Register</button>
                </div>
            </form>
            
            <div class="text-center mt-3">
                <p>Already have an account? <a href="login.php" class="text-decoration-none">Sign In</a></p>
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