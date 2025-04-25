<?php
require_once 'Database.php';

class Semester {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get all semesters
    public function getAllSemesters() {
        $sql = "SELECT * FROM semesters ORDER BY id";
        
        $result = $this->db->query($sql);
        
        $semesters = [];
        while ($row = $result->fetch_assoc()) {
            $semesters[] = $row;
        }
        
        return $semesters;
    }
    
    // Get semester by ID
    public function getSemesterById($id) {
        $sql = "SELECT * FROM semesters WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Get current semester
    public function getCurrentSemester() {
        $sql = "SELECT * FROM semesters WHERE is_current = 1 LIMIT 1";
        
        $result = $this->db->query($sql);
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Set current semester
    public function setCurrentSemester($semester_id) {
        // First, unset all current semesters
        $reset_sql = "UPDATE semesters SET is_current = 0";
        $this->db->query($reset_sql);
        
        // Then set the new current semester
        $sql = "UPDATE semesters SET is_current = 1 WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $semester_id);
        
        return $stmt->execute();
    }
    
    // Add a new semester
    public function addSemester($name, $is_current = 0) {
        // If new semester is current, unset all others
        if ($is_current) {
            $reset_sql = "UPDATE semesters SET is_current = 0";
            $this->db->query($reset_sql);
        }
        
        $sql = "INSERT INTO semesters (name, is_current) VALUES (?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $name, $is_current);
        
        return $stmt->execute();
    }
    
    // Update a semester
    public function updateSemester($id, $name, $is_current = 0) {
        // If updated semester is current, unset all others
        if ($is_current) {
            $reset_sql = "UPDATE semesters SET is_current = 0";
            $this->db->query($reset_sql);
        }
        
        $sql = "UPDATE semesters SET name = ?, is_current = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sii", $name, $is_current, $id);
        
        return $stmt->execute();
    }
    
    // Delete a semester
    public function deleteSemester($id) {
        $sql = "DELETE FROM semesters WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    // Get semesters dropdown for forms
    public function getSemestersDropdown($selected_id = null) {
        $semesters = $this->getAllSemesters();
        
        $dropdown = '';
        foreach ($semesters as $semester) {
            $selected = ($selected_id == $semester['id']) ? 'selected' : '';
            $dropdown .= "<option value='{$semester['id']}' {$selected}>{$semester['name']}</option>";
        }
        
        return $dropdown;
    }
}
?> 