<?php
// Tên file: /includes/header.php (PHIÊN BẢN TINH GỌN)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/CategoryRepository.php';
require_once __DIR__ . '/../classes/Auth.php';

// Chỉ khởi tạo kết nối CSDL một lần
if (!isset($conn) || !$conn->ping()) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
}
// Khởi tạo Auth để biết tình trạng đăng nhập
$auth = new Auth($conn);
$current_user = $auth->currentUser(); // Câu 2: xác định trạng thái đăng nhập để hiển thị link Tài khoản/Đăng nhập/Đăng ký

// Lấy categories cho menu chính
$categoryRepo = new CategoryRepository($conn);
$all_categories = $categoryRepo->getAllCategories(); // Câu 4: dùng để dựng menu chuyên mục từ CSDL

// Xây dựng cây menu chuyên mục
$category_map = [];
foreach ($all_categories as $cat) {
    $pid = $cat['parent_id'] ?: 0;
    if (!isset($category_map[$pid])) $category_map[$pid] = [];
    $category_map[$pid][] = $cat;
}
?>
<header class="top-bar">
    <div class="container">
        <div class="logo">
            <a href="index.php" aria-label="Trang chủ">
                24H 📰 <span class="logo-subtext">THỂ THAO - BÓNG ĐÁ</span>
            </a>
        </div>
        <nav class="top-menu">
            <ul>
                <li>
                    <form action="index.php" method="get">
                        <input type="text" name="q" placeholder="Nhập tin cần tìm">
                        <button type="submit" style="border:none; background:transparent; padding:0; margin-left:6px;"><i class="fa fa-search"></i></button>
                    </form>
                </li>
                <li>
                    <?php if ($current_user): ?>
                        <a href="<?php echo BASE_URL; ?>/account.php">Tài khoản</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/user_login.php">Đăng nhập</a>
                    <?php endif; ?>
                </li>
                <?php if (!$current_user): ?>
                <li><a href="<?php echo BASE_URL; ?>/user_register.php">Đăng ký</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<nav class="main-nav">
    <div class="container">
        <ul>
            <li><a href="index.php" class="active"><i class="fa fa-home"></i> TRANG CHỦ</a></li>
            <?php
            // Hiển thị menu cấp 1 (Câu 4: mỗi mục sẽ dẫn tới category.php?slug=...)
            foreach ($category_map[0] ?? [] as $top_cat) {
                $children = $category_map[$top_cat['id']] ?? [];
                
                if (empty($children)) {
                    // Menu không có menu con
                    echo '<li><a href="category.php?slug=' . htmlspecialchars($top_cat['slug']) . '">' . htmlspecialchars(strtoupper($top_cat['name'])) . '</a></li>';
                } else {
                    // Menu có menu con
                    echo '<li class="has-children">';
                    echo '<a href="category.php?slug=' . htmlspecialchars($top_cat['slug']) . '">' . htmlspecialchars(strtoupper($top_cat['name'])) . '</a>';
                    echo '<ul class="sub-menu">';
                    foreach ($children as $child_cat) {
                        echo '<li><a href="category.php?slug=' . htmlspecialchars($child_cat['slug']) . '">' . htmlspecialchars($child_cat['name']) . '</a></li>';
                    }
                    echo '</ul>';
                    echo '</li>';
                }
            }
            ?>
        </ul>
    </div>
</nav>