<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Article.php';
require_once __DIR__ . '/../classes/ArticleRepository.php';
require_once __DIR__ . '/../classes/CategoryRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdminOrEditor();

$articleRepo = new ArticleRepository($conn);
$categoryRepo = new CategoryRepository($conn);

$search = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;
$articles = $articleRepo->listAll($limit, $offset, $search);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>QL Bài viết</title>
  <link rel="stylesheet" href="/webthethao_project/css/style.css" />
  <style>
    body{ background:#f6f8fa; }
    .wrap{ max-width: 1200px; margin: 20px auto; }
    .actions{ display:flex; justify-content: space-between; align-items:center; }
    table{ width:100%; border-collapse: collapse; background:#fff; }
    th, td{ padding:8px 10px; border-bottom:1px solid #eee; text-align:left; }
    th{ background:#fafafa; }
    .btn{ display:inline-block; padding:6px 10px; border-radius:4px; text-decoration:none; }
    .btn-primary{ background:#78B43D; color:#fff; }
    .btn-danger{ background:#c0392b; color:#fff; }
    .btn-secondary{ background:#555; color:#fff; }
    .search-box input{ padding:8px; border-radius:6px; border:1px solid #ddd; }
  </style>
</head>
<body>
<div class="wrap">
  <div class="actions">
    <h1>Quản lý Bài viết</h1>
    <div>
      <a class="btn btn-secondary" href="/webthethao_project/admin/index.php">← Bảng điều khiển</a>
      <a class="btn btn-primary" href="/webthethao_project/admin/article_edit.php">+ Thêm bài viết</a>
    </div>
  </div>

  <form method="get" class="search-box" style="margin: 10px 0 16px;">
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm theo tiêu đề..." />
    <button class="btn btn-secondary" type="submit">Tìm</button>
  </form>

  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Tiêu đề</th>
        <th>Chuyên mục</th>
        <th>Nổi bật</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articles as $a): ?>
      <tr>
        <td><?php echo (int)$a->id; ?></td>
        <td><a href="/webthethao_project/article.php?slug=<?php echo htmlspecialchars($a->slug); ?>" target="_blank"><?php echo htmlspecialchars($a->title); ?></a></td>
        <td><?php echo htmlspecialchars($a->category_name); ?></td>
        <td><?php echo $a->is_featured ? '✔' : '✖'; ?></td>
        <td><?php echo htmlspecialchars($a->created_at); ?></td>
        <td>
          <a class="btn btn-primary" href="/webthethao_project/admin/article_edit.php?id=<?php echo (int)$a->id; ?>">Sửa</a>
          <form action="/webthethao_project/admin/article_delete.php" method="post" style="display:inline;" onsubmit="return confirm('Xóa bài viết này?');">
            <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
            <input type="hidden" name="id" value="<?php echo (int)$a->id; ?>" />
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