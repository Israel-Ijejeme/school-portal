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

// Initialize classes
$course = new Course();
$semester = new Semester();

// Get teacher's courses
$teacherCourses = $course->getCoursesByTeacherId($user_id);

// Get current semester
$currentSemester = $semester->getCurrentSemester();

// Get all students from teacher's courses (avoid duplicates)
$allStudents = [];
foreach ($teacherCourses as $courseData) {
    $students = $course->getCourseStudents($courseData['id']);
    
    foreach ($students as $student) {
        // Use student ID as key to avoid duplicates
        if (!isset($allStudents[$student['id']])) {
            $allStudents[$student['id']] = $student;
            $allStudents[$student['id']]['courses'] = [];
        }
        
        // Add course to student's courses
        $allStudents[$student['id']]['courses'][] = [
            'id' => $courseData['id'],
            'code' => $courseData['course_code'],
            'title' => $courseData['title'],
            'semester_id' => $courseData['semester_id'],
            'semester_name' => $courseData['semester_name']
        ];
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">My Students</h1>
    
    <!-- Students Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Students (<?php echo count($allStudents); ?> total)
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($allStudents)): ?>
                <div class="alert alert-info">
                    <p>You do not have any students enrolled in your courses.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Department</th>
                                <th>Courses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allStudents as $student): ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo $student['full_name']; ?></td>
                                    <td><?php echo $student['email']; ?></td>
                                    <td><?php echo $student['department_name']; ?></td>
                                    <td>
                                        <?php foreach ($student['courses'] as $course): ?>
                                            <div>
                                                <a href="course_students.php?course_id=<?php echo $course['id']; ?>">
                                                    <?php echo $course['code']; ?>: <?php echo $course['title']; ?>
                                                </a>
                                                <?php if ($currentSemester && $course['semester_id'] == $currentSemester['id']): ?>
                                                    <span class="badge bg-primary">Current</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        // Show links for current semester courses
                                        $currentSemesterCourses = array_filter($student['courses'], function($c) use ($currentSemester) {
                                            return $currentSemester && $c['semester_id'] == $currentSemester['id'];
                                        });
                                        
                                        foreach ($currentSemesterCourses as $course): 
                                        ?>
                                            <a href="grade_student.php?student_id=<?php echo $student['id']; ?>&course_id=<?php echo $course['id']; ?>" 
                                               class="btn btn-sm btn-success mb-1">
                                                <i class="fas fa-edit"></i> Grade (<?php echo $course['code']; ?>)
                                            </a>
                                        <?php endforeach; ?>
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