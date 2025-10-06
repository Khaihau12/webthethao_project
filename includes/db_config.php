<?php
$servername = "localhost";
$username = "root";     // <--- ĐIỀN USERNAME CỦA BẠN
$password = "";         // <--- ĐIỀN PASSWORD CỦA BẠN
$dbname = "webthethao"; 

// Tạo kết nối
$conn = new mysqli($servername, $username, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    // Nếu lỗi, dừng và báo lỗi kết nối
    die("Kết nối database thất bại: " . $conn->connect_error);
}

// Thiết lập bộ ký tự UTF8
$conn->set_charset("utf8mb4");

// --- CÁC HÀM LẤY DỮ LIỆU ĐỘNG ---

/**
 * Lấy tin nổi bật lớn nhất
 */
function getFeaturedArticle($conn) {
    // Lấy thêm cột image_url, summary, created_at, category, content
    $sql = "SELECT id, title, summary, slug, image_url, created_at, category, content FROM articles WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 1";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ?: null;
    }
    return null;
}

/**
 * Lấy tin tức cho Sidebar (Tin tức trong ngày)
 */
function getSidebarArticles($conn, $limit = 7) {
    // Lấy các tin tức mới nhất, không phải tin nổi bật
    $sql = "SELECT id, category, title, slug, image_url, summary, created_at FROM articles WHERE is_featured = 0 ORDER BY created_at DESC LIMIT ?";
    $articles = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
    return $articles;
}

/**
 * Lấy các tin chính phụ (cạnh tin lớn)
 */
function getTopMainArticles($conn, $limit = 4) {
    // Lấy 4 tin mới nhất, không phải tin nổi bật
    $sql = "SELECT id, category, title, slug, summary, created_at FROM articles WHERE is_featured = 0 ORDER BY created_at DESC LIMIT ?";
    $articles = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
    return $articles;
}

/**
 * Lấy tin tức Video (Lọc theo category = 'Video')
 */
function getVideoArticles($conn, $limit = 3) {
    $sql = "SELECT id, title, slug, summary, created_at FROM articles WHERE category = 'Video' ORDER BY created_at DESC LIMIT ?";
    $articles = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
    return $articles;
}

/**
 * Lấy tin tức Kinh Doanh (Lọc theo category = 'Kinh Doanh' cho khối 4 tin ảnh nhỏ)
 */
function getBusinessArticles($conn, $limit = 4) {
    $sql = "SELECT id, title, slug, image_url, summary, created_at FROM articles WHERE category = 'Kinh Doanh' ORDER BY created_at DESC LIMIT ?";
    $articles = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
    return $articles;
}

/**
 * Lấy tin tức Thị Trường (Lọc theo category = 'Thị Trường' hoặc 'Bất Động Sản')
 */
function getMarketArticles($conn, $limit = 4) {
    // Kết hợp các category liên quan đến thị trường
    $sql = "SELECT id, title, slug, image_url, created_at FROM articles WHERE category IN ('Thị Trường', 'Bất Động Sản') ORDER BY created_at DESC LIMIT ?";
    $articles = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
    return $articles;
}

/**
 * Hàm Lấy chi tiết bài viết dựa trên slug
 */
function getArticleBySlug($conn, $slug) {
    $stmt = $conn->prepare("SELECT id, title, content, summary, category, created_at, image_url FROM articles WHERE slug = ? LIMIT 1");
    if (! $stmt) return null;
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * Lấy các bài viết mới nhất
 */
function getLatestArticles($conn, $limit = 10) {
    $sql = "SELECT id, category, title, slug, created_at FROM articles ORDER BY created_at DESC LIMIT ?";
    $articles = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $articles[] = $row;
        }
        $stmt->close();
    }
    return $articles;
}

/**
 * Lấy ảnh liên quan đến một bài viết
 * Trả về mảng có key là position_key => array of images
 */
function getArticleImages($conn, $article_id) {
    $sql = "SELECT id, file_path, caption, alt_text, position_key, display_order FROM article_images WHERE article_id = ? ORDER BY display_order ASC, id ASC";
    $images = [];
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param('i', $article_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $key = $row['position_key'] ?? '';
            if (!isset($images[$key])) $images[$key] = [];
            $images[$key][] = $row;
        }
        $stmt->close();
    }
    return $images;
}

/**
 * Thay các token [IMAGE:key] trong content bằng HTML <figure>...<img>
 */
function injectArticleImages($content, $images) {
    if (empty($content) || empty($images)) return $content;
    $result = preg_replace_callback('/\[IMAGE:([A-Za-z0-9_\-]+)\]/', function($m) use ($images) {
        $key = $m[1];
        if (empty($images[$key])) return ''; // nếu không có ảnh, xóa token
        $html = '';
        foreach ($images[$key] as $img) {
            $src = htmlspecialchars($img['file_path'], ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($img['alt_text'] ?? '', ENT_QUOTES, 'UTF-8');
            $caption = htmlspecialchars($img['caption'] ?? '', ENT_QUOTES, 'UTF-8');
            $html .= '<figure class="article-inline-image" style="text-align:center; margin:18px 0;">';
            $html .= "<img src=\"$src\" alt=\"$alt\" style=\"max-width:100%; height:auto;\">";
            if ($caption) $html .= "<figcaption style=\"font-size:13px;color:#666;\">$caption</figcaption>";
            $html .= '</figure>';
        }
        return $html;
    }, $content);
    return $result;
}
?>