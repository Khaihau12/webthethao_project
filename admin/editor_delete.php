<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/UserRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/admin/editors.php');
    exit;
}

if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
    header('Location: ' . BASE_URL . '/admin/editors.php');
    exit;
}

$current = $auth->currentUser();
$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/admin/editors.php');
    exit;
}

// Prevent deleting self or admin accounts via this endpoint
if ($current && (int)$current->id === $id) {
    header('Location: ' . BASE_URL . '/admin/editors.php');
    exit;
}

$repo = new UserRepository($conn);
$user = $repo->findById($id);
if (!$user || $user->role !== 'editor') {
    header('Location: ' . BASE_URL . '/admin/editors.php');
    exit;
}

// Optional: reassign or nullify author_id for their articles. We'll just keep articles with author_id to preserve history.
$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'editor'");
$stmt->bind_param('i', $id);
$stmt->execute();

header('Location: ' . BASE_URL . '/admin/editors.php');
exit;
