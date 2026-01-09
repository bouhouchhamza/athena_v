<?php
session_start();
require_once __DIR__ . '/../utils/Auth.php';

$auth = new Auth();
$auth->logout();

// Redirect to login page
header('Location: login.php');
exit();
?>
