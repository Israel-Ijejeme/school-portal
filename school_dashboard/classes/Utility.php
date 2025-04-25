<?php
class Utility {
    // Redirect to a different page
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    // Clean input data
    public static function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    // Validate email
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    // Generate a random string
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    // Format date
    public static function formatDate($date, $format = 'd M, Y') {
        return date($format, strtotime($date));
    }
    
    // Check if a request is POST
    public static function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    // Convert grade point to letter grade (Nigerian system)
    public static function gradePointToLetter($grade_point) {
        if ($grade_point >= 4.5) {
            return 'A';
        } elseif ($grade_point >= 3.5) {
            return 'B';
        } elseif ($grade_point >= 2.5) {
            return 'C';
        } elseif ($grade_point >= 1.5) {
            return 'D';
        } elseif ($grade_point >= 1.0) {
            return 'E';
        } else {
            return 'F';
        }
    }
    
    // Upload a file
    public static function uploadFile($file, $destination, $allowed_types = ['jpg', 'jpeg', 'png'], $max_size = 2097152) {
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return [
                'success' => false,
                'message' => 'Error uploading file'
            ];
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            return [
                'success' => false,
                'message' => 'File too large. Maximum size is ' . ($max_size / 1024 / 1024) . 'MB'
            ];
        }
        
        // Check file type
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_types)) {
            return [
                'success' => false,
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types)
            ];
        }
        
        // Generate a unique filename
        $new_filename = self::generateRandomString() . '.' . $file_extension;
        $upload_path = $destination . '/' . $new_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return [
                'success' => true,
                'filename' => $new_filename,
                'path' => $upload_path
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Failed to save file'
            ];
        }
    }
    
    // Calculate CGPA classification (Nigerian system)
    public static function getCGPAClassification($cgpa) {
        if ($cgpa >= 4.5) {
            return 'First Class';
        } elseif ($cgpa >= 3.5) {
            return 'Second Class Upper';
        } elseif ($cgpa >= 2.5) {
            return 'Second Class Lower';
        } elseif ($cgpa >= 1.5) {
            return 'Third Class';
        } elseif ($cgpa >= 1.0) {
            return 'Pass';
        } else {
            return 'Fail';
        }
    }
    
    // Format CGPA to 2 decimal places
    public static function formatCGPA($cgpa) {
        return number_format($cgpa, 2);
    }
}
?> 