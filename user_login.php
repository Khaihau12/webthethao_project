<?php
// Câu 2: Đăng nhập
// - Mục tiêu: nhận username/password từ form, kiểm tra CSRF, xác thực bằng Auth->login(),
//   sau đó chuyển hướng về trang tài khoản nếu thành công, hoặc hiển thị lỗi nếu thất bại.
// - Bảo mật: sử dụng CSRF token để chống tấn công giả mạo yêu cầu; dùng password_hash/password_verify.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // 1) Kiểm tra CSRF: từ thẻ input hidden name="csrf"
  if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) { $error = 'CSRF không hợp lệ.'; }
  // 2) Lấy input và làm sạch tối thiểu (trim)
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');
  // 3) Nếu hợp lệ, gọi Auth->login() để xác thực
  if ($error === '') {
    // Auth->login() sẽ: tìm user theo username, password_verify, lưu user_id & user_role vào session
    if ($auth->login($username, $password)) {
      // 4) Thành công: chuyển hướng về trang tài khoản của tôi
      header('Location: /webthethao_project/account.php');
      exit;
    } else { $error = 'Sai tài khoản hoặc mật khẩu.'; }
  }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng nhập</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>.wrap{max-width:500px;margin:20px auto;background:#fff;border:1px solid #eee;border-radius:8px;padding:16px}label{display:block;margin-top:10px}input{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-top:6px}.btn{margin-top:12px;padding:10px 14px;background:#78B43D;color:#fff;border:none;border-radius:6px}.alert{background:#ffecec;color:#a00;padding:10px;border-radius:6px;margin-bottom:10px}</style>
</head>
<body>
  <?php require __DIR__ . '/includes/header.php'; ?>
  <div class="wrap">
    <h1>Đăng nhập</h1>
    <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post">
      <!-- CSRF token: bắt buộc cho POST để chống tấn công CSRF -->
      <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
      <label>Username</label>
      <input type="text" name="username" required />
      <label>Mật khẩu</label>
      <input type="password" name="password" required />
      <button class="btn" type="submit">Đăng nhập</button>
    </form>
    <p>Chưa có tài khoản? <a href="/webthethao_project/user_register.php">Đăng ký</a></p>
  </div>
</body>
</html>
