<?php
require_once 'Database.php';

class Grade {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Add or update a grade for a student's course
    public function setGrade($student_id, $course_id, $semester_id, $score, $graded_by) {
        // Calculate grade and grade point based on score (Nigerian grading system)
        $grade = '';
        $grade_point = 0;
        
        if ($score >= 70) {
            $grade = 'A';
            $grade_point = 5.0;
        } elseif ($score >= 60) {
            $grade = 'B';
            $grade_point = 4.0;
        } elseif ($score >= 50) {
            $grade = 'C';
            $grade_point = 3.0;
        } elseif ($score >= 45) {
            $grade = 'D';
            $grade_point = 2.0;
        } elseif ($score >= 40) {
            $grade = 'E';
            $grade_point = 1.0;
        } else {
            $grade = 'F';
            $grade_point = 0.0;
        }
        
        // Check if grade already exists
        $check_sql = "SELECT id FROM grades WHERE student_id = ? AND course_id = ? AND semester_id = ?";
        $check_stmt = $this->db->prepare($check_sql);
        $check_stmt->bind_param("iii", $student_id, $course_id, $semester_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing grade
            $grade_id = $check_result->fetch_assoc()['id'];
            $sql = "UPDATE grades 
                    SET score = ?, grade = ?, grade_point = ?, graded_by = ?, graded_at = NOW() 
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("dsdii", $score, $grade, $grade_point, $graded_by, $grade_id);
        } else {
            // Insert new grade
            $sql = "INSERT INTO grades (student_id, course_id, semester_id, score, grade, grade_point, graded_by) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iiidsdi", $student_id, $course_id, $semester_id, $score, $grade, $grade_point, $graded_by);
        }
        
        return $stmt->execute();
    }
    
    // Get a grade by student, course, and semester
    public function getGrade($student_id, $course_id, $semester_id) {
        $sql = "SELECT g.*, c.course_code, c.title, c.credit_units, u.full_name as grader_name 
                FROM grades g 
                JOIN courses c ON g.course_id = c.id 
                JOIN users u ON g.graded_by = u.id 
                WHERE g.student_id = ? AND g.course_id = ? AND g.semester_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $student_id, $course_id, $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Get all grades for a student
    public function getStudentGrades($student_id) {
        $sql = "SELECT g.*, c.course_code, c.title, c.credit_units, s.name as semester_name, u.full_name as grader_name 
                FROM grades g 
                JOIN courses c ON g.course_id = c.id 
                JOIN semesters s ON g.semester_id = s.id 
                JOIN users u ON g.graded_by = u.id 
                WHERE g.student_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
        
        return $grades;
    }
    
    // Get student grades by semester
    public function getStudentGradesBySemester($student_id, $semester_id) {
        $sql = "SELECT g.*, c.course_code, c.title, c.credit_units, u.full_name as grader_name 
                FROM grades g 
                JOIN courses c ON g.course_id = c.id 
                JOIN users u ON g.graded_by = u.id 
                WHERE g.student_id = ? AND g.semester_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $student_id, $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
        
        return $grades;
    }
    
    // Get all grades for a course
    public function getCourseGrades($course_id, $semester_id) {
        $sql = "SELECT g.*, u.full_name as student_name, u.id as student_id 
                FROM grades g 
                JOIN users u ON g.student_id = u.id 
                WHERE g.course_id = ? AND g.semester_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $course_id, $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $grades = [];
        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }
        
        return $grades;
    }
    
    // Calculate GPA for a student in a semester
    public function calculateSemesterGPA($student_id, $semester_id) {
        $sql = "SELECT g.grade_point, c.credit_units 
                FROM grades g 
                JOIN courses c ON g.course_id = c.id 
                WHERE g.student_id = ? AND g.semester_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $student_id, $semester_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_points = 0;
        $total_units = 0;
        
        while ($row = $result->fetch_assoc()) {
            $total_points += ($row['grade_point'] * $row['credit_units']);
            $total_units += $row['credit_units'];
        }
        
        if ($total_units > 0) {
            return round($total_points / $total_units, 2);
        }
        
        return 0;
    }
    
    // Calculate CGPA for a student
    public function calculateCGPA($student_id) {
        $sql = "SELECT g.grade_point, c.credit_units 
                FROM grades g 
                JOIN courses c ON g.course_id = c.id 
                WHERE g.student_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $total_points = 0;
        $total_units = 0;
        
        while ($row = $result->fetch_assoc()) {
            $total_points += ($row['grade_point'] * $row['credit_units']);
            $total_units += $row['credit_units'];
        }
        
        if ($total_units > 0) {
            return round($total_points / $total_units, 2);
        }
        
        return 0;
    }
    
    // Delete a grade
    public function deleteGrade($grade_id) {
        $sql = "DELETE FROM grades WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $grade_id);
        
        return $stmt->execute();
    }
}
?> 