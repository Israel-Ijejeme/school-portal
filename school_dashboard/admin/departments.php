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

// Handle delete department request
if (Utility::isPostRequest() && isset($_POST['delete_department']) && isset($_POST['department_id'])) {
    $department_id = intval($_POST['department_id']);
    
    // Check if there are teachers or students in this department
    $teachersInDept = $user->getUsersByDepartment($department_id);
    
    if (count($teachersInDept) > 0) {
        SessionManager::setFlash('error', 'Cannot delete department. There are teachers assigned to this department.');
    } else {
        // Delete the department
        $result = $department->deleteDepartment($department_id);
        
        if ($result) {
            SessionManager::setFlash('success', 'Department deleted successfully.');
        } else {
            SessionManager::setFlash('error', 'Failed to delete department. The department may have courses associated with it.');
        }
    }
    
    // Refresh the page
    header('Location: departments.php');
    exit;
}

// Handle add/edit department
if (Utility::isPostRequest() && isset($_POST['save_department'])) {
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $name = Utility::sanitizeInput($_POST['name']);
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Department name is required.';
    }
    
    // If no errors, add/update department
    if (empty($errors)) {
        if ($department_id > 0) {
            // Update existing department
            $result = $department->updateDepartment($department_id, $name);
            $message = 'Department updated successfully.';
        } else {
            // Add new department
            $result = $department->addDepartment($name);
            $message = 'Department added successfully.';
        }
        
        if ($result) {
            SessionManager::setFlash('success', $message);
            header('Location: departments.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to save department. The name may already be in use.');
        }
    } else {
        // Set error messages
        foreach ($errors as $error) {
            SessionManager::setFlash('error', $error);
        }
    }
}

// Handle assign teacher to department
if (Utility::isPostRequest() && isset($_POST['assign_teacher'])) {
    $teacher_id = intval($_POST['teacher_id']);
    $department_id = intval($_POST['teacher_department_id']);
    
    // Update teacher's department
    $result = $user->updateUserDepartment($teacher_id, $department_id);
    
    if ($result) {
        SessionManager::setFlash('success', 'Teacher assigned to department successfully.');
    } else {
        SessionManager::setFlash('error', 'Failed to assign teacher to department.');
    }
    
    // Refresh the page
    header('Location: departments.php');
    exit;
}

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Manage Departments</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
            <i class="fas fa-plus-circle"></i> Add New Department
        </button>
    </div>
    
    <!-- Departments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Departments (<?php echo count($departments); ?>)</h6>
        </div>
        <div class="card-body">
            <?php if (empty($departments)): ?>
                <div class="alert alert-info">
                    <p>No departments found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Teachers</th>
                                <th>Students</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): 
                                // Get teachers count
                                $teachersCount = count($user->getUsersByTypeAndDepartment(2, $dept['id']));
                                
                                // Get students count
                                $studentsCount = count($user->getUsersByTypeAndDepartment(3, $dept['id']));
                            ?>
                                <tr>
                                    <td><?php echo $dept['id']; ?></td>
                                    <td><?php echo $dept['name']; ?></td>
                                    <td><?php echo $teachersCount; ?></td>
                                    <td><?php echo $studentsCount; ?></td>
                                    <td><?php echo Utility::formatDate($dept['created_at']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary mb-1 edit-department" 
                                                data-id="<?php echo $dept['id']; ?>"
                                                data-name="<?php echo $dept['name']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#editDepartmentModal">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <form method="POST" action="" style="display:inline;">
                                            <input type="hidden" name="department_id" value="<?php echo $dept['id']; ?>">
                                            <button type="submit" name="delete_department" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this department? This action cannot be undone.');">
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
    
    <!-- Teachers by Department Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Teachers by Department</h6>
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#assignTeacherModal">
                <i class="fas fa-user-plus"></i> Assign Teacher
            </button>
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
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary assign-teacher" 
                                                data-id="<?php echo $teacher['id']; ?>"
                                                data-name="<?php echo $teacher['full_name']; ?>"
                                                data-department="<?php echo $teacher['department_id']; ?>"
                                                data-bs-toggle="modal" data-bs-target="#assignTeacherModal">
                                            <i class="fas fa-exchange-alt"></i> Change Department
                                        </button>
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

<!-- Add Department Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_department" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Department Modal -->
<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" id="edit_department_id" name="department_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_department" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assignTeacherModal" tabindex="-1" aria-labelledby="assignTeacherModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="assignTeacherModalLabel">Assign Teacher to Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Teacher</label>
                        <select class="form-control" id="teacher_id" name="teacher_id" required>
                            <option value="">Select Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['id']; ?>"><?php echo $teacher['full_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_department_id" class="form-label">Department</label>
                        <select class="form-control" id="teacher_department_id" name="teacher_department_id" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="assign_teacher" class="btn btn-primary">Assign Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set edit form values when edit button is clicked
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-department');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            document.getElementById('edit_department_id').value = id;
            document.getElementById('edit_name').value = name;
        });
    });
    
    // Set assign teacher form values
    const assignButtons = document.querySelectorAll('.assign-teacher');
    
    assignButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const department = this.getAttribute('data-department');
            
            document.getElementById('teacher_id').value = id;
            document.getElementById('teacher_department_id').value = department;
        });
    });
});
</script>

<?php
// Include footer
include '../includes/footer.php';
?> 