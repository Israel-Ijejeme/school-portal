<?php
require_once 'Database.php';

class Attendance {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Set attendance for a student in a course.
     *
     * @param int $student_id
     * @param int $course_id
     * @param float $percentage
     * @param int $teacher_id
     * @return bool
     */
    public function setAttendance($student_id, $course_id, $percentage, $teacher_id) {
        $query = "INSERT INTO attendance (student_id, course_id, percentage, teacher_id, created_at)
                  VALUES (:student_id, :course_id, :percentage, :teacher_id, NOW())
                  ON DUPLICATE KEY UPDATE percentage = :percentage";
        $this->db->query($query);
        $this->db->bind(':student_id', $student_id);
        $this->db->bind(':course_id', $course_id);
        $this->db->bind(':percentage', $percentage);
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->execute();
    }

    /**
     * Get attendance for a student in a course.
     *
     * @param int $student_id
     * @param int $course_id
     * @return array|false
     */
    
    public function getAttendance($student_id, $course_id) {
        $query = "SELECT percentage FROM attendance WHERE student_id = ? AND course_id = ?";
        $this->db->query($query);
        $this->db->bind(1, $student_id);
        $this->db->bind(2, $course_id);
        return $this->db->single();
    }

    /**
     * Get all attendance records for a course.
     *
     * @param int $course_id
     * @return array
     */
    public function getAttendanceByCourse($course_id) {
        $query = "SELECT a.student_id, a.percentage, u.full_name, u.department_name
                  FROM attendance a
                  JOIN users u ON a.student_id = u.id
                  WHERE a.course_id = :course_id";
        $this->db->query($query);
        $this->db->bind(':course_id', $course_id);
        return $this->db->resultSet();
    }

    /**
     * Get all attendance records for a student.
     *
     * @param int $student_id
     * @return array
     */
    public function getAttendanceByStudent($student_id) {
        $query = "SELECT a.course_id, a.percentage, c.title AS course_title, c.course_code
                  FROM attendance a
                  JOIN courses c ON a.course_id = c.id
                  WHERE a.student_id = :student_id";
        $this->db->query($query);
        $this->db->bind(':student_id', $student_id);
        return $this->db->resultSet();
    }
}