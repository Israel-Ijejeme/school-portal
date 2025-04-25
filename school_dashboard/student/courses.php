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
$semesters = $semester->getAllSemesters();
$currentSemester = $semester->getCurrentSemester();

// Get grades
$grade = new Grade();

// Filter by semester if requested
$selected_semester_id = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : ($currentSemester ? $currentSemester['id'] : 0);

// Filter courses by semester if selected
if ($selected_semester_id > 0) {
    $filteredCourses = array_filter($studentCourses, function($course) use ($selected_semester_id) {
        return $course['semester_id'] == $selected_semester_id;
    });
} else {
    $filteredCourses = $studentCourses;
}

// Handle course unregister
if (Utility::isPostRequest() && isset($_POST['unregister']) && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    
    // Check if the course is in the current semester
    $canUnregister = false;
    foreach ($studentCourses as $c) {
        if ($c['id'] == $course_id && $c['semester_id'] == $currentSemester['id']) {
            $canUnregister = true;
            break;
        }
    }
    
    if ($canUnregister) {
        $result = $course->unregisterStudentFromCourse($user_id, $course_id);
        
        if ($result) {
            SessionManager::setFlash('success', 'Successfully unregistered from the course.');
            header('Location: courses.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to unregister from the course. Please try again.');
        }
    } else {
        SessionManager::setFlash('error', 'You can only unregister from courses in the current semester.');
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Courses</h1>
        <a href="register_courses.php" class="btn btn-primary">
            <i class="fas fa-plus-circle"></i> Register New Courses
        </a>
    </div>
    
    <!-- Semester Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Courses</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="courses.php" class="form-inline">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label for="semester_id" class="me-2">Semester:</label>
                            <select name="semester_id" id="semester_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">All Semesters</option>
                                <?php foreach ($semesters as $sem): ?>
                                    <option value="<?php echo $sem['id']; ?>" <?php echo $selected_semester_id == $sem['id'] ? 'selected' : ''; ?>>
                                        <?php echo $sem['name']; ?> <?php echo $sem['is_current'] ? '(Current)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Courses List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Enrolled Courses (<?php echo count($filteredCourses); ?>)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($filteredCourses)): ?>
                <div class="alert alert-info">
                    <p>You are not enrolled in any courses for the selected semester.</p>
                    <?php if ($selected_semester_id == ($currentSemester ? $currentSemester['id'] : 0)): ?>
                        <p>Click the "Register New Courses" button to register for courses.</p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Semester</th>
                                <th>Credit Units</th>
                                <th>Teacher</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredCourses as $c): 
                                // Get grade for this course if any
                                $courseGrade = $grade->getGrade($user_id, $c['id'], $c['semester_id']);
                                $hasGrade = $courseGrade !== false;
                            ?>
                                <tr>
                                    <td><?php echo $c['course_code']; ?></td>
                                    <td><?php echo $c['title']; ?></td>
                                    <td><?php echo $c['department_name']; ?></td>
                                    <td>
                                        <?php echo $c['semester_name']; ?>
                                        <?php if ($currentSemester && $c['semester_id'] == $currentSemester['id']): ?>
                                            <span class="badge bg-primary">Current</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $c['credit_units']; ?></td>
                                    <td><?php echo $c['teacher_name']; ?></td>
                                    <td>
                                        <?php if ($hasGrade): ?>
                                            <span class="badge <?php echo ($courseGrade['grade'] == 'F') ? 'bg-danger' : (($courseGrade['grade'] == 'A') ? 'bg-success' : 'bg-primary'); ?>">
                                                <?php echo $courseGrade['grade']; ?> (<?php echo $courseGrade['score']; ?>%)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Not Graded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($currentSemester && $c['semester_id'] == $currentSemester['id'] && !$hasGrade): ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" name="unregister" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Are you sure you want to unregister from this course?');">
                                                    <i class="fas fa-times-circle"></i> Unregister
                                                </button>
                                            </form>
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
    
    <!-- Credit Units Summary -->
    <?php if (!empty($filteredCourses)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Credit Units Summary</h6>
            </div>
            <div class="card-body">
                <?php
                $totalCredits = 0;
                foreach ($filteredCourses as $c) {
                    $totalCredits += $c['credit_units'];
                }
                ?>
                <p><strong>Total Credit Units:</strong> <?php echo $totalCredits; ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 