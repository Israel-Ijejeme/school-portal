<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Grade.php';
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

// Check if course_id is provided
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    SessionManager::setFlash('error', 'Course ID is required.');
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['course_id']);

// Initialize classes
$course = new Course();
$grade = new Grade();

// Get course details
$courseData = $course->getCourseById($course_id);

// Check if course exists and belongs to the teacher
if (!$courseData || $courseData['teacher_id'] != $user_id) {
    SessionManager::setFlash('error', 'You are not authorized to view this course or the course does not exist.');
    header('Location: courses.php');
    exit;
}

// Get students enrolled in the course
$students = $course->getCourseStudents($course_id);

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Students in <?php echo $courseData['course_code']; ?>: <?php echo $courseData['title']; ?></h1>
        <a href="courses.php" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Courses
        </a>
    </div>
    
    <!-- Course Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Course Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Course Code:</strong> <?php echo $courseData['course_code']; ?></p>
                    <p><strong>Title:</strong> <?php echo $courseData['title']; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Department:</strong> <?php echo $courseData['department_name']; ?></p>
                    <p><strong>Semester:</strong> <?php echo $courseData['semester_name']; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Credit Units:</strong> <?php echo $courseData['credit_units']; ?></p>
                    <p><strong>Students Enrolled:</strong> <?php echo count($students); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Students Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Enrolled Students</h6>
            <a href="course_grades.php?course_id=<?php echo $course_id; ?>" class="btn btn-success btn-sm">
                <i class="fas fa-chart-bar"></i> Manage Grades
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <p>No students are currently enrolled in this course.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Grade Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): 
                                // Check if student has a grade for this course
                                $studentGrade = $grade->getGrade($student['id'], $course_id, $courseData['semester_id']);
                                $hasGrade = $studentGrade !== false;
                            ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo $student['full_name']; ?></td>
                                    <td><?php echo $student['email']; ?></td>
                                    <td><?php echo $student['department_name']; ?></td>
                                    <td>
                                        <?php if ($hasGrade): ?>
                                            <span class="badge bg-success">Graded (<?php echo $studentGrade['grade']; ?> - <?php echo $studentGrade['score']; ?>%)</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Not Graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasGrade): ?>
                                            <a href="grade_student.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit Grade
                                            </a>
                                        <?php else: ?>
                                            <a href="grade_student.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $course_id; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-plus-circle"></i> Add Grade
                                            </a>
                                        <?php endif; ?>
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

<?php
// Include footer
include '../includes/footer.php';
?> 