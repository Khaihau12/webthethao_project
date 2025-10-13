<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/ArticleRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';
require_once __DIR__ . '/../classes/MediaManager.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        // Fetch article to know which images it referenced
    $stmt = $conn->prepare("SELECT content, image_url FROM articles WHERE article_id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $art = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $repo = new ArticleRepository($conn);
        $repo->delete($id);

        if ($art) {
            $files = MediaManager::extractUploadFilenamesFromHtml($art['content'] ?? '');
            if (!empty($art['image_url']) && MediaManager::isUploadUrl($art['image_url'])) {
                $files[] = MediaManager::filenameFromUrl($art['image_url']);
            }
            $files = array_values(array_unique(array_filter($files)));
            foreach ($files as $f) { MediaManager::deleteIfUnreferenced($conn, $f, 0); }
        }
    }
}
header('Location: /webthethao_project/admin/articles.php');
exit;