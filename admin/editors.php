<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/UserRepository.php';
require_once __DIR__ . '/../classes/Helpers.php';

$db = Database::getInstance();
$conn = $db->getConnection();
$auth = new Auth($conn);
$auth->requireAdmin(); // only admin can manage editors

$userRepo = new UserRepository($conn);
$errors = [];$success='';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) { $errors[] = 'CSRF không hợp lệ.'; }
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($username === '' || $password === '') { $errors[] = 'Cần username và password.'; }
    // uniqueness checks
    if (!$errors && $userRepo->findByUsername($username)) { $errors[] = 'Username đã tồn tại.'; }
    if (!$errors && $email !== '' && $userRepo->findByEmail($email)) { $errors[] = 'Email đã tồn tại.'; }
    if (!$errors) {
        $ok = $userRepo->create($username, $password, 'editor', $display_name ?: null, $email ?: null);
        if ($ok) { $success = 'Tạo editor thành công.'; } else { $errors[] = 'Không thể tạo editor.'; }
    }
}

// list editors
$res = $conn->query("SELECT id, username, display_name, email, role, created_at FROM users WHERE role = 'editor' ORDER BY created_at DESC");
$editors = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Quản lý Editor</title>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css" />
  <style>
    body{ background:#f6f8fa; }
    .wrap{ max-width: 1100px; margin: 20px auto; }
    table{ width:100%; border-collapse: collapse; background:#fff; }
    th, td{ padding:8px 10px; border-bottom:1px solid #eee; text-align:left; }
    th{ background:#fafafa; }
    .btn{ display:inline-block; padding:6px 10px; border-radius:4px; text-decoration:none; }
    .btn-primary{ background:#78B43D; color:#fff; }
    .btn-danger{ background:#c0392b; color:#fff; }
    .btn-secondary{ background:#555; color:#fff; }
    .card{ background:#fff; border:1px solid #eee; border-radius:8px; padding:16px; margin-bottom:16px; }
    input, select{ padding:8px; border:1px solid #ddd; border-radius:6px; }
  </style>
 </head>
 <body>
 <div class="wrap">
   <div style="display:flex; justify-content: space-between; align-items:center;">
     <h1>Quản lý Editor</h1>
     <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/admin/index.php">← Bảng điều khiển</a>
   </div>

   <div class="card">
     <h3>Thêm editor mới</h3>
     <?php foreach ($errors as $e): ?><div class="alert" style="background:#ffecec;color:#a00;padding:8px;border-radius:6px;margin-bottom:8px;"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
     <?php if ($success): ?><div class="success" style="background:#e8fbf0;color:#2a7a3a;padding:8px;border-radius:6px;margin-bottom:8px;"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
     <form method="post">
       <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
       <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
         <div>
           <label>Username</label>
           <input type="text" name="username" required />
         </div>
         <div>
           <label>Password</label>
           <input type="password" name="password" required />
         </div>
         <div>
           <label>Display name</label>
           <input type="text" name="display_name" placeholder="Tên hiển thị trên bài viết" />
         </div>
         <div>
           <label>Email</label>
           <input type="email" name="email" />
         </div>
       </div>
       <button class="btn btn-primary" type="submit" style="margin-top:10px;">Tạo editor</button>
     </form>
   </div>

   <div class="card">
     <h3>Danh sách editor</h3>
     <table>
       <thead><tr><th>ID</th><th>Username</th><th>Display name</th><th>Email</th><th>Tạo lúc</th><th>Hành động</th></tr></thead>
       <tbody>
         <?php foreach ($editors as $u): ?>
           <tr>
             <td><?php echo (int)$u['id']; ?></td>
             <td><?php echo htmlspecialchars($u['username']); ?></td>
             <td><?php echo htmlspecialchars($u['display_name'] ?? ''); ?></td>
             <td><?php echo htmlspecialchars($u['email'] ?? ''); ?></td>
             <td><?php echo htmlspecialchars($u['created_at']); ?></td>
             <td>
               <form action="<?php echo BASE_URL; ?>/admin/editor_delete.php" method="post" onsubmit="return confirm('Xóa editor này?');" style="display:inline;">
                 <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
                 <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>" />
                 <button class="btn btn-danger" type="submit">Xóa</button>
               </form>
             </td>
           </tr>
         <?php endforeach; ?>
         <?php if (empty($editors)): ?><tr><td colspan="6">Chưa có editor.</td></tr><?php endif; ?>
       </tbody>
     </table>
   </div>
 </div>
 </body>
 </html>
