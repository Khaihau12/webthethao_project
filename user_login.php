<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) { $error = 'CSRF không hợp lệ.'; }
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($error === '') {
        if ($auth->login($username, $password)) {
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
