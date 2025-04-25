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

// Initialize classes
$grade = new Grade();
$semester = new Semester();
$course = new Course();

// Get all semesters
$semesters = $semester->getAllSemesters();
$currentSemester = $semester->getCurrentSemester();

// Filter by semester if requested
$selected_semester_id = isset($_GET['semester_id']) ? intval($_GET['semester_id']) : 0;

// Get all student's grades
$allGrades = $grade->getStudentGrades($user_id);

// Calculate overall CGPA
$cgpa = $grade->calculateCGPA($user_id);
$classification = Utility::getCGPAClassification($cgpa);

// Filter grades by semester if selected
if ($selected_semester_id > 0) {
    $filteredGrades = array_filter($allGrades, function($g) use ($selected_semester_id) {
        return $g['semester_id'] == $selected_semester_id;
    });
    
    // Calculate semester GPA
    $semesterGPA = $grade->calculateSemesterGPA($user_id, $selected_semester_id);
    $semesterClassification = Utility::getCGPAClassification($semesterGPA);
} else {
    $filteredGrades = $allGrades;
}

// Check if all registered courses have been graded
$studentCourses = $course->getStudentCourses($user_id);
$allGraded = true;
$totalCourses = count($studentCourses);
$gradedCourses = count($allGrades);

if ($totalCourses > $gradedCourses) {
    $allGraded = false;
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>My Grades & CGPA</h1>
        <div>
            <button id="printButton" class="btn btn-success me-2">
                <i class="fas fa-print"></i> Print Results
            </button>
            <a href="courses.php" class="btn btn-primary">
                <i class="fas fa-book"></i> My Courses
            </a>
        </div>
    </div>
    
    <!-- CGPA Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Cumulative Grade Point Average (CGPA)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($cgpa, 2); ?>/5.0</div>
                            <div class="small mt-2"><?php echo $classification; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($selected_semester_id > 0): ?>
        <div class="col-lg-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Semester Grade Point Average (GPA)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?php echo number_format($semesterGPA, 2); ?>/5.0
                                <?php 
                                foreach ($semesters as $sem) {
                                    if ($sem['id'] == $selected_semester_id) {
                                        echo " - " . $sem['name'];
                                        break;
                                    }
                                }
                                ?>
                            </div>
                            <div class="small mt-2"><?php echo $semesterClassification; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if (!$allGraded): ?>
    <div class="alert alert-warning mb-4">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Note:</strong> Not all of your registered courses have been graded yet. Your CGPA may change when all grades are submitted.
    </div>
    <?php endif; ?>
    
    <!-- Semester Filter -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Grades by Semester</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="grades.php" class="form-inline">
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
    
    <!-- Grades Table -->
    <div class="card shadow mb-4" id="printableArea">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Grade Report
                <?php if ($selected_semester_id > 0): 
                    foreach ($semesters as $sem) {
                        if ($sem['id'] == $selected_semester_id) {
                            echo " - " . $sem['name'];
                            break;
                        }
                    }
                endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <!-- Student Info -->
            <div class="row mb-4 border-bottom pb-3">
                <div class="col-md-4">
                    <p><strong>Name:</strong> <?php echo $userData['full_name']; ?></p>
                    <p><strong>ID:</strong> <?php echo $userData['id']; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Department:</strong> <?php echo $userData['department_name']; ?></p>
                    <p><strong>Email:</strong> <?php echo $userData['email']; ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>CGPA:</strong> <?php echo number_format($cgpa, 2); ?>/5.0</p>
                    <p><strong>Classification:</strong> <?php echo $classification; ?></p>
                </div>
            </div>
            
            <?php if (empty($filteredGrades)): ?>
                <div class="alert alert-info">
                    <p>No grades available for the selected semester.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Title</th>
                                <th>Credit Units</th>
                                <th>Score</th>
                                <th>Grade</th>
                                <th>Grade Point</th>
                                <th>Quality Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalCreditUnits = 0;
                            $totalQualityPoints = 0;
                            
                            foreach ($filteredGrades as $g): 
                                $qualityPoints = $g['grade_point'] * $g['credit_units'];
                                $totalCreditUnits += $g['credit_units'];
                                $totalQualityPoints += $qualityPoints;
                            ?>
                                <tr>
                                    <td><?php echo $g['course_code']; ?></td>
                                    <td><?php echo $g['title']; ?></td>
                                    <td><?php echo $g['credit_units']; ?></td>
                                    <td><?php echo $g['score']; ?></td>
                                    <td>
                                        <span class="badge <?php echo ($g['grade'] == 'F') ? 'bg-danger' : (($g['grade'] == 'A') ? 'bg-success' : 'bg-primary'); ?>">
                                            <?php echo $g['grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $g['grade_point']; ?></td>
                                    <td><?php echo number_format($qualityPoints, 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-dark">
                                <th colspan="2">Total</th>
                                <th><?php echo $totalCreditUnits; ?></th>
                                <th colspan="3">GPA: <?php echo ($totalCreditUnits > 0) ? number_format($totalQualityPoints / $totalCreditUnits, 2) : '0.00'; ?></th>
                                <th><?php echo number_format($totalQualityPoints, 2); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
            
            <!-- Nigerian Grading System Reference -->
            <div class="mt-4 small">
                <h6>Nigerian Grading System</h6>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Score Range</th>
                            <th>Grade</th>
                            <th>Grade Point</th>
                            <th>Classification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>70-100</td>
                            <td>A</td>
                            <td>5.0</td>
                            <td rowspan="2">First Class</td>
                        </tr>
                        <tr>
                            <td>60-69</td>
                            <td>B</td>
                            <td>4.0</td>
                        </tr>
                        <tr>
                            <td>50-59</td>
                            <td>C</td>
                            <td>3.0</td>
                            <td>Second Class Lower</td>
                        </tr>
                        <tr>
                            <td>45-49</td>
                            <td>D</td>
                            <td>2.0</td>
                            <td>Third Class</td>
                        </tr>
                        <tr>
                            <td>40-44</td>
                            <td>E</td>
                            <td>1.0</td>
                            <td>Pass</td>
                        </tr>
                        <tr>
                            <td>0-39</td>
                            <td>F</td>
                            <td>0.0</td>
                            <td>Fail</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Print Script -->
<script>
document.getElementById('printButton').addEventListener('click', function() {
    var printContents = document.getElementById('printableArea').innerHTML;
    var originalContents = document.body.innerHTML;
    
    document.body.innerHTML = '<div class="container mt-4">' + printContents + '</div>';
    
    window.print();
    
    document.body.innerHTML = originalContents;
    
    // Reattach event listeners after printing
    document.getElementById('printButton').addEventListener('click', arguments.callee);
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?> 