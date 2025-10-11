<?php
// Tên file: article.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Helpers.php';
require_once __DIR__ . '/classes/InteractionRepository.php';

// Khởi tạo
$db = Database::getInstance();
$conn = $db->getConnection();
$articleRepo = new ArticleRepository($conn);
$auth = new Auth($conn);
$interRepo = new InteractionRepository($conn);
$current_user = $auth->currentUser();

$slug = $_GET['slug'] ?? ''; 
$article = null;

if ($slug) {
    $article = $articleRepo->getArticleBySlug($slug);
}

// Record view for logged-in user
if ($article && $current_user) {
    $interRepo->touchView($current_user->id, (int)$article->id);
}

// Handle interactions
if ($article && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
        http_response_code(400);
        die('CSRF không hợp lệ.');
    }
    if (!$current_user) {
        header('Location: /webthethao_project/user_login.php');
        exit;
    }
    if (isset($_POST['like_toggle'])) {
        $interRepo->toggleLike($current_user->id, (int)$article->id);
        header('Location: article.php?slug=' . urlencode($slug));
        exit;
    }
    if (isset($_POST['save_toggle'])) {
        $interRepo->toggleSave($current_user->id, (int)$article->id);
        header('Location: article.php?slug=' . urlencode($slug));
        exit;
    }
    if (isset($_POST['comment_content'])) {
        $content = trim($_POST['comment_content']);
        if ($content !== '') { $interRepo->addComment($current_user->id, (int)$article->id, $content); }
        header('Location: article.php?slug=' . urlencode($slug));
        exit;
    }
}

// Lấy 5 tin mới nhất cho sidebar
$sidebar_articles = $articleRepo->getLatestArticles(5, true);

// Chuẩn bị các biến để hiển thị
$page_title = $article ? htmlspecialchars($article->title) : "Không tìm thấy bài viết";
$category_name = $article ? htmlspecialchars($article->category_name) : "Tin tức";
$category_slug = $article ? htmlspecialchars($article->category_slug) : "";
$article_date = $article ? date('d/m/Y', strtotime($article->created_at)) : '';
$article_time = $article ? date('H:i A (GMT+7)', strtotime($article->created_at)) : '';
$like_count = $article ? $interRepo->likeCount((int)$article->id) : 0;
$user_liked = ($article && $current_user) ? $interRepo->isLiked($current_user->id, (int)$article->id) : false;
$user_saved = ($article && $current_user) ? $interRepo->isSaved($current_user->id, (int)$article->id) : false;
$comments = $article ? $interRepo->getComments((int)$article->id, 50, 0) : [];
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
                            <?php echo $article->content; ?>
                        </div>
                    </div>
                </article>
                <?php if ($article): ?>
                <section class="article-actions" style="margin-top:16px;border-top:1px solid #eee;padding-top:12px;">
                    <form method="post" style="display:inline">
                        <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
                        <button type="submit" name="like_toggle" value="1" style="padding:6px 10px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer">
                            <?php echo $user_liked ? '❤️ Đã thích' : '🤍 Thích'; ?> (<?php echo (int)$like_count; ?>)
                        </button>
                    </form>
                    <form method="post" style="display:inline;margin-left:8px;">
                        <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
                        <button type="submit" name="save_toggle" value="1" style="padding:6px 10px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer">
                            <?php echo $user_saved ? '🔖 Đã lưu' : '📌 Lưu đọc sau'; ?>
                        </button>
                    </form>
                </section>

                <section class="comments" style="margin-top:20px;">
                    <h3>Bình luận</h3>
                    <?php if ($current_user): ?>
                    <form method="post" style="margin-bottom:12px;">
                        <input type="hidden" name="csrf" value="<?php echo Helpers::csrfToken(); ?>" />
                        <textarea name="comment_content" placeholder="Viết bình luận" style="width:100%;min-height:80px;padding:10px;border:1px solid #ddd;border-radius:6px;"></textarea>
                        <button type="submit" style="margin-top:8px;padding:8px 12px;background:#78B43D;color:#fff;border:none;border-radius:6px">Gửi</button>
                    </form>
                    <?php else: ?>
                        <p><a href="/webthethao_project/user_login.php">Đăng nhập</a> để bình luận.</p>
                    <?php endif; ?>
                    <div class="comment-list">
                        <?php foreach ($comments as $c): ?>
                        <div style="border-bottom:1px dashed #eee;padding:8px 0;">
                            <div style="font-weight:500;"><?php echo htmlspecialchars($c['display_name'] ?: $c['username']); ?> <span style="color:#999;font-size:12px;">• <?php echo htmlspecialchars($c['created_at']); ?></span></div>
                            <div><?php echo nl2br(htmlspecialchars($c['content'])); ?></div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($comments)): ?><p>Chưa có bình luận.</p><?php endif; ?>
                    </div>
                </section>
                <?php endif; ?>
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
$__footer = __DIR__ . '/includes/footer.php'; if (file_exists($__footer)) require $__footer;
$conn->close();
?>