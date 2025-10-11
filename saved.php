<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/InteractionRepository.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$user = $auth->currentUser();
if (!$user) { header('Location: /webthethao_project/user_login.php'); exit; }

$inter = new InteractionRepository($conn);
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;
$total = $inter->countSaved($user->id);
$rows = $inter->listSaved($user->id, $limit, $offset);
$pages = max(1, (int)ceil($total / $limit));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tin đã lưu</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>.wrap{max-width:1000px;margin:20px auto}.card{background:#fff;border:1px solid #eee;border-radius:8px;padding:16px}.list article{border-bottom:1px dashed #ddd;padding:10px 0}.pager a{margin:0 4px}</style>
</head>
<body>
  <?php require __DIR__ . '/includes/header.php'; ?>
  <div class="wrap">
    <div class="card">
      <h1>Tin bài đã lưu</h1>
      <div class="list">
        <?php foreach ($rows as $row): ?>
          <article>
            <a href="/webthethao_project/article.php?slug=<?php echo htmlspecialchars($row['slug']); ?>"><?php echo htmlspecialchars($row['title']); ?></a>
          </article>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?><p>Bạn chưa lưu bài viết nào.</p><?php endif; ?>
      </div>
      <div class="pager" style="margin-top:10px;">
        <?php if ($page > 1): ?><a href="?page=<?php echo $page-1; ?>">« Trước</a><?php endif; ?>
        <span>Trang <?php echo $page; ?>/<?php echo $pages; ?></span>
        <?php if ($page < $pages): ?><a href="?page=<?php echo $page+1; ?>">Tiếp »</a><?php endif; ?>
      </div>
      <p style="margin-top:10px;"><a href="/webthethao_project/account.php">← Về trang tài khoản</a></p>
    </div>
  </div>
  <?php $__footer = __DIR__ . '/includes/footer.php'; if (file_exists($__footer)) require $__footer; ?>
</body>
</html>
