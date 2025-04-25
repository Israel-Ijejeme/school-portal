<?php
require_once 'classes/SessionManager.php';
SessionManager::startSession();

// If user is already logged in, redirect to appropriate dashboard
if (SessionManager::isLoggedIn()) {
    if (SessionManager::isAdmin()) {
        header('Location: admin/dashboard.php');
    } elseif (SessionManager::isTeacher()) {
        header('Location: teacher/dashboard.php');
    } elseif (SessionManager::isStudent()) {
        header('Location: student/dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard - Login</title>
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
            max-width: 500px;
            margin: 50px auto;
            padding: 40px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-login {
            padding: 12px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .role-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #0d6efd;
        }
        .card {
            border: none;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .alert-flash {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Flash Messages -->
        <?php if (SessionManager::hasFlash('success')): ?>
            <div class="alert alert-success alert-flash">
                <?php echo SessionManager::getFlash('success'); ?>
            </div>
        <?php endif; ?>
        
        <?php if (SessionManager::hasFlash('error')): ?>
            <div class="alert alert-danger alert-flash">
                <?php echo SessionManager::getFlash('error'); ?>
            </div>
        <?php endif; ?>
        
        <div class="login-container">
            <div class="logo">
                <i class="fas fa-school"></i> School Dashboard
            </div>
            <h4 class="text-center mb-4">Sign in as</h4>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="role-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h5 class="card-title">Admin</h5>
                            <a href="admin/login.php" class="btn btn-primary btn-login w-100">Sign In</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="role-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h5 class="card-title">Teacher</h5>
                            <a href="teacher/login.php" class="btn btn-success btn-login w-100">Sign In</a>
                            <a href="teacher/register.php" class="btn btn-outline-success w-100">Register</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="card text-center h-100">
                        <div class="card-body">
                            <div class="role-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h5 class="card-title">Student</h5>
                            <a href="student/login.php" class="btn btn-info btn-login w-100">Sign In</a>
                            <a href="student/register.php" class="btn btn-outline-info w-100">Register</a>
                        </div>
                    </div>
                </div>
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
    <!-- Custom JS -->
    <script>
        // Auto close alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert-flash');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html> 