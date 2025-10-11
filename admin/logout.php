<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->logout();
header('Location: /webthethao_project/admin/login.php');
exit;