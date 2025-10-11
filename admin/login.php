<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);

$error = '';
$created_admin = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $action = $_POST['action'] ?? 'login';

    if ($action === 'bootstrap') {
        if ($username === '' || $password === '') {
            $error = 'Vui lòng nhập đủ username và password để tạo admin đầu tiên.';
        } else {
            $ok = $auth->bootstrapFirstAdmin($username, $password);
            if ($ok) { $created_admin = true; }
            else { $error = 'Không thể tạo admin: Username đã tồn tại. Vui lòng chọn tên khác.'; }
        }
    } else {
        if ($auth->login($username, $password)) {
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Sai tài khoản hoặc mật khẩu.';
        }
    }
}

$userRepo = new UserRepository($conn);
$needs_bootstrap = $userRepo->countAdmins() === 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng nhập quản trị</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css" />
    <style>
        body { background: #f7f7f7; }
        .login-box { max-width: 420px; margin: 60px auto; background: #fff; padding: 24px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .login-box h1 { margin: 0 0 16px; font-size: 22px; }
        .login-box label { display:block; margin-top: 10px; font-weight: 500; }
        .login-box input { width: 100%; padding: 10px; margin-top: 6px; border:1px solid #ddd; border-radius: 6px; }
        .login-box button { margin-top: 16px; padding: 10px 14px; background: #78B43D; border: none; color: #fff; border-radius: 6px; cursor: pointer; }
        .login-box .muted { color: #666; font-size: 13px; }
        .alert { background: #ffecec; color:#a00; padding:10px; border-radius:6px; margin-bottom:10px; }
        .success { background:#e8fbf0; color:#2a7a3a; padding:10px; border-radius:6px; margin-bottom:10px; }
        .switch { margin-top: 10px; }
    </style>
</head>
<body>
<div class="login-box">
    <h1>Quản trị - Đăng nhập</h1>
    <?php if ($error): ?><div class="alert"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if ($created_admin): ?><div class="success">Tạo admin đầu tiên thành công. Hãy đăng nhập.</div><?php endif; ?>

    <?php if ($needs_bootstrap): ?>
    <p class="muted">Chưa có tài khoản quản trị nào. Hãy tạo tài khoản admin đầu tiên bên dưới.<br>
    Lưu ý: Username không được trùng với người dùng đã có; nếu trùng hệ thống sẽ báo lỗi.</p>
        <form method="post">
            <input type="hidden" name="action" value="bootstrap" />
            <label>Tên đăng nhập</label>
            <input type="text" name="username" required />
            <label>Mật khẩu</label>
            <input type="password" name="password" required />
            <button type="submit">Tạo tài khoản admin</button>
        </form>
        
    <?php else: ?>
        <form method="post">
            <label>Tên đăng nhập</label>
            <input type="text" name="username" required />
            <label>Mật khẩu</label>
            <input type="password" name="password" required />
            <button type="submit">Đăng nhập</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>