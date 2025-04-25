<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Grade.php';
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

// Initialize classes
$course = new Course();
$grade = new Grade();
$semester = new Semester();

// Get teacher's courses
$teacherCourses = $course->getCoursesByTeacherId($user_id);

// Get all semesters
$semesters = $semester->getAllSemesters();
$currentSemester = $semester->getCurrentSemester();

// Filter by semester if requested
$selected_semester_id = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : ($currentSemester ? $currentSemester['id'] : 0);
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Filter courses by semester if selected
if ($selected_semester_id > 0) {
    $filteredCourses = array_filter($teacherCourses, function($course) use ($selected_semester_id) {
        return $course['semester_id'] == $selected_semester_id;
    });
} else {
    $filteredCourses = $teacherCourses;
}

// Get grades for the selected course or all courses
$gradesData = [];
if ($selected_course_id > 0) {
    // Get course details
    $selectedCourse = null;
    foreach ($filteredCourses as $c) {
        if ($c['id'] == $selected_course_id) {
            $selectedCourse = $c;
            break;
        }
    }
    
    if ($selectedCourse) {
        $courseGrades = $grade->getCourseGrades($selected_course_id, $selectedCourse['semester_id']);
        $gradesData[] = [
            'course' => $selectedCourse,
            'grades' => $courseGrades
        ];
    }
} else {
    // Get grades for all filtered courses
    foreach ($filteredCourses as $c) {
        $courseGrades = $grade->getCourseGrades($c['id'], $c['semester_id']);
        
        // Only include courses with grades
        if (!empty($courseGrades)) {
            $gradesData[] = [
                'course' => $c,
                'grades' => $courseGrades
            ];
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">Manage Grades</h1>
    
    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Grades</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="grades.php" class="form-inline">
                <div class="row">
                    <div class="col-md-5">
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
                    <div class="col-md-5">
                        <div class="form-group mb-2">
                            <label for="course_id" class="me-2">Course:</label>
                            <select name="course_id" id="course_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">All Courses</option>
                                <?php foreach ($filteredCourses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php echo $selected_course_id == $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo $c['course_code']; ?>: <?php echo $c['title']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Apply Filters</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (empty($gradesData)): ?>
        <div class="alert alert-info">
            <p>No grades found for the selected filters.</p>
            <p>You may need to grade students first or select different filter options.</p>
        </div>
    <?php else: ?>
        <?php foreach ($gradesData as $data): ?>
            <!-- Grades Table for Each Course -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php echo $data['course']['course_code']; ?>: <?php echo $data['course']['title']; ?> 
                        (<?php echo $data['course']['semester_name']; ?>)
                    </h6>
                    <div>
                        <a href="course_grades.php?course_id=<?php echo $data['course']['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Grades
                        </a>
                        <a href="course_students.php?course_id=<?php echo $data['course']['id']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-users"></i> View Students
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                    <th>Grade Point</th>
                                    <th>Graded At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['grades'] as $g): ?>
                                    <tr>
                                        <td><?php echo $g['student_id']; ?></td>
                                        <td><?php echo $g['student_name']; ?></td>
                                        <td><?php echo $g['score']; ?></td>
                                        <td>
                                            <span class="badge <?php echo ($g['grade'] == 'F') ? 'bg-danger' : (($g['grade'] == 'A') ? 'bg-success' : 'bg-primary'); ?>">
                                                <?php echo $g['grade']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $g['grade_point']; ?></td>
                                        <td><?php echo Utility::formatDate($g['graded_at']); ?></td>
                                        <td>
                                            <a href="grade_student.php?student_id=<?php echo $g['student_id']; ?>&course_id=<?php echo $data['course']['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 