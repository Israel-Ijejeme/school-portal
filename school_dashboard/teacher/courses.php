<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Attendance.php';
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

// Initialize classes
$course = new Course();
$attendance = new Attendance();

// Check if course_id is provided
if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);

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

    // Process attendance submission if form is submitted
    if (Utility::isPostRequest()) {
        $success = true;
        $errorMessage = '';

        foreach ($_POST['attendance'] as $student_id => $attendance_percentage) {
            // Skip empty attendance percentages
            if (trim($attendance_percentage) === '') continue;

            $attendance_percentage = floatval($attendance_percentage);

            // Validate attendance percentage
            if ($attendance_percentage < 0 || $attendance_percentage > 100) {
                $success = false;
                $errorMessage = 'Attendance percentages must be between 0 and 100.';
                break;
            }

            // Set attendance
            $result = $attendance->setAttendance($student_id, $course_id, $attendance_percentage, $user_id);

            if (!$result) {
                $success = false;
                $errorMessage = 'Failed to save attendance. Please try again.';
                break;
            }
        }

        if ($success) {
            SessionManager::setFlash('success', 'Attendance has been saved successfully.');
            header('Location: courses.php?course_id=' . $course_id);
            exit;
        } else {
            SessionManager::setFlash('error', $errorMessage);
        }
    }
} else {
    // If no course_id is provided, display a list of courses
    $courses = $course->getCoursesByTeacherId($user_id);
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <?php if (isset($course_id)): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Attendance for <?php echo $courseData['course_code']; ?>: <?php echo $courseData['title']; ?></h1>
            <div>
                <a href="course_students.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary me-2">
                    <i class="fas fa-users"></i> View Students
                </a>
                <a href="courses.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Courses
                </a>
            </div>
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
        
        <!-- Attendance Form -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Manage Attendance</h6>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-info">
                        <p>No students are currently enrolled in this course.</p>
                    </div>
                <?php else: ?>
                    <form method="POST" action="">
                        <div class="table-responsive">
                            <table class="table table-bordered" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Student ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Attendance (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): 
                                        // Check if student has attendance for this course
                                        $studentAttendance = $attendance->getAttendance($student['id'], $course_id);
                                        $currentAttendance = $studentAttendance !== false ? $studentAttendance['percentage'] : '';
                                    ?>
                                        <tr>
                                            <td><?php echo $student['id']; ?></td>
                                            <td><?php echo $student['full_name']; ?></td>
                                            <td><?php echo $student['department_name']; ?></td>
                                            <td>
                                                <input type="number" class="form-control" name="attendance[<?php echo $student['id']; ?>]" 
                                                       value="<?php echo $currentAttendance; ?>" min="0" max="100" step="0.01">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Save Attendance
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <h1 class="mb-4">My Courses</h1>
        <div class="row">
            <?php foreach ($courses as $course): ?>
                <div class="col-md-4">
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h5><?php echo $course['title']; ?></h5>
                            <p><strong>Course Code:</strong> <?php echo $course['course_code']; ?></p>
                            <p><strong>Department:</strong> <?php echo $course['department_name']; ?></p>
                            <a href="courses.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Manage Attendance
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>