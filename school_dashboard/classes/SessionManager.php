<?php
class SessionManager {
    // Start a new session if not already started
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // Check if user is logged in
    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }
    
    // Get the logged-in user's ID
    public static function getUserId() {
        self::startSession();
        return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    }
    
    // Get the logged-in user's name
    public static function getUserName() {
        self::startSession();
        return isset($_SESSION['full_name']) ? $_SESSION['full_name'] : null;
    }
    
    // Get the logged-in user's email
    public static function getUserEmail() {
        self::startSession();
        return isset($_SESSION['email']) ? $_SESSION['email'] : null;
    }
    
    // Get the logged-in user's type ID
    public static function getUserTypeId() {
        self::startSession();
        return isset($_SESSION['user_type_id']) ? $_SESSION['user_type_id'] : null;
    }
    
    // Check if logged-in user is admin
    public static function isAdmin() {
        return self::getUserTypeId() == 1;
    }
    
    // Check if logged-in user is teacher
    public static function isTeacher() {
        return self::getUserTypeId() == 2;
    }
    
    // Check if logged-in user is student
    public static function isStudent() {
        return self::getUserTypeId() == 3;
    }
    
    // Set a session variable
    public static function set($key, $value) {
        self::startSession();
        $_SESSION[$key] = $value;
    }
    
    // Get a session variable
    public static function get($key) {
        self::startSession();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }
    
    // Remove a session variable
    public static function remove($key) {
        self::startSession();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    // Destroy the session (logout)
    public static function destroy() {
        self::startSession();
        // Unset all session variables
        $_SESSION = [];
        
        // If using session cookie, delete it
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        
        // Destroy the session
        session_destroy();
    }
    
    // Set a flash message (for one-time use, like notifications)
    public static function setFlash($key, $message) {
        self::startSession();
        $_SESSION['flash'][$key] = $message;
    }
    
    // Get a flash message and remove it
    public static function getFlash($key) {
        self::startSession();
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }
    
    // Check if a flash message exists
    public static function hasFlash($key) {
        self::startSession();
        return isset($_SESSION['flash'][$key]);
    }
}
?> 