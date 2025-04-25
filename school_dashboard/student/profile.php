<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
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

// Get departments for the dropdown
$department = new Department();
$departments = $department->getAllDepartments();

// Handle profile update
if (Utility::isPostRequest() && isset($_POST['update_profile'])) {
    $full_name = Utility::sanitizeInput($_POST['full_name']);
    $email = Utility::sanitizeInput($_POST['email']);
    $age = isset($_POST['age']) ? intval($_POST['age']) : 0;
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    
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
    
    if ($age <= 0) {
        $errors[] = 'Please enter a valid age.';
    }
    
    if ($department_id <= 0) {
        $errors[] = 'Please select a department.';
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $result = $user->updateProfile($user_id, $full_name, $email, $age, $department_id);
        
        if ($result) {
            SessionManager::setFlash('success', 'Profile updated successfully.');
            header('Location: profile.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to update profile. Please try again.');
        }
    } else {
        // Set error messages
        foreach ($errors as $error) {
            SessionManager::setFlash('error', $error);
        }
    }
}

// Handle profile picture upload
if (Utility::isPostRequest() && isset($_POST['update_picture']) && isset($_FILES['profile_picture'])) {
    // Create uploads directory if it doesn't exist
    $uploads_dir = '../assets/uploads/profile_pictures';
    if (!file_exists($uploads_dir)) {
        mkdir($uploads_dir, 0777, true);
    }
    
    // Upload the file
    $upload_result = Utility::uploadFile($_FILES['profile_picture'], $uploads_dir, ['jpg', 'jpeg', 'png'], 2097152);
    
    if ($upload_result['success']) {
        // Update profile picture in database
        $profile_picture = $upload_result['filename'];
        $result = $user->updateProfilePicture($user_id, $profile_picture);
        
        if ($result) {
            SessionManager::setFlash('success', 'Profile picture updated successfully.');
            header('Location: profile.php');
            exit;
        } else {
            SessionManager::setFlash('error', 'Failed to update profile picture in database. Please try again.');
        }
    } else {
        SessionManager::setFlash('error', $upload_result['message']);
    }
}

// Refresh user data
$userData = $user->getUserById($user_id);

// Include header
include '../includes/header.php';
?>

<div class="container-fluid">
    <h1 class="mt-4 mb-4">My Profile</h1>
    
    <div class="row">
        <!-- Profile Picture Card -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Profile Picture</h6>
                </div>
                <div class="card-body text-center">
                    <img class="img-fluid rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;"
                         src="<?php echo '../assets/uploads/profile_pictures/' . $userData['profile_picture']; ?>" 
                         alt="Profile Picture">
                    
                    <h5 class="mb-3"><?php echo $userData['full_name']; ?></h5>
                    <p class="text-muted mb-1"><?php echo $userData['department_name']; ?> Student</p>
                    
                    <!-- Profile Picture Upload Form -->
                    <form method="POST" action="" enctype="multipart/form-data" class="mt-3">
                        <div class="form-group mb-3">
                            <label for="profile_picture">Change Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" required accept=".jpg,.jpeg,.png">
                            <small class="form-text text-muted">Max size: 2MB. Allowed formats: JPG, JPEG, PNG.</small>
                        </div>
                        <button type="submit" name="update_picture" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload New Picture
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Profile Details Card -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Personal Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="full_name">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo $userData['full_name']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $userData['email']; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="age">Age</label>
                                    <input type="number" class="form-control" id="age" name="age" 
                                           value="<?php echo $userData['age']; ?>" required min="16">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="department_id">Department</label>
                                    <select class="form-control" id="department_id" name="department_id" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?php echo $dept['id']; ?>" <?php echo $userData['department_id'] == $dept['id'] ? 'selected' : ''; ?>>
                                                <?php echo $dept['name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Account Type</label>
                                    <input type="text" class="form-control" value="Student" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label>Joined Date</label>
                                    <input type="text" class="form-control" 
                                           value="<?php echo Utility::formatDate($userData['created_at']); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-success">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Academic Information Card -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Academic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Student ID</label>
                                <input type="text" class="form-control" value="<?php echo $userData['id']; ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label>Department</label>
                                <input type="text" class="form-control" value="<?php echo $userData['department_name']; ?>" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="dashboard.php" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                        </a>
                        <a href="courses.php" class="btn btn-info">
                            <i class="fas fa-book"></i> My Courses
                        </a>
                        <a href="grades.php" class="btn btn-success">
                            <i class="fas fa-chart-bar"></i> My Grades
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?> 