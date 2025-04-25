<?php
require_once '../classes/SessionManager.php';
SessionManager::startSession();

// Redirect to login if not logged in, unless we're already on login page
$current_page = basename($_SERVER['PHP_SELF']);
if (!SessionManager::isLoggedIn() && !in_array($current_page, ['index.php', 'login.php', 'register.php'])) {
    header('Location: ../index.php');
    exit;
}

// Get user type for navigation
$user_type = SessionManager::getUserTypeId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>School Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Version 2.0 - Forced sidebar layout */
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: row;
            width: 100%;
        }
        .row {
            width: 100%;
            margin: 0;
            display: flex;
            flex-wrap: nowrap;
        }
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            padding-top: 20px;
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            width: 250px;
            flex-shrink: 0;
            z-index: 100;
            border-right: 1px solid #dee2e6;
        }
        .content-area {
            flex: 1;
            padding: 20px;
            overflow-x: hidden;
            max-width: calc(100vw - 250px);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .nav-link {
            color: #333;
        }
        .nav-link:hover {
            background-color: #e9ecef;
        }
        .nav-link.active {
            background-color: #0d6efd;
            color: white;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .alert-flash {
            margin-top: 10px;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .main-content {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                top: 0;
                border-right: none;
                border-bottom: 1px solid #dee2e6;
            }
            .content-area {
                max-width: 100%;
                padding: 15px;
            }
        }
    </style>
</head>
<body id="school-dashboard-v2">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SessionManager::isLoggedIn() ? '../dashboard.php' : '../index.php'; ?>">
                School Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (SessionManager::isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i> <?php echo SessionManager::getUserName(); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <?php if (SessionManager::isAdmin()): ?>
                                    <li><a class="dropdown-item" href="../admin/profile.php">Profile</a></li>
                                <?php elseif (SessionManager::isTeacher()): ?>
                                    <li><a class="dropdown-item" href="../teacher/profile.php">Profile</a></li>
                                <?php elseif (SessionManager::isStudent()): ?>
                                    <li><a class="dropdown-item" href="../student/profile.php">Profile</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../index.php">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php if (SessionManager::isLoggedIn()): ?>
        <!-- Layout with sidebar for logged in users - simplified layout with forced side-by-side -->
        <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0; table-layout: fixed;">
            <tr>
                <!-- Sidebar -->
                <td style="width: 250px; vertical-align: top; background-color: #f8f9fa; border-right: 1px solid #dee2e6; padding: 0;">
                    <div class="sidebar" style="width: 250px; height: calc(100vh - 56px); overflow-y: auto; padding-top: 20px;">
                        <div class="list-group list-group-flush">
                            <?php if (SessionManager::isAdmin()): ?>
                                <!-- Admin Navigation -->
                                <a href="../admin/dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                                <a href="../admin/teachers.php" class="list-group-item list-group-item-action <?php echo $current_page == 'teachers.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-chalkboard-teacher me-2"></i> Manage Teachers
                                </a>
                                <a href="../admin/students.php" class="list-group-item list-group-item-action <?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-user-graduate me-2"></i> Manage Students
                                </a>
                                <a href="../admin/courses.php" class="list-group-item list-group-item-action <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-book me-2"></i> Manage Courses
                                </a>
                                <a href="../admin/departments.php" class="list-group-item list-group-item-action <?php echo $current_page == 'departments.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-building me-2"></i> Manage Departments
                                </a>
                            <?php elseif (SessionManager::isTeacher()): ?>
                                <!-- Teacher Navigation -->
                                <a href="../teacher/dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                                <a href="../teacher/courses.php" class="list-group-item list-group-item-action <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-book me-2"></i> My Courses
                                </a>
                                <a href="../teacher/students.php" class="list-group-item list-group-item-action <?php echo $current_page == 'students.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-user-graduate me-2"></i> Students
                                </a>
                                <a href="../teacher/grades.php" class="list-group-item list-group-item-action <?php echo $current_page == 'grades.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-bar me-2"></i> Grades
                                </a>
                                <a href="../teacher/profile.php" class="list-group-item list-group-item-action <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-user-circle me-2"></i> Profile
                                </a>
                            <?php elseif (SessionManager::isStudent()): ?>
                                <!-- Student Navigation -->
                                <a href="../student/dashboard.php" class="list-group-item list-group-item-action <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                                <a href="../student/courses.php" class="list-group-item list-group-item-action <?php echo $current_page == 'courses.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-book me-2"></i> My Courses
                                </a>
                                <a href="../student/register_courses.php" class="list-group-item list-group-item-action <?php echo $current_page == 'register_courses.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-plus-circle me-2"></i> Register Courses
                                </a>
                                <a href="../student/grades.php" class="list-group-item list-group-item-action <?php echo $current_page == 'grades.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-chart-bar me-2"></i> Grades & CGPA
                                </a>
                                <a href="../student/profile.php" class="list-group-item list-group-item-action <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                                    <i class="fas fa-user-circle me-2"></i> Profile
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                
                <!-- Main Content -->
                <td style="vertical-align: top; padding: 20px; width: calc(100% - 250px);">
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
                </td>
            </tr>
        </table>
    <?php else: ?>
        <!-- Layout without sidebar for non-logged in users -->
        <div class="container main-content">
            <div class="row">
                <div class="col-12 px-4 pt-3">
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
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 