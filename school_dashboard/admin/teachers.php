<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Department.php';
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

// Get all departments
$departments = $department->getAllDepartments();

// Get all teachers
$teachers = $user->getUsersByType(2); // 2 = teacher type

// Handle delete request
if (Utility::isPostRequest() && isset($_POST['delete_teacher']) && isset($_POST['teacher_id'])) {
    $teacher_id = intval($_POST['teacher_id']);
    
    // Delete the teacher
    $result = $user->deleteUser($teacher_id);
    
    if ($result) {
        SessionManager::setFlash('success', 'Teacher deleted successfully.');
    } else {
        SessionManager::setFlash('error', 'Failed to delete teacher. The teacher may have associated courses or grades.');
    }
    
    // Refresh the page
    header('Location: teachers.php');
    exit;
}

// Handle add/edit teacher
if (Utility::isPostRequest() && isset($_POST['save_teacher'])) {
    $teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
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
    
    if ($teacher_id == 0 && empty($password)) {
        $errors[] = 'Password is required for new teachers.';
    }
    
    if ($department_id <= 0) {
        $errors[] = 'Please select a department.';
    }
    
    if ($age <= 0) {
        $errors[] = 'Please enter a valid age.';
    }
    
    // If no errors, add/update teacher
    if (empty($errors)) {
        if ($teacher_id > 0) {
            // Update existing teacher
            $result = $user->updateProfile($teacher_id, $full_name, $email, $age, $department_id);
            $message = 'Teacher updated successfully.';
        } else {
            // Add new teacher
            $result = $user->register($full_name, $email, $password, 2, $department_id, $age);
            $message = 'Teacher added successfully.';
        }
        
        if ($result) {
            SessionManager::setFlash('success', $message);
            header('Location: teachers.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to save teacher. Email may already be in use.');
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
        <h1>Manage Teachers</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            <i class="fas fa-plus-circle"></i> Add New Teacher
        </button>
    </div>
    
    <!-- Teachers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Teachers (<?php echo count($teachers); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($teachers)): ?>
                <div class="alert alert-info">
                    <p>No teachers found.</p>
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
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr>
                                    <td><?php echo $teacher['id']; ?></td>
                                    <td><?php echo $teacher['full_name']; ?></td>
                                    <td><?php echo $teacher['email']; ?></td>
                                    <td><?php echo $teacher['department_name']; ?></td>
                                    <td><?php echo $teacher['age']; ?></td>
                                    <td><?php echo Utility::formatDate($teacher['created_at']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary edit-teacher" 
                                                data-id="<?php echo $teacher['id']; ?>"
                                                data-name="<?php echo $teacher['full_name']; ?>"
                                                data-email="<?php echo $teacher['email']; ?>"
                                                data-department="<?php echo $teacher['department_id']; ?>"
                                                data-age="<?php echo $teacher['age']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#editTeacherModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                            <button type="submit" name="delete_teacher" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.');">
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

<!-- Add Teacher Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-labelledby="addTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTeacherModalLabel">Add New Teacher</h5>
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
                        <input type="number" class="form-control" id="age" name="age" min="18" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_teacher" class="btn btn-primary">Save Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Teacher Modal -->
<div class="modal fade" id="editTeacherModal" tabindex="-1" aria-labelledby="editTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTeacherModalLabel">Edit Teacher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_teacher_id" name="teacher_id">
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
                        <input type="number" class="form-control" id="edit_age" name="age" min="18" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_teacher" class="btn btn-primary">Update Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set edit form values when edit button is clicked
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-teacher');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const email = this.getAttribute('data-email');
            const department = this.getAttribute('data-department');
            const age = this.getAttribute('data-age');
            
            document.getElementById('edit_teacher_id').value = id;
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