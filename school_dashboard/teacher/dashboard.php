<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Semester.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// Redirect if not logged in or not a teacher
if (!SessionManager::isLoggedIn() || !SessionManager::isTeacher()) {
    SessionManager::setFlash('error', 'You must be logged in as a teacher to access this page.');
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = SessionManager::getUserId();
$user = new User();
$userData = $user->getUserById($user_id);

// Get teacher's courses
$course = new Course();
$teacherCourses = $course->getCoursesByTeacherId($user_id);

// Get semester info
$semester = new Semester();
$currentSemester = $semester->getCurrentSemester();

// Get counts for dashboard stats
$coursesCount = count($teacherCourses);

// Count students across all courses
$studentsCount = 0;
foreach ($teacherCourses as $courseData) {
    $courseStudents = $course->getCourseStudents($courseData['id']);
    $studentsCount += count($courseStudents);
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Teacher Dashboard</h1>
    
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                My Courses</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $coursesCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                My Students</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $studentsCount; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Current Semester</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo $currentSemester ? $currentSemester['name'] : 'N/A'; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- My Courses -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">My Courses</h6>
                    <a href="courses.php" class="btn btn-primary btn-sm">View All</a>
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
    
    <!-- Teacher Information -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Teacher Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <h5>Personal Details</h5>
                            <p><strong>Name:</strong> <?php echo $userData['full_name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $userData['email']; ?></p>
                            <p><strong>Age:</strong> <?php echo $userData['age']; ?></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h5>Academic Details</h5>
                            <p><strong>Department:</strong> <?php echo $userData['department_name']; ?></p>
                            <p><strong>Courses Assigned:</strong> <?php echo $coursesCount; ?></p>
                            <p><strong>Students:</strong> <?php echo $studentsCount; ?></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h5>Quick Links</h5>
                            <a href="courses.php" class="btn btn-primary btn-sm mb-2 d-block">My Courses</a>
                            <a href="students.php" class="btn btn-success btn-sm mb-2 d-block">My Students</a>
                            <a href="grades.php" class="btn btn-info btn-sm mb-2 d-block">Manage Grades</a>
                            <a href="profile.php" class="btn btn-secondary btn-sm mb-2 d-block">Edit Profile</a>
                            <a href="../logout.php" class="btn btn-danger btn-sm d-block">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 