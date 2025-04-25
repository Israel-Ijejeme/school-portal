<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Grade.php';
require_once '../classes/Semester.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// Redirect if not logged in or not a student
if (!SessionManager::isLoggedIn() || !SessionManager::isStudent()) {
    SessionManager::setFlash('error', 'You must be logged in as a student to access this page.');
    header('Location: login.php');
    exit;
}

// Get user data
$user_id = SessionManager::getUserId();
$user = new User();
$userData = $user->getUserById($user_id);

// Get student's courses
$course = new Course();
$studentCourses = $course->getStudentCourses($user_id);

// Get semester info
$semester = new Semester();
$currentSemester = $semester->getCurrentSemester();

// Get grades and calculate CGPA
$grade = new Grade();
$studentGrades = $grade->getStudentGrades($user_id);
$cgpa = $grade->calculateCGPA($user_id);

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Student Dashboard</h1>
    
    <div class="row">
        <!-- Stats Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Courses Enrolled</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo count($studentCourses); ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
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
                                CGPA</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($cgpa, 2); ?>/5.0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Classification</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo Utility::getCGPAClassification($cgpa); ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-medal fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Courses -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">My Courses</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($studentCourses)): ?>
                        <p class="text-center">You are not enrolled in any courses.</p>
                        <div class="text-center">
                            <a href="register_courses.php" class="btn btn-primary">Register Courses</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Credit Units</th>
                                        <th>Teacher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($studentCourses, 0, 5) as $course): ?>
                                        <tr>
                                            <td><?php echo $course['course_code']; ?></td>
                                            <td><?php echo $course['title']; ?></td>
                                            <td><?php echo $course['credit_units']; ?></td>
                                            <td><?php echo $course['teacher_name']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (count($studentCourses) > 5): ?>
                                <div class="text-center">
                                    <a href="courses.php" class="btn btn-primary btn-sm">View All Courses</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Grades -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Grades</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($studentGrades)): ?>
                        <p class="text-center">No grades available yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Course</th>
                                        <th>Score</th>
                                        <th>Grade</th>
                                        <th>Grade Point</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($studentGrades, 0, 5) as $grade): ?>
                                        <tr>
                                            <td><?php echo $grade['course_code']; ?></td>
                                            <td><?php echo $grade['score']; ?></td>
                                            <td><?php echo $grade['grade']; ?></td>
                                            <td><?php echo $grade['grade_point']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php if (count($studentGrades) > 5): ?>
                                <div class="text-center">
                                    <a href="grades.php" class="btn btn-primary btn-sm">View All Grades</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Student Information -->
    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Student Information</h6>
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
                            <p><strong>Current CGPA:</strong> <?php echo number_format($cgpa, 2); ?>/5.0</p>
                            <p><strong>Classification:</strong> <?php echo Utility::getCGPAClassification($cgpa); ?></p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <h5>Quick Links</h5>
                            <a href="register_courses.php" class="btn btn-primary btn-sm mb-2 d-block">Register Courses</a>
                            <a href="grades.php" class="btn btn-info btn-sm mb-2 d-block">View Grades & CGPA</a>
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