<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Department.php';
require_once '../classes/Semester.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// Redirect if not logged in or not an admin
if (!SessionManager::isLoggedIn() || !SessionManager::isAdmin()) {
    SessionManager::setFlash('error', 'You must be logged in as an admin to access this page.');
    header('Location: login.php');
    exit;
}

// Get counts for dashboard stats
$user = new User();
$course = new Course();
$department = new Department();
$semester = new Semester();

$teacherCount = count($user->getUsersByType(2));
$studentCount = count($user->getUsersByType(3));
$courseCount = count($course->getAllCourses());
$departmentCount = count($department->getAllDepartments());

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #0d6efd;
            padding: 10px 0;
        }
        .navbar-brand {
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-left: 15px;
        }
        .layout-container {
            display: table;
            width: 100%;
            height: calc(100vh - 56px);
        }
        .sidebar {
            display: table-cell;
            width: 250px;
            background-color: #f8f9fa;
            vertical-align: top;
            border-right: 1px solid #dee2e6;
        }
        .content {
            display: table-cell;
            vertical-align: top;
            padding: 20px;
        }
        .list-group-item {
            border-radius: 0;
            border-left: none;
            border-right: none;
        }
        .list-group-item.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .alert {
            margin-bottom: 15px;
        }
        .btn-block {
            display: block;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard_fixed.php">School Dashboard</a>
            <div class="ms-auto">
                <div class="dropdown">
                    <a class="text-white dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false" style="text-decoration: none;">
                        <i class="fas fa-user-circle"></i> <?php echo SessionManager::getUserName(); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuLink">
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Layout Container with Table Layout -->
    <div class="layout-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="list-group list-group-flush">
                <a href="dashboard_fixed.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="teachers.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chalkboard-teacher me-2"></i> Manage Teachers
                </a>
                <a href="students.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-graduate me-2"></i> Manage Students
                </a>
                <a href="courses.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-book me-2"></i> Manage Courses
                </a>
                <a href="departments.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-building me-2"></i> Manage Departments
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content">
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
            
            <h1 class="mb-4">Admin Dashboard</h1>
            
            <div class="row">
                <!-- Stats Cards -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Teachers</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $teacherCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Students</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $studentCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Courses</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $courseCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Departments</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $departmentCount; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-building fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Quick Access -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Access</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <a href="teachers.php" class="btn btn-primary btn-block">
                                        <i class="fas fa-chalkboard-teacher me-2"></i> Manage Teachers
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <a href="students.php" class="btn btn-success btn-block">
                                        <i class="fas fa-user-graduate me-2"></i> Manage Students
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <a href="courses.php" class="btn btn-info btn-block">
                                        <i class="fas fa-book me-2"></i> Manage Courses
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <a href="departments.php" class="btn btn-warning btn-block">
                                        <i class="fas fa-building me-2"></i> Manage Departments
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <a href="../logout.php" class="btn btn-danger btn-block">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5>Current Semester</h5>
                                <?php
                                $currentSemester = $semester->getCurrentSemester();
                                if ($currentSemester) {
                                    echo '<p class="mb-0">' . $currentSemester['name'] . '</p>';
                                } else {
                                    echo '<p class="mb-0 text-danger">No active semester set</p>';
                                }
                                ?>
                            </div>
                            <div class="mb-3">
                                <h5>Admin Information</h5>
                                <p class="mb-0">Name: <?php echo SessionManager::getUserName(); ?></p>
                                <p class="mb-0">Email: <?php echo SessionManager::getUserEmail(); ?></p>
                            </div>
                            <div class="mb-0">
                                <h5>System Details</h5>
                                <p class="mb-0">Nigerian Education System</p>
                                <p class="mb-0">Maximum CGPA: 5.0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-auto">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © 2023 School Dashboard - Nigerian Education System
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto close alert messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert-flash');
                alerts.forEach(function(alert) {
                    if (bootstrap.Alert) {
                        var bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    } else {
                        alert.style.display = 'none';
                    }
                });
            }, 5000);
            
            console.log('Admin Dashboard - Standalone version loaded');
        });
    </script>
</body>
</html> 