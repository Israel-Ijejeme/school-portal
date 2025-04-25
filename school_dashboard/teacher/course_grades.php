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

// Process grade submission if form is submitted
if (Utility::isPostRequest()) {
    $success = true;
    $errorMessage = '';
    
    foreach ($_POST['scores'] as $student_id => $score) {
        // Skip empty scores
        if (trim($score) === '') continue;
        
        $score = floatval($score);
        
        // Validate score
        if ($score < 0 || $score > 100) {
            $success = false;
            $errorMessage = 'Scores must be between 0 and 100.';
            break;
        }
        
        // Set grade
        $result = $grade->setGrade($student_id, $course_id, $courseData['semester_id'], $score, $user_id);
        
        if (!$result) {
            $success = false;
            $errorMessage = 'Failed to save grades. Please try again.';
            break;
        }
    }
    
    if ($success) {
        SessionManager::setFlash('success', 'Grades have been saved successfully.');
        header('Location: course_grades.php?course_id=' . $course_id);
        exit;
    } else {
        SessionManager::setFlash('error', $errorMessage);
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Grades for <?php echo $courseData['course_code']; ?>: <?php echo $courseData['title']; ?></h1>
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
    
    <!-- Grades Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Manage Grades</h6>
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <p>No students are currently enrolled in this course.</p>
                </div>
            <?php else: ?>
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
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Score (0-100)</th>
                                    <th>Current Grade</th>
                                    <th>Grade Point</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): 
                                    // Check if student has a grade for this course
                                    $studentGrade = $grade->getGrade($student['id'], $course_id, $courseData['semester_id']);
                                    $hasGrade = $studentGrade !== false;
                                    $currentScore = $hasGrade ? $studentGrade['score'] : '';
                                    $currentGrade = $hasGrade ? $studentGrade['grade'] : '-';
                                    $currentGradePoint = $hasGrade ? $studentGrade['grade_point'] : '-';
                                ?>
                                    <tr>
                                        <td><?php echo $student['id']; ?></td>
                                        <td><?php echo $student['full_name']; ?></td>
                                        <td><?php echo $student['department_name']; ?></td>
                                        <td>
                                            <input type="number" class="form-control" name="scores[<?php echo $student['id']; ?>]" 
                                                   value="<?php echo $currentScore; ?>" min="0" max="100" step="0.01">
                                        </td>
                                        <td><?php echo $currentGrade; ?></td>
                                        <td><?php echo $currentGradePoint; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Grades
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 