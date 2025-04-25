<?php
require_once 'classes/SessionManager.php';

// Destroy the session to log out
SessionManager::destroy();

// Redirect to login page with success message
SessionManager::startSession();
SessionManager::setFlash('success', 'You have been successfully logged out.');
header('Location: index.php');
exit;
?> 