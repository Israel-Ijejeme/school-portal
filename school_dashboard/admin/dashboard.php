<?php
require_once '../classes/SessionManager.php';
require_once '../classes/User.php';
require_once '../classes/Course.php';
require_once '../classes/Department.php';
require_once '../classes/Semester.php';

SessionManager::startSession();

// Redirect to the new fixed dashboard
header('Location: dashboard_fixed.php');
exit;
?> 