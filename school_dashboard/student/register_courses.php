<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Semester.php';
require_once '../classes/Department.php';
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

// Initialize classes
$course = new Course();
$semester = new Semester();
$department = new Department();

// Get current semester
$currentSemester = $semester->getCurrentSemester();
if (!$currentSemester) {
    SessionManager::setFlash('error', 'No current semester is set. Please contact the administrator.');
    header('Location: dashboard.php');
    exit;
}

// Get student's department
$studentDepartment = $department->getDepartmentById($userData['department_id']);

// Get all departments for filtering
$allDepartments = $department->getAllDepartments();

// Get courses already registered by the student
$registeredCourses = $course->getStudentCourses($user_id);
$registeredCourseIds = [];
foreach ($registeredCourses as $rc) {
    if ($rc['semester_id'] == $currentSemester['id']) {
        $registeredCourseIds[] = $rc['id'];
    }
}

// Filter by department if requested
$selected_department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

// Get available courses for the current semester
$availableCourses = $course->getCoursesBySemesterId($currentSemester['id']);

// Filter courses by department if selected
if ($selected_department_id > 0) {
    $filteredCourses = array_filter($availableCourses, function($course) use ($selected_department_id) {
        return $course['department_id'] == $selected_department_id;
    });
} else {
    $filteredCourses = $availableCourses;
}

// Handle course registration
if (Utility::isPostRequest() && isset($_POST['register']) && isset($_POST['course_id'])) {
    $course_id = intval($_POST['course_id']);
    
    // Check if course is in the list of available courses
    $courseValid = false;
    foreach ($filteredCourses as $c) {
        if ($c['id'] == $course_id && !in_array($c['id'], $registeredCourseIds)) {
            $courseValid = true;
            break;
        }
    }
    
    if ($courseValid) {
        $result = $course->registerStudentForCourse($user_id, $course_id, $currentSemester['id']);
        
        if ($result) {
            SessionManager::setFlash('success', 'Successfully registered for the course.');
            header('Location: register_courses.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to register for the course. You may already be registered.');
        }
    } else {
        SessionManager::setFlash('error', 'Invalid course selection or you are already registered for this course.');
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Register Courses for <?php echo $currentSemester['name']; ?></h1>
        <a href="courses.php" class="btn btn-primary">
            <i class="fas fa-book"></i> My Courses
        </a>
    </div>
    
    <!-- Department Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Courses by Department</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="register_courses.php" class="form-inline">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group mb-2">
                            <label for="department_id" class="me-2">Department:</label>
                            <select name="department_id" id="department_id" class="form-control" onchange="this.form.submit()">
                                <option value="0">All Departments</option>
                                <?php foreach ($allDepartments as $dept): ?>
                                    <option value="<?php echo $dept['id']; ?>" <?php echo $selected_department_id == $dept['id'] ? 'selected' : ''; ?>>
                                        <?php echo $dept['name']; ?>
                                        <?php echo $userData['department_id'] == $dept['id'] ? ' (My Department)' : ''; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Registered Courses -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                My Registered Courses for <?php echo $currentSemester['name']; ?>
            </h6>
        </div>
        <div class="card-body">
            <?php
            $currentSemesterCourses = array_filter($registeredCourses, function($c) use ($currentSemester) {
                return $c['semester_id'] == $currentSemester['id'];
            });
            ?>
            
            <?php if (empty($currentSemesterCourses)): ?>
                <div class="alert alert-info">
                    <p>You are not registered for any courses in the current semester.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Credit Units</th>
                                <th>Teacher</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($currentSemesterCourses as $c): ?>
                                <tr>
                                    <td><?php echo $c['course_code']; ?></td>
                                    <td><?php echo $c['title']; ?></td>
                                    <td><?php echo $c['department_name']; ?></td>
                                    <td><?php echo $c['credit_units']; ?></td>
                                    <td><?php echo $c['teacher_name']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php
                $totalRegisteredCredits = 0;
                foreach ($currentSemesterCourses as $c) {
                    $totalRegisteredCredits += $c['credit_units'];
                }
                ?>
                <div class="mt-3">
                    <p><strong>Total Registered Credit Units:</strong> <?php echo $totalRegisteredCredits; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Available Courses -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Available Courses for Registration
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($filteredCourses)): ?>
                <div class="alert alert-info">
                    <p>No courses available for the selected filter.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Credit Units</th>
                                <th>Teacher</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredCourses as $c): ?>
                                <tr>
                                    <td><?php echo $c['course_code']; ?></td>
                                    <td><?php echo $c['title']; ?></td>
                                    <td>
                                        <?php echo $c['department_name']; ?>
                                        <?php if ($c['department_id'] == $userData['department_id']): ?>
                                            <span class="badge bg-primary">My Department</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $c['credit_units']; ?></td>
                                    <td><?php echo $c['teacher_name']; ?></td>
                                    <td>
                                        <?php if (in_array($c['id'], $registeredCourseIds)): ?>
                                            <span class="badge bg-success">Already Registered</span>
                                        <?php else: ?>
                                            <form method="POST" action="" style="display:inline;">
                                                <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
                                                <button type="submit" name="register" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-plus-circle"></i> Register
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
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 