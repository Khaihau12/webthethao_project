<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/CategoryRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdmin();

$repo = new CategoryRepository($conn);
$cats = $repo->listAll(1000, 0);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QL Chuyên mục</title>
  <link rel="stylesheet" href="/webthethao_project/css/style.css" />
  <style>
    body{ background:#f6f8fa; }
    .wrap{ max-width: 1000px; margin: 20px auto; }
    table{ width:100%; border-collapse: collapse; background:#fff; }
    th, td{ padding:8px 10px; border-bottom:1px solid #eee; text-align:left; }
    th{ background:#fafafa; }
    .btn{ display:inline-block; padding:6px 10px; border-radius:4px; text-decoration:none; }
    .btn-primary{ background:#78B43D; color:#fff; }
    .btn-danger{ background:#c0392b; color:#fff; }
    .btn-secondary{ background:#555; color:#fff; }
  </style>
</head>
<body>
<div class="wrap">
  <div style="display:flex; justify-content: space-between; align-items:center;">
    <h1>Quản lý Chuyên mục</h1>
    <div>
      <a class="btn btn-secondary" href="/webthethao_project/admin/index.php">← Bảng điều khiển</a>
      <a class="btn btn-primary" href="/webthethao_project/admin/category_edit.php">+ Thêm chuyên mục</a>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tên</th>
        <th>Slug</th>
        <th>Cha</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cats as $c): ?>
      <tr>
        <td><?php echo (int)$c['id']; ?></td>
        <td><?php echo htmlspecialchars($c['name']); ?></td>
        <td><?php echo htmlspecialchars($c['slug']); ?></td>
        <td><?php echo htmlspecialchars($c['parent_id'] ?? ''); ?></td>
        <td>
          <a class="btn btn-primary" href="/webthethao_project/admin/category_edit.php?id=<?php echo (int)$c['id']; ?>">Sửa</a>
          <form action="/webthethao_project/admin/category_delete.php" method="post" style="display:inline;" onsubmit="return confirm('Xóa chuyên mục này?');">
            <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>" />
            <button class="btn btn-danger" type="submit">Xóa</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>