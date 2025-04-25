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

// Check if course_id and student_id are provided
if (!isset($_GET['course_id']) || empty($_GET['course_id']) || !isset($_GET['student_id']) || empty($_GET['student_id'])) {
    SessionManager::setFlash('error', 'Course ID and Student ID are required.');
    header('Location: courses.php');
    exit;
}

$course_id = intval($_GET['course_id']);
$student_id = intval($_GET['student_id']);

// Initialize classes
$course = new Course();
$grade = new Grade();
$user = new User();

// Get course details
$courseData = $course->getCourseById($course_id);

// Check if course exists and belongs to the teacher
if (!$courseData || $courseData['teacher_id'] != $user_id) {
    SessionManager::setFlash('error', 'You are not authorized to grade students in this course or the course does not exist.');
    header('Location: courses.php');
    exit;
}

// Get student details
$studentData = $user->getUserById($student_id);

// Check if student exists and is enrolled in the course
$students = $course->getCourseStudents($course_id);
$student_enrolled = false;

foreach ($students as $student) {
    if ($student['id'] == $student_id) {
        $student_enrolled = true;
        break;
    }
}

if (!$studentData || !$student_enrolled) {
    SessionManager::setFlash('error', 'Student not found or not enrolled in this course.');
    header('Location: course_students.php?course_id=' . $course_id);
    exit;
}

// Get existing grade if any
$existingGrade = $grade->getGrade($student_id, $course_id, $courseData['semester_id']);

// Process grade submission
if (Utility::isPostRequest()) {
    // Validate score
    $score = isset($_POST['score']) ? floatval($_POST['score']) : 0;
    
    if ($score < 0 || $score > 100) {
        SessionManager::setFlash('error', 'Score must be between 0 and 100.');
    } else {
        // Save grade
        $result = $grade->setGrade($student_id, $course_id, $courseData['semester_id'], $score, $user_id);
        
        if ($result) {
            SessionManager::setFlash('success', 'Grade has been saved successfully.');
            header('Location: course_students.php?course_id=' . $course_id);
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to save grade. Please try again.');
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?php echo $existingGrade ? 'Edit' : 'Add'; ?> Grade for <?php echo $studentData['full_name']; ?></h1>
        <a href="course_students.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Students
        </a>
    </div>
    
    <!-- Course and Student Details Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Course Information</h5>
                    <p><strong>Course Code:</strong> <?php echo $courseData['course_code']; ?></p>
                    <p><strong>Title:</strong> <?php echo $courseData['title']; ?></p>
                    <p><strong>Department:</strong> <?php echo $courseData['department_name']; ?></p>
                    <p><strong>Semester:</strong> <?php echo $courseData['semester_name']; ?></p>
                    <p><strong>Credit Units:</strong> <?php echo $courseData['credit_units']; ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Student Information</h5>
                    <p><strong>ID:</strong> <?php echo $studentData['id']; ?></p>
                    <p><strong>Name:</strong> <?php echo $studentData['full_name']; ?></p>
                    <p><strong>Email:</strong> <?php echo $studentData['email']; ?></p>
                    <p><strong>Department:</strong> <?php echo $studentData['department_name']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Grade Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $existingGrade ? 'Edit' : 'Add'; ?> Grade</h6>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <p><strong>Nigerian Grading System:</strong></p>
                <ul class="mb-0">
                    <li>70-100: A (5.0 points)</li>
                    <li>60-69: B (4.0 points)</li>
                    <li>50-59: C (3.0 points)</li>
                    <li>45-49: D (2.0 points)</li>
                    <li>40-44: E (1.0 points)</li>
                    <li>0-39: F (0.0 points)</li>
                </ul>
            </div>
            
            <form method="POST" action="">
                <div class="form-group mb-3">
                    <label for="score">Score (0-100):</label>
                    <input type="number" class="form-control" id="score" name="score" 
                           min="0" max="100" step="0.01" required
                           value="<?php echo $existingGrade ? $existingGrade['score'] : ''; ?>">
                </div>
                
                <?php if ($existingGrade): ?>
                <div class="form-group mb-3">
                    <label>Current Grade:</label>
                    <div class="form-control" readonly><?php echo $existingGrade['grade']; ?></div>
                </div>
                
                <div class="form-group mb-3">
                    <label>Current Grade Point:</label>
                    <div class="form-control" readonly><?php echo $existingGrade['grade_point']; ?></div>
                </div>
                
                <div class="form-group mb-3">
                    <label>Last Graded By:</label>
                    <div class="form-control" readonly><?php echo $existingGrade['grader_name']; ?></div>
                </div>
                
                <div class="form-group mb-3">
                    <label>Last Graded At:</label>
                    <div class="form-control" readonly><?php echo Utility::formatDate($existingGrade['graded_at'], 'd M, Y H:i'); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> <?php echo $existingGrade ? 'Update' : 'Save'; ?> Grade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 