<?php
// Tên file: /includes/header.php (PHIÊN BẢN TINH GỌN)
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/CategoryRepository.php';

// Chỉ khởi tạo kết nối CSDL một lần
if (!isset($conn) || !$conn->ping()) {
    $db = Database::getInstance();
    $conn = $db->getConnection();
}

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
            </ul>
        </nav>
    </div>
</header>

<nav class="main-nav">
    <div class="container">
        <ul>
            <li><a href="index.php" class="active"><i class="fa fa-home"></i> TRANG CHỦ</a></li>
            <?php
            // Hiển thị menu cấp 1
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