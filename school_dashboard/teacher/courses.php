<?php
require_once '../classes/SessionManager.php';
require_once '../classes/Course.php';

SessionManager::startSession();

// Redirect if not logged in or not a teacher
if (!SessionManager::isLoggedIn() || SessionManager::getUserTypeId() != 2) {
    SessionManager::setFlash('error', 'You must be logged in as a teacher to access this page.');
    header('Location: ../login.php');
    exit;
}

// Fetch courses assigned to the teacher
$course = new Course();
$teacherCourses = $course->getCoursesByTeacherId(SessionManager::getUserId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
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
            <a class="navbar-brand" href="dashboard.php">Teacher Dashboard</a>
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

    <!-- Layout Container -->
    <div class="layout-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a href="courses.php" class="list-group-item list-group-item-action active">
                    <i class="fas fa-book me-2"></i> My Courses
                </a>
                <a href="students.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-graduate me-2"></i> My Students
                </a>
                <a href="grades.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chart-bar me-2"></i> Manage Grades
                </a>
                
                <a href="profile.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user me-2"></i> Profile
                </a>
                <a href="../logout.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="content">
            <h1 class="mb-4">My Courses</h1>
            
            <!-- Courses Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Courses Assigned to Me</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($teacherCourses)): ?>
                        <p class="text-center">You are not assigned to any courses.</p>
                        <p class="text-center">Please contact the administrator to be assigned to courses.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Department</th>
                                        <th>Semester</th>
                                        <th>Credit Units</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($teacherCourses as $courseData): ?>
                                        <tr>
                                            <td><?php echo $courseData['course_code']; ?></td>
                                            <td><?php echo $courseData['title']; ?></td>
                                            <td><?php echo $courseData['department_name']; ?></td>
                                            <td><?php echo $courseData['semester_name']; ?></td>
                                            <td><?php echo $courseData['credit_units']; ?></td>
                                            <td>
                                                <a href="course_students.php?course_id=<?php echo $courseData['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-users"></i> Students
                                                </a>
                                                <a href="course_grades.php?course_id=<?php echo $courseData['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-chart-bar"></i> Grades
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-light text-center text-lg-start mt-auto">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.05);">
            © 2023 Teacher Dashboard - Nigerian Education System
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>