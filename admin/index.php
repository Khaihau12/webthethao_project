<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdminOrEditor();
$user = $auth->currentUser();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trang quản trị</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css" />
  <style>
    .admin-wrap { max-width: 1100px; margin: 20px auto; }
    .admin-header { display:flex; justify-content: space-between; align-items:center; margin-bottom: 16px; }
    .card-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(260px,1fr)); gap: 16px; }
    .card { background:#fff; border:1px solid #eee; border-radius:8px; padding:16px; box-shadow:0 1px 4px rgba(0,0,0,.04); }
    .card a { display:inline-block; margin-top: 8px; color: #78B43D; }
    body { background:#f6f8fa; }
    .btn-logout { color:#a00; }
  </style>
</head>
<body>
  <div class="admin-wrap">
    <div class="admin-header">
      <h1>Bảng điều khiển</h1>
      <div>
  Xin chào, <strong><?php echo htmlspecialchars($user->username); ?></strong> |
  <a class="btn-logout" href="<?php echo BASE_URL; ?>/admin/logout.php">Đăng xuất</a>
      </div>
    </div>
    <div class="card-grid">
      <div class="card">
        <h3>Quản lý Bài viết</h3>
        <p>Thêm mới, chỉnh sửa, xóa bài viết.</p>
  <a href="<?php echo BASE_URL; ?>/admin/articles.php">Tới danh sách bài viết →</a>
      </div>
      <div class="card">
        <h3>Quản lý Chuyên mục</h3>
        <p>Thêm mới, chỉnh sửa, xóa chuyên mục.</p>
        <a href="<?php echo BASE_URL; ?>/admin/categories.php">Tới danh sách chuyên mục →</a>
      </div>
      <?php if (($user->role ?? '') === 'admin'): ?>
      <div class="card">
        <h3>Quản lý Editor</h3>
        <p>Tạo tài khoản editor để soạn thảo và quản lý nội dung.</p>
        <a href="<?php echo BASE_URL; ?>/admin/editors.php">Tới danh sách editor →</a>
      </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>