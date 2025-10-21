
<?php
// Đăng ký tài khoản đơn giản
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/UserRepository.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$userRepo = new UserRepository($conn);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Kiểm tra cơ bản
    if ($username === '' || $password === '') {
        $errors[] = 'Cần username và password';
    }
    
    // Kiểm tra trùng username
    if (!$errors) {
        $existing = $userRepo->findByUsername($username);
        if ($existing) {
            $errors[] = 'Username đã tồn tại.';
        }
    }
    
    // Kiểm tra trùng email
    if (!$errors && $email !== '') {
        $existingEmail = $userRepo->findByEmail($email);
        if ($existingEmail) {
            $errors[] = 'Email đã được sử dụng.';
        }
    }
    
    // Tạo tài khoản
    if (!$errors) {
        $ok = $userRepo->create($username, $password, 'user', $display_name ?: $username, $email ?: null);
        if ($ok) {
            $success = true;
        } else {
            $errors[] = 'Không thể đăng ký.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Đăng ký</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>.wrap{max-width:500px;margin:20px auto;background:#fff;border:1px solid #eee;border-radius:8px;padding:16px;}label{display:block;margin-top:10px}input{width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;margin-top:6px}.btn{margin-top:12px;padding:10px 14px;background:#78B43D;color:#fff;border:none;border-radius:6px}.alert{background:#ffecec;color:#a00;padding:10px;border-radius:6px;margin-bottom:10px}.success{background:#e8fbf0;color:#2a7a3a;padding:10px;border-radius:6px;margin-bottom:10px}</style>
</head>
<body>
  <?php require __DIR__ . '/includes/header.php'; ?>
  <div class="wrap">
    <h1>Đăng ký tài khoản</h1>
    <?php foreach ($errors as $e): ?><div class="alert"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
    <?php if ($success): ?><div class="success">Đăng ký thành công. Bạn có thể <a href="/webthethao_project/user_login.php">đăng nhập</a>.</div><?php endif; ?>
    <form method="post">
      <label>Tên hiển thị</label>
      <input type="text" name="display_name" />
      <label>Username</label>
      <input type="text" name="username" required />
      <label>Email (tùy chọn)</label>
      <input type="email" name="email" />
      <label>Mật khẩu</label>
      <input type="password" name="password" required />
      <button class="btn" type="submit">Đăng ký</button>
    </form>
  </div>
</body>
</html>
