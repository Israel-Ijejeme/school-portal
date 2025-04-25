<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Department.php';
require_once '../classes/Course.php';
require_once '../classes/Grade.php';
require_once '../classes/Utility.php';

SessionManager::startSession();

// Redirect if not logged in or not an admin
if (!SessionManager::isLoggedIn() || !SessionManager::isAdmin()) {
    SessionManager::setFlash('error', 'You must be logged in as an admin to access this page.');
    header('Location: login.php');
    exit;
}

// Initialize classes
$user = new User();
$department = new Department();
$course = new Course();
$grade = new Grade();

// Get all departments
$departments = $department->getAllDepartments();

// Get all students
$students = $user->getUsersByType(3); // 3 = student type

// Handle delete request
if (Utility::isPostRequest() && isset($_POST['delete_student']) && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    
    // Delete the student
    $result = $user->deleteUser($student_id);
    
    if ($result) {
        SessionManager::setFlash('success', 'Student deleted successfully.');
    } else {
        SessionManager::setFlash('error', 'Failed to delete student. The student may have associated courses or grades.');
    }
    
    // Refresh the page
    header('Location: students.php');
    exit;
}

// Handle add/edit student
if (Utility::isPostRequest() && isset($_POST['save_student'])) {
    $student_id = isset($_POST['student_id']) ? intval($_POST['student_id']) : 0;
    $full_name = Utility::sanitizeInput($_POST['full_name']);
    $email = Utility::sanitizeInput($_POST['email']);
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $department_id = intval($_POST['department_id']);
    $age = intval($_POST['age']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!Utility::validateEmail($email)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if ($student_id == 0 && empty($password)) {
        $errors[] = 'Password is required for new students.';
    }
    
    if ($department_id <= 0) {
        $errors[] = 'Please select a department.';
    }
    
    if ($age <= 0) {
        $errors[] = 'Please enter a valid age.';
    }
    
    // If no errors, add/update student
    if (empty($errors)) {
        if ($student_id > 0) {
            // Update existing student
            $result = $user->updateProfile($student_id, $full_name, $email, $age, $department_id);
            $message = 'Student updated successfully.';
        } else {
            // Add new student
            $result = $user->register($full_name, $email, $password, 3, $department_id, $age);
            $message = 'Student added successfully.';
        }
        
        if ($result) {
            SessionManager::setFlash('success', $message);
            header('Location: students.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to save student. Email may already be in use.');
        }
    } else {
        // Set error messages
        foreach ($errors as $error) {
            SessionManager::setFlash('error', $error);
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Students</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
            <i class="fas fa-plus-circle"></i> Add New Student
        </button>
    </div>
    
    <!-- Students Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Students (<?php echo count($students); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="alert alert-info">
                    <p>No students found.</p>
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
                                <th>Age</th>
                                <th>CGPA</th>
                                <th>Courses</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): 
                                // Get student's courses
                                $studentCourses = $course->getStudentCourses($student['id']);
                                $courseCount = count($studentCourses);
                                
                                // Get student's CGPA
                                $cgpa = $grade->calculateCGPA($student['id']);
                            ?>
                                <tr>
                                    <td><?php echo $student['id']; ?></td>
                                    <td><?php echo $student['full_name']; ?></td>
                                    <td><?php echo $student['email']; ?></td>
                                    <td><?php echo $student['department_name']; ?></td>
                                    <td><?php echo $student['age']; ?></td>
                                    <td><?php echo number_format($cgpa, 2); ?></td>
                                    <td><?php echo $courseCount; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary mb-1 edit-student" 
                                                data-id="<?php echo $student['id']; ?>"
                                                data-name="<?php echo $student['full_name']; ?>"
                                                data-email="<?php echo $student['email']; ?>"
                                                data-department="<?php echo $student['department_id']; ?>"
                                                data-age="<?php echo $student['age']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#editStudentModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="delete_student" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </form>
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

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStudentModalLabel">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="department_id" class="form-label">Department</label>
                        <select class="form-control" id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="age" name="age" min="16" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_student" class="btn btn-primary">Save Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStudentModalLabel">Edit Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_student_id" name="student_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="edit_full_name" name="full_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Password (Leave blank to keep current password)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_department_id" class="form-label">Department</label>
                        <select class="form-control" id="edit_department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_age" class="form-label">Age</label>
                        <input type="number" class="form-control" id="edit_age" name="age" min="16" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_student" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set edit form values when edit button is clicked
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-student');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const department = this.getAttribute('data-department');
            const age = this.getAttribute('data-age');
            
            document.getElementById('edit_student_id').value = id;
            document.getElementById('edit_full_name').value = name;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_department_id').value = department;
            document.getElementById('edit_age').value = age;
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?> 