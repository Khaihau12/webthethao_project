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
$current_user = $auth->currentUser();

// Lấy categories cho menu chính
$categoryRepo = new CategoryRepository($conn);
$all_categories = $categoryRepo->getAllCategories();

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

<?php
// Lấy tên file hiện tại để xác định menu active
$current_page = basename($_SERVER['PHP_SELF']);
$current_slug = $_GET['slug'] ?? '';
?>
<nav class="main-nav">
    <div class="container">
        <ul>
            <li><a href="index.php" class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><i class="fa fa-home"></i> TRANG CHỦ</a></li>
            <?php
            // Hiển thị menu cấp 1
            foreach ($category_map[0] ?? [] as $top_cat) {
                $children = $category_map[$top_cat['id']] ?? [];
                $is_active = ($current_page == 'category.php' && $current_slug == $top_cat['slug']) ? 'active' : '';
                
                if (empty($children)) {
                    // Menu không có menu con
                    echo '<li><a href="category.php?slug=' . htmlspecialchars($top_cat['slug']) . '" class="' . $is_active . '">' . htmlspecialchars(strtoupper($top_cat['name'])) . '</a></li>';
                } else {
                    // Menu có menu con
                    echo '<li class="has-children">';
                    echo '<a href="category.php?slug=' . htmlspecialchars($top_cat['slug']) . '" class="' . $is_active . '">' . htmlspecialchars(strtoupper($top_cat['name'])) . '</a>';
                    echo '<ul class="sub-menu">';
                    foreach ($children as $child_cat) {
                        $child_active = ($current_page == 'category.php' && $current_slug == $child_cat['slug']) ? 'active' : '';
                        echo '<li><a href="category.php?slug=' . htmlspecialchars($child_cat['slug']) . '" class="' . $child_active . '">' . htmlspecialchars($child_cat['name']) . '</a></li>';
                    }
                    echo '</ul>';
                    echo '</li>';
                }
            }
            ?>
        </ul>
    </div>
</nav>