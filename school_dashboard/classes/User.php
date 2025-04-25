<?php
require_once 'Database.php';

class User {
    private $db;
    private $id;
    private $full_name;
    private $email;
    private $user_type_id;
    private $department_id;
    private $age;
    private $profile_picture;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Login method
    public function login($email, $password) {
        $email = $this->db->escapeString($email);
        
        $sql = "SELECT id, full_name, email, password, user_type_id, department_id, age, profile_picture 
                FROM users 
                WHERE email = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set user properties
                $this->id = $user['id'];
                $this->full_name = $user['full_name'];
                $this->email = $user['email'];
                $this->user_type_id = $user['user_type_id'];
                $this->department_id = $user['department_id'];
                $this->age = $user['age'];
                $this->profile_picture = $user['profile_picture'];
                
                // Start session
                session_start();
                $_SESSION['user_id'] = $this->id;
                $_SESSION['full_name'] = $this->full_name;
                $_SESSION['email'] = $this->email;
                $_SESSION['user_type_id'] = $this->user_type_id;
                
                return true;
            }
        }
        
        return false;
    }
    
    // Register method for students and teachers
    public function register($full_name, $email, $password, $user_type_id, $department_id, $age) {
        // Check if email already exists
        $email = $this->db->escapeString($email);
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $this->db->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            return false; // Email already exists
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (full_name, email, password, user_type_id, department_id, age) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssiii", $full_name, $email, $hashed_password, $user_type_id, $department_id, $age);
        
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
    
    // Get user by ID
    public function getUserById($id) {
        $sql = "SELECT u.*, d.name as department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Update user profile
    public function updateProfile($id, $full_name, $email, $age, $department_id) {
        $sql = "UPDATE users 
                SET full_name = ?, email = ?, age = ?, department_id = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssiii", $full_name, $email, $age, $department_id, $id);
        
        return $stmt->execute();
    }
    
    // Update profile picture
    public function updateProfilePicture($id, $profile_picture) {
        $sql = "UPDATE users SET profile_picture = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $profile_picture, $id);
        
        return $stmt->execute();
    }
    
    // Get all users by user type
    public function getUsersByType($user_type_id) {
        $sql = "SELECT u.*, d.name as department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.user_type_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $user_type_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    // Delete user
    public function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getFullName() {
        return $this->full_name;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getUserTypeId() {
        return $this->user_type_id;
    }
    
    public function getDepartmentId() {
        return $this->department_id;
    }
    
    public function getAge() {
        return $this->age;
    }
    
    public function getProfilePicture() {
        return $this->profile_picture;
    }
    
    // Check if user is admin
    public function isAdmin() {
        return $this->user_type_id == 1;
    }
    
    // Check if user is teacher
    public function isTeacher() {
        return $this->user_type_id == 2;
    }
    
    // Check if user is student
    public function isStudent() {
        return $this->user_type_id == 3;
    }
    
    // Get all users in a department
    public function getUsersByDepartment($department_id) {
        $sql = "SELECT u.*, d.name as department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.department_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    // Get users by type and department
    public function getUsersByTypeAndDepartment($user_type_id, $department_id) {
        $sql = "SELECT u.*, d.name as department_name 
                FROM users u 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE u.user_type_id = ? AND u.department_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $user_type_id, $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        return $users;
    }
    
    // Update user's department
    public function updateUserDepartment($user_id, $department_id) {
        $sql = "UPDATE users SET department_id = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $department_id, $user_id);
        
        return $stmt->execute();
    }
}
?> 