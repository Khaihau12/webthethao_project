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
$all = $repo->listAll(1000, 0);
$id = (int)($_GET['id'] ?? 0);
$editing = null;
if ($id > 0) {
    foreach ($all as $row) { if ($row['id'] == $id) { $editing = $row; break; } }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
        $errors[] = 'CSRF token không hợp lệ.';
    }
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $parent_id = $_POST['parent_id'] ?? '';
    if ($name === '') $errors[] = 'Tên chuyên mục bắt buộc.';
    if ($slug === '') $slug = Helpers::slugify($name);

    if (!$errors) {
        if ($id > 0) {
            $ok = $repo->update($id, $name, $slug, $parent_id === '' ? null : (int)$parent_id);
        } else {
            $ok = $repo->create($name, $slug, $parent_id === '' ? null : (int)$parent_id);
        }
        if ($ok) {
            header('Location: /webthethao_project/admin/categories.php');
            exit;
        } else {
            $errors[] = 'Không thể lưu chuyên mục (trùng slug?).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $id>0? 'Sửa' : 'Thêm'; ?> chuyên mục</title>
  <link rel="stylesheet" href="/webthethao_project/css/style.css" />
  <style>
    body{ background:#f6f8fa; }
    .wrap{ max-width: 700px; margin: 20px auto; background:#fff; border:1px solid #eee; border-radius:8px; padding:16px; }
    input[type=text], select{ width:100%; padding:10px; border:1px solid #ddd; border-radius:6px; margin-top:6px; }
    label{ margin-top:10px; display:block; font-weight: 500; }
    .btn{ margin-top: 14px; padding:8px 12px; border:none; border-radius:6px; color:#fff; background:#78B43D; cursor:pointer; }
    .alert{ background:#ffecec; color:#a00; padding:10px; border-radius:6px; margin-bottom:10px; }
  </style>
</head>
<body>
<div class="wrap">
  <h1><?php echo $id>0? 'Sửa' : 'Thêm'; ?> chuyên mục</h1>
  <p><a href="/webthethao_project/admin/categories.php">← Quay lại danh sách</a></p>
  <?php foreach ($errors as $e): ?><div class="alert"><?php echo htmlspecialchars($e); ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
    <label>Tên</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($editing['name'] ?? ''); ?>" required />

    <label>Slug (để trống sẽ tự tạo)</label>
    <input type="text" name="slug" value="<?php echo htmlspecialchars($editing['slug'] ?? ''); ?>" />

    <label>Chuyên mục cha</label>
    <select name="parent_id">
      <option value="">-- không --</option>
      <?php foreach ($all as $row): if ($editing && $row['id'] == $editing['id']) continue; ?>
        <option value="<?php echo (int)$row['id']; ?>" <?php if(($editing['parent_id'] ?? '') == $row['id']) echo 'selected';?>><?php echo htmlspecialchars($row['name']); ?></option>
      <?php endforeach; ?>
    </select>

    <button class="btn" type="submit">Lưu</button>
  </form>
</div>
</body>
</html>