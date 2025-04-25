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
$semesters = $semester->getAllSemesters();
$currentSemester = $semester->getCurrentSemester();

// Filter by semester if requested
$selected_semester_id = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : ($currentSemester ? $currentSemester['id'] : 0);

// Filter courses by semester if selected
if ($selected_semester_id > 0) {
    $filteredCourses = array_filter($teacherCourses, function($course) use ($selected_semester_id) {
        return $course['semester_id'] == $selected_semester_id;
    });
} else {
    $filteredCourses = $teacherCourses;
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">My Courses</h1>
    
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
    
    <!-- Courses Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">My Courses</h6>
        </div>
        <div class="card-body">
            <?php if (empty($filteredCourses)): ?>
                <div class="alert alert-info">
                    <?php if ($selected_semester_id > 0): ?>
                        <p>You are not assigned to any courses for the selected semester.</p>
                    <?php else: ?>
                        <p>You are not assigned to any courses.</p>
                    <?php endif; ?>
                    <p>Please contact the administrator to be assigned to courses.</p>
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
                                <th>Students</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredCourses as $courseData): 
                                // Get student count for this course
                                $students = $course->getCourseStudents($courseData['id']);
                                $studentCount = count($students);
                            ?>
                                <tr>
                                    <td><?php echo $courseData['course_code']; ?></td>
                                    <td><?php echo $courseData['title']; ?></td>
                                    <td><?php echo $courseData['department_name']; ?></td>
                                    <td><?php echo $courseData['semester_name']; ?></td>
                                    <td><?php echo $courseData['credit_units']; ?></td>
                                    <td><?php echo $studentCount; ?></td>
                                    <td>
                                        <a href="course_students.php?course_id=<?php echo $courseData['id']; ?>" class="btn btn-sm btn-primary mb-1">
                                            <i class="fas fa-users"></i> View Students
                                        </a>
                                        <a href="course_grades.php?course_id=<?php echo $courseData['id']; ?>" class="btn btn-sm btn-success mb-1">
                                            <i class="fas fa-chart-bar"></i> Manage Grades
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

<?php
// Include footer
include '../includes/footer.php';
?> 