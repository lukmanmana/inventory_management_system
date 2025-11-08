<?php
require_once __DIR__ . '/../services/AuthManager.php';

$auth = new AuthManager();
$auth->logout();

// Redirect to login page
header('Location: ../../public/index.php');
exit;
