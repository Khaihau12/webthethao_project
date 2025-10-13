<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Article.php';
require_once __DIR__ . '/../classes/ArticleRepository.php';
require_once __DIR__ . '/../classes/CategoryRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';
require_once __DIR__ . '/../classes/Editor.php';
require_once __DIR__ . '/../classes/MediaManager.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdminOrEditor();
$current = $auth->currentUser();

$articleRepo = new ArticleRepository($conn);
$categoryRepo = new CategoryRepository($conn);
$categories = $categoryRepo->listAll(1000, 0);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editing = null;
if ($id > 0) {
    // Lấy lại bài viết theo id
  $stmt = $conn->prepare("SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at,
                   c.name as category_name, c.slug as category_slug
              FROM articles a JOIN categories c ON a.category_id = c.category_id WHERE a.article_id = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($row) { $editing = new Article($row); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'CSRF token không hợp lệ.';
    }
    $category_id = (int)($_POST['category_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $is_featured = !empty($_POST['is_featured']) ? 1 : 0;

    if ($title === '') $errors[] = 'Tiêu đề bắt buộc.';
    if ($category_id <= 0) $errors[] = 'Chọn chuyên mục.';
    if ($slug === '') $slug = Helpers::slugify($title);

  if (!$errors) {
    // Track old images to clean up if they are removed in the new content
    $oldFiles = [];
    if ($id > 0 && $editing) {
      $oldFiles = MediaManager::extractUploadFilenamesFromHtml($editing->content ?? '');
      if (!empty($editing->image_url) && MediaManager::isUploadUrl($editing->image_url)) {
        $oldFiles[] = MediaManager::filenameFromUrl($editing->image_url);
      }
      $oldFiles = array_values(array_unique(array_filter($oldFiles)));
    }
  $data = compact('category_id','title','slug','summary','content','image_url','is_featured');
  // Assign author_id
  $data['author_id'] = $current ? (int)$current->id : null;
        if ($id > 0) {
            $ok = $articleRepo->update($id, $data);
        } else {
            $ok = $articleRepo->create($data);
        }
    if ($ok) {
      // After save, compute new files and delete ones no longer used
      $newFiles = MediaManager::extractUploadFilenamesFromHtml($content ?? '');
      if (!empty($image_url) && MediaManager::isUploadUrl($image_url)) {
        $newFiles[] = MediaManager::filenameFromUrl($image_url);
      }
      $newFiles = array_values(array_unique(array_filter($newFiles)));
      $toDelete = array_diff($oldFiles, $newFiles);
      foreach ($toDelete as $f) { MediaManager::deleteIfUnreferenced($conn, $f, $id); }
            header('Location: /webthethao_project/admin/articles.php');
            exit;
        } else {
            $errors[] = 'Không thể lưu bài viết. Kiểm tra trùng slug hoặc dữ liệu không hợp lệ.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $id>0? 'Sửa' : 'Thêm'; ?> bài viết</title>
  <link rel="stylesheet" href="/webthethao_project/css/style.css" />
  <style>
    body{ background:#f6f8fa; }
    .wrap{ max-width: 900px; margin: 20px auto; background:#fff; border:1px solid #eee; border-radius:8px; padding:16px; }
    input[type=text], select, textarea{ width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:6px; }
  textarea{ min-height: 260px; }
    label{ margin-top:10px; display:block; font-weight: 500; }
    .btn{ margin-top: 14px; padding:8px 12px; border:none; border-radius:6px; color:#fff; background:#78B43D; cursor:pointer; }
    .alert{ background:#ffecec; color:#a00; padding:10px; border-radius:6px; margin-bottom:10px; }
  </style>
</head>
<body>
<div class="wrap">
  <h1><?php echo $id>0? 'Sửa' : 'Thêm'; ?> bài viết</h1>
  <p><a href="/webthethao_project/admin/articles.php">← Quay lại danh sách</a></p>
  <?php foreach ($errors as $e): ?><div class="alert"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
    <label>Chuyên mục</label>
    <select name="category_id" required>
      <option value="">-- chọn --</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?php echo (int)$c['id']; ?>" <?php if(($editing? $editing->category_id:0) == $c['id']) echo 'selected';?>><?php echo htmlspecialchars($c['name']); ?></option>
      <?php endforeach; ?>
    </select>

    <label>Tiêu đề</label>
    <input type="text" name="title" value="<?php echo htmlspecialchars($editing->title ?? ''); ?>" required />

    <label>Slug (để trống sẽ tự tạo)</label>
    <input type="text" name="slug" value="<?php echo htmlspecialchars($editing->slug ?? ''); ?>" />

    <label>Tóm tắt</label>
    <textarea name="summary"><?php echo htmlspecialchars($editing->summary ?? ''); ?></textarea>

  <label>Nội dung</label>
  <textarea id="content" name="content"><?php echo htmlspecialchars($editing->content ?? ''); ?></textarea>
  <?php echo Editor::renderTinyMCE('#content', Helpers::csrfToken()); ?>

  <label>Ảnh</label>
  <input id="image_url" type="hidden" name="image_url" value="<?php echo htmlspecialchars($editing->image_url ?? ''); ?>" />
  <?php echo MediaManager::renderImageSelector('#image_url', Helpers::csrfToken()); ?>

    <label><input type="checkbox" name="is_featured" value="1" <?php echo !empty($editing->is_featured)?'checked':''; ?> /> Tin nổi bật</label>

    <button class="btn" type="submit">Lưu</button>
  </form>
</div>
</body>
</html>