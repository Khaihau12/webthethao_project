<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/CategoryRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdminOrEditor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $repo = new CategoryRepository($conn);
        $repo->delete($id);
    }
}
header('Location: /webthethao_project/admin/categories.php');
exit;