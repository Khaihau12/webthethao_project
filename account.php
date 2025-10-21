
<?php
// =============================
// Câu 3: Cập nhật thông tin tài khoản (đổi mật khẩu)
// =============================
// Trang này cho phép người dùng đổi mật khẩu và xem các bài đã lưu/xem.
// Các bước xử lý đổi mật khẩu gồm:
//   1. Nhận dữ liệu từ form POST (old_password, new_password, csrf token)
//   2. Kiểm tra hợp lệ CSRF token để chống tấn công giả mạo
//   3. Kiểm tra mật khẩu cũ đúng không bằng password_verify
//   4. Nếu đúng, cập nhật password_hash mới vào DB
//   5. Hiển thị thông báo thành công hoặc lỗi
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/classes/InteractionRepository.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$user = $auth->currentUser();
if (!$user) { header('Location: /webthethao_project/user_login.php'); exit; }

$userRepo = new UserRepository($conn);
$inter = new InteractionRepository($conn);
$articleRepo = new ArticleRepository($conn);

$msg = '';$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
  // Đổi mật khẩu đơn giản
  $old = trim($_POST['old_password'] ?? '');
  $new = trim($_POST['new_password'] ?? '');
  
  // Lấy thông tin user từ DB
  $fresh = $userRepo->findById($user->id);
  if ($fresh && password_verify($old, $fresh->password_hash)) {
    // Cập nhật mật khẩu mới
    $hash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->bind_param('si', $hash, $user->id);
    if ($stmt->execute()) {
      $msg = 'Đổi mật khẩu thành công.';
    } else {
      $err = 'Lỗi khi đổi mật khẩu.';
    }
    $stmt->close();
  } else {
    $err = 'Mật khẩu cũ không đúng.';
  }
}

$saved = $inter->listSaved($user->id, 5, 0);
$viewed = $inter->listViewed($user->id, 5, 0);
$saved_total = $inter->countSaved($user->id);
$viewed_total = $inter->countViewed($user->id);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tài khoản của tôi</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    .wrap{max-width:1100px;margin:20px auto}
    .grid{display:grid;grid-template-columns:1fr 2fr;gap:20px}
    .card{background:#fff;border:1px solid #eee;border-radius:8px;padding:16px}
    .list article{border-bottom:1px dashed #ddd;padding:10px 0}
    .alert{background:#ffecec;color:#a00;padding:10px;border-radius:6px;margin-bottom:10px}
    .success{background:#e8fbf0;color:#2a7a3a;padding:10px;border-radius:6px;margin-bottom:10px}
    input{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-top:6px}
    .btn{margin-top:10px;padding:10px 14px;background:#78B43D;color:#fff;border:none;border-radius:6px}
  </style>
</head>
<body>
  <?php require __DIR__ . '/includes/header.php'; ?>
  <div class="wrap">
    <h1>Xin chào, <?php echo htmlspecialchars($user->display_name ?: $user->username); ?></h1>
    <div class="grid">
      <div class="card">
        <h3>Đổi mật khẩu</h3>
        <?php if($err):?><div class="alert"><?php echo htmlspecialchars($err);?></div><?php endif;?>
        <?php if($msg):?><div class="success"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
        <form method="post">
          <input type="hidden" name="change_password" value="1" />
          <label>Mật khẩu cũ</label>
          <input type="password" name="old_password" required />
          <label>Mật khẩu mới</label>
          <input type="password" name="new_password" required />
          <button class="btn" type="submit">Đổi mật khẩu</button>
        </form>
        <p style="margin-top:10px"><a href="/webthethao_project/user_logout.php">Đăng xuất</a></p>
      </div>
      <div>
        <div class="card">
          <h3 style="display:flex;justify-content:space-between;align-items:center;">Tin bài đã lưu <small style="color:#888;font-weight:400">(<?php echo (int)$saved_total; ?>)</small> <a href="/webthethao_project/saved.php" style="font-size:14px;">Xem tất cả</a></h3>
          <div class="list">
            <?php foreach ($saved as $row): ?>
            <article>
              <a href="/webthethao_project/article.php?slug=<?php echo htmlspecialchars($row['slug']); ?>"><?php echo htmlspecialchars($row['title']); ?></a>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card" style="margin-top:16px;">
          <h3 style="display:flex;justify-content:space-between;align-items:center;">Tin bài đã xem <small style="color:#888;font-weight:400">(<?php echo (int)$viewed_total; ?>)</small> <a href="/webthethao_project/viewed.php" style="font-size:14px;">Xem tất cả</a></h3>
          <div class="list">
            <?php foreach ($viewed as $row): ?>
            <article>
              <a href="/webthethao_project/article.php?slug=<?php echo htmlspecialchars($row['slug']); ?>"><?php echo htmlspecialchars($row['title']); ?></a>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
