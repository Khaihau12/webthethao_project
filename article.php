<?php
// Tên file: article.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/ArticleRepository.php';

// Khởi tạo
$db = Database::getInstance();
$conn = $db->getConnection();
$articleRepo = new ArticleRepository($conn);

$slug = $_GET['slug'] ?? ''; 
$article = null;

if ($slug) {
    $article = $articleRepo->getArticleBySlug($slug);
}

// Lấy 5 tin mới nhất cho sidebar
$sidebar_articles = $articleRepo->getLatestArticles(5, true);

// Chuẩn bị các biến để hiển thị
$page_title = $article ? htmlspecialchars($article->title) : "Không tìm thấy bài viết";
$category_name = $article ? htmlspecialchars($article->category_name) : "Tin tức";
$category_slug = $article ? htmlspecialchars($article->category_slug) : "";
$article_date = $article ? date('d/m/Y', strtotime($article->created_at)) : '';
$article_time = $article ? date('H:i A (GMT+7)', strtotime($article->created_at)) : '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Web Thể Thao</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <div class="container content-area d-flex" style="padding-top: 20px;">
        <main class="main-column col-8 main-column-pad">
            <?php if ($article): ?>
                
                <div class="breadcrumb">
                    <a href="index.php">Trang chủ</a> &raquo; 
                    <a href="category.php?slug=<?php echo $category_slug; ?>"><?php echo $category_name; ?></a>
                </div>
                
                <article class="full-article">
                    <div class="article-header">
                        <h1 class="article-title"><?php echo htmlspecialchars($article->title); ?></h1>
                        <div class="article-meta-info d-flex justify-content-between align-items-center">
                            <span class="meta-left">
                                Chuyên mục: <span class="date-time"><?php echo $category_name; ?></span>
                                &nbsp;•&nbsp;
                                <span class="date-time"><?php echo $article_date; ?> <?php echo $article_time; ?></span>
                            </span>
                        </div>
                    </div>
                
                    <div class="article-content">
                        <?php if (!empty($article->summary)): ?>
                            <p style="font-weight: bold; color: #000; font-size: 19px; line-height: 1.5; margin-bottom: 20px;">
                                <?php echo htmlspecialchars($article->summary); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="article-body">
                            <?php echo nl2br($article->content); ?>
                        </div>
                    </div>
                </article>
            <?php else: ?>
                <h1 class="article-title-error">404 - KHÔNG TÌM THẤY BÀI VIẾT</h1>
                <p>Bài viết bạn đang tìm kiếm có thể đã bị xóa hoặc đường dẫn không còn tồn tại.</p>
            <?php endif; ?>
        </main>

        <aside class="sidebar-column col-4">
            <div class="latest-news-block">
                <header class="latest-news-tit fw-bold d-inline-block padd-t-10 mar-b-15">
                    <h2 class="fw-bold text-uppercase color-green-custom">Tin mới nhất</h2>
                </header>
                <div class="latest-news-list">
                    <?php foreach ($sidebar_articles as $side_article): ?>
                        <?php if ($article && $side_article->id == $article->id) continue; // Bỏ qua bài đang đọc ?>
                        <div class="sidebar-article">
                            <h4 style="font-size: 11px; color: #888; text-transform: uppercase;"><?php echo htmlspecialchars($side_article->category_name); ?></h4>
                            <p><a href="article.php?slug=<?php echo htmlspecialchars($side_article->slug); ?>" class="color-main hover-color-24h"><?php echo htmlspecialchars($side_article->title); ?></a></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>
<?php
$conn->close();
?>