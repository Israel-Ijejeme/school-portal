<?php
require_once 'Database.php';

class Department {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get all departments
    public function getAllDepartments() {
        $sql = "SELECT * FROM departments ORDER BY name";
        
        $result = $this->db->query($sql);
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $departments[] = $row;
        }
        
        return $departments;
    }
    
    // Get department by ID
    public function getDepartmentById($id) {
        $sql = "SELECT * FROM departments WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Add a new department
    public function addDepartment($name) {
        $sql = "INSERT INTO departments (name) VALUES (?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $name);
        
        return $stmt->execute();
    }
    
    // Update a department
    public function updateDepartment($id, $name) {
        $sql = "UPDATE departments SET name = ? WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $name, $id);
        
        return $stmt->execute();
    }
    
    // Delete a department
    public function deleteDepartment($id) {
        $sql = "DELETE FROM departments WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id);
        
        return $stmt->execute();
    }
    
    // Get departments dropdown for forms
    public function getDepartmentsDropdown($selected_id = null) {
        $departments = $this->getAllDepartments();
        
        $dropdown = '';
        foreach ($departments as $department) {
            $selected = ($selected_id == $department['id']) ? 'selected' : '';
            $dropdown .= "<option value='{$department['id']}' {$selected}>{$department['name']}</option>";
        }
        
        return $dropdown;
    }
}
?> 