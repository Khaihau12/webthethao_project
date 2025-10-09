<?php
// Tên file: category.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/ArticleRepository.php';

// Lấy slug của chuyên mục từ URL
$category_slug = $_GET['slug'] ?? '';

if (empty($category_slug)) {
    die("Lỗi: Không có chuyên mục nào được chọn.");
}

// Khởi tạo
$db = Database::getInstance();
$conn = $db->getConnection();
$articleRepo = new ArticleRepository($conn);

// Lấy các bài viết thuộc chuyên mục này
$articles_in_category = $articleRepo->getArticlesByCategorySlug($category_slug);

// Lấy tên chuyên mục để hiển thị
// Nếu có bài viết, lấy tên từ bài đầu tiên. Nếu không, tạo tên từ slug.
$category_name = !empty($articles_in_category) 
    ? htmlspecialchars($articles_in_category[0]->category_name) 
    : ucfirst(str_replace('-', ' ', $category_slug));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyên mục: <?php echo $category_name; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <div class="container" style="padding: 20px 0;">
        <h1 class="page-title" style="font-size: 24px; border-bottom: 3px solid #78B43D; padding-bottom: 10px; margin-bottom: 25px; text-transform: uppercase;">
            <?php echo $category_name; ?>
        </h1>

        <div class="category-article-list">
            <?php if (!empty($articles_in_category)): ?>
                <?php foreach ($articles_in_category as $article): ?>
                    <article class="list-news-item d-flex" style="margin-bottom: 20px; border-bottom: 1px dotted #ccc; padding-bottom: 15px;">
                        <a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="list-news-img" style="flex: 0 0 220px; margin-right: 20px;">
                            <img src="<?php echo htmlspecialchars($article->image_url ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($article->title); ?>" class="img-fluid" style="aspect-ratio: 16/9; object-fit: cover; border-radius: 4px;">
                        </a>
                        <div class="list-news-info">
                            <h3 class="list-news-title">
                                <a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="fw-bold color-main hover-color-24h" style="font-size: 20px; line-height: 1.3;">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </h3>
                            <p style="font-size: 15px; color: #555; margin-top: 8px;">
                                <?php echo htmlspecialchars($article->summary); ?>
                            </p>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Không tìm thấy bài viết nào trong chuyên mục "<?php echo $category_name; ?>".</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>