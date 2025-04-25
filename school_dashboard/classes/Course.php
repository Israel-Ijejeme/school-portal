<?php
require_once 'Database.php';

class Course {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get all courses
    public function getAllCourses() {
        $sql = "SELECT c.*, d.name as department_name, s.name as semester_name, u.full_name as teacher_name 
                FROM courses c 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN semesters s ON c.semester_id = s.id 
                LEFT JOIN users u ON c.teacher_id = u.id";
        
        $result = $this->db->query($sql);
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        return $courses;
    }
    
    // Get all courses with detailed information for admin display
    public function getAllCoursesWithDetails() {
        $sql = "SELECT c.*, d.name as department_name, u.full_name as teacher_name 
                FROM courses c 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN users u ON c.teacher_id = u.id
                ORDER BY c.id ASC";
        
        $result = $this->db->query($sql);
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        return $courses;
    }
    
    // Get the count of students enrolled in a course
    public function getEnrolledStudentsCount($course_id) {
        $sql = "SELECT COUNT(*) as count FROM student_courses WHERE course_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            return $row['count'];
        }
        
        return 0;
    }
    
    // Get course by ID
    public function getCourseById($id) {
        $sql = "SELECT c.*, d.name as department_name, s.name as semester_name, u.full_name as teacher_name 
                FROM courses c 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN semesters s ON c.semester_id = s.id 
                LEFT JOIN users u ON c.teacher_id = u.id 
                WHERE c.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Get courses by teacher ID
    public function getCoursesByTeacherId($teacher_id) {
        $sql = "SELECT c.*, d.name as department_name, s.name as semester_name 
                FROM courses c 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN semesters s ON c.semester_id = s.id 
                WHERE c.teacher_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        return $courses;
    }
    
    // Get courses by department ID
    public function getCoursesByDepartmentId($department_id) {
        $sql = "SELECT c.*, d.name as department_name, s.name as semester_name, u.full_name as teacher_name 
                FROM courses c 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN semesters s ON c.semester_id = s.id 
                LEFT JOIN users u ON c.teacher_id = u.id 
                WHERE c.department_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        return $courses;
    }
    
    // Get courses by semester ID
    public function getCoursesBySemesterId($semester_id) {
        $sql = "SELECT c.*, d.name as department_name, s.name as semester_name, u.full_name as teacher_name 
                FROM courses c 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN semesters s ON c.semester_id = s.id 
                LEFT JOIN users u ON c.teacher_id = u.id 
                WHERE c.semester_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        return $courses;
    }
    
    // Get courses registered by a student
    public function getStudentCourses($student_id) {
        $sql = "SELECT c.*, d.name as department_name, s.name as semester_name, u.full_name as teacher_name, sc.id as registration_id 
                FROM student_courses sc 
                JOIN courses c ON sc.course_id = c.id 
                LEFT JOIN departments d ON c.department_id = d.id 
                LEFT JOIN semesters s ON c.semester_id = s.id 
                LEFT JOIN users u ON c.teacher_id = u.id 
                WHERE sc.student_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $courses = [];
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        
        return $courses;
    }
    
    // Get students registered for a course
    public function getCourseStudents($course_id) {
        $sql = "SELECT u.*, d.name as department_name, sc.id as registration_id 
                FROM student_courses sc 
                JOIN users u ON sc.student_id = u.id 
                LEFT JOIN departments d ON u.department_id = d.id 
                WHERE sc.course_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        return $students;
    }
    
    // Add a new course
    public function addCourse($course_name, $course_code, $description, $credit_hours, $department_id, $teacher_id) {
        $sql = "INSERT INTO courses (course_name, course_code, description, credit_hours, department_id, teacher_id) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssiii", $course_name, $course_code, $description, $credit_hours, $department_id, $teacher_id);
        
        return $stmt->execute();
    }
    
    // Update a course
    public function updateCourse($id, $course_name, $course_code, $description, $credit_hours, $department_id, $teacher_id) {
        $sql = "UPDATE courses 
                SET course_name = ?, course_code = ?, description = ?, credit_hours = ?, department_id = ?, teacher_id = ? 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssiiii", $course_name, $course_code, $description, $credit_hours, $department_id, $teacher_id, $id);
        
        return $stmt->execute();
    }
    
    // Delete a course
    public function deleteCourse($id) {
        $sql = "DELETE FROM courses WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    // Register a student for a course
    public function registerStudentForCourse($student_id, $course_id, $semester_id) {
        // Check if already registered
        $check_sql = "SELECT id FROM student_courses WHERE student_id = ? AND course_id = ? AND semester_id = ?";
        $check_stmt = $this->db->prepare($check_sql);
        $check_stmt->bind_param("iii", $student_id, $course_id, $semester_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            return false; // Already registered
        }
        
        $sql = "INSERT INTO student_courses (student_id, course_id, semester_id) VALUES (?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $student_id, $course_id, $semester_id);
        
        return $stmt->execute();
    }
    
    // Unregister a student from a course
    public function unregisterStudentFromCourse($student_id, $course_id) {
        $sql = "DELETE FROM student_courses WHERE student_id = ? AND course_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $student_id, $course_id);
        
        return $stmt->execute();
    }
}
?> 