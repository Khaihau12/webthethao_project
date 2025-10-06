<?php
// Tên file: article.php
require_once __DIR__ . '/includes/db_config.php';

$slug = $_GET['slug'] ?? ''; 
$article = null;

if ($slug) {
    $article = getArticleBySlug($conn, $slug);
}
$article_images = [];
if ($slug && $article) {
    $article_images = getArticleImages($conn, $article['id']);
    // Inject images into content (tokens like [IMAGE:key])
    $article['content'] = injectArticleImages($article['content'], $article_images);
}

$conn->close();

$page_title = $article ? htmlspecialchars($article['title']) : "Không tìm thấy bài viết";
$category = $article ? htmlspecialchars($article['category']) : "Tin tức";
$article_date = $article ? date('d/m/Y', strtotime($article['created_at'])) : '';
$article_time = $article ? date('H:i A (GMT+7)', strtotime($article['created_at'])) : '';
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

    <div class="container sub-nav-menu-container">
        <ul class="sub-nav-menu">
            <li><a href="#">TIN TỨC TRONG NGÀY</a></li>
            <li><a href="#">Chính trị - Xã hội</a></li>
            <li><a href="#">Tra cứu phường xã</a></li>
            <li><a href="#">Đời sống - Dân sinh</a></li>
            <li><a href="#">Giao thông - Đô thị</a></li>
            <li><a href="#">Nóng trên mạng</a></li>
        </ul>
    </div>

    <div class="container content-area d-flex">
    <div class="main-column col-8 main-column-pad">
            <?php if ($article): ?>
                
                <div class="breadcrumb">
                    <a href="index.php">Trang chủ</a> &raquo; 
                    <a href="#">Tin tức trong ngày</a> &raquo; 
                    <span class="active"><?php echo $category; ?></span>
                </div>
                
                <p style="font-size: 14px; margin: 5px 0 15px; color: #888;">
                    Thứ Hai, <?php echo $article_date; ?> <?php echo $article_time; ?>
                </p>

                <article class="full-article">
                    <div class="article-header">
                            <h1 class="article-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                        
                            <div class="article-meta-info d-flex justify-content-between align-items-center">
                                <span class="meta-left">
                                    Chuyên mục: <span class="date-time"><?php echo $category; ?></span>
                                    &nbsp;•&nbsp;
                                    <span class="date-time"><?php echo $article_date; ?> <?php echo $article_time; ?></span>
                                </span>
                                <div class="action-buttons">
                                    <button class="save-btn"><i class="fa fa-bookmark"></i> LƯU BÀI</button>
                                    <button class="share-btn"><i class="fa fa-share-alt"></i> CHIA SẺ</button>
                                </div>
                            </div>
                        </div>
                    
                        <div class="article-content">
                            <?php if (!empty($article['image_url'])): ?>
                                <div style="margin-bottom:15px; text-align:center;">
                                    <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" style="max-width:100%; height:auto;" />
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($article['summary'])): ?>
                                <p style="font-weight: bold; color: #000; font-size: 19px; line-height: 1.5; margin-bottom: 20px;">
                                    <?php echo htmlspecialchars($article['summary']); ?>
                                </p>
                            <?php endif; ?>

                            <!-- Preview + full content: show short text preview first, then reveal full HTML when user clicks -->
                            <div id="article-preview" class="article-body"></div>
                            <div id="article-full" class="article-body" style="display:none;">
                                <?php echo nl2br($article['content']); ?>
                            </div>
                            <div style="text-align:center; margin-top:12px;">
                                <button id="read-more-btn" class="read-more-btn" style="display:none;">Xem thêm</button>
                            </div>
                            <script>
                            (function(){
                                var full = document.getElementById('article-full');
                                var preview = document.getElementById('article-preview');
                                var btn = document.getElementById('read-more-btn');
                                if (!full) return;
                                // Use textContent to create a safe text-only preview (won't break HTML)
                                var fullText = (full.textContent || full.innerText || '').trim();
                                var maxChars = 900; // adjust preview length
                                if (fullText.length <= maxChars) {
                                    // short article: show full content, hide preview/button
                                    preview.style.display = 'none';
                                    btn.style.display = 'none';
                                    full.style.display = 'block';
                                    return;
                                }
                                // create preview text
                                preview.textContent = fullText.slice(0, maxChars) + '...';
                                preview.style.whiteSpace = 'pre-wrap';
                                // show button
                                btn.style.display = 'inline-block';
                                btn.addEventListener('click', function(){
                                    preview.style.display = 'none';
                                    btn.style.display = 'none';
                                    full.style.display = 'block';
                                });
                            })();
                            </script>
                        </div>
                </article>
            <?php else: ?>
                <h1 class="article-title-error">404 - KHÔNG TÌM THẤY BÀI VIẾT</h1>
            <?php endif; ?>
        </div>

        <aside class="sidebar-column col-4">
            <div class="market-news-block">
                <h3 style="font-weight: bold; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #78B43D; padding-bottom: 5px; color: #78B43D;">TIN TỨC THỊ TRƯỜNG</h3>
                <div class="market-news-item d-flex">
                    <div class="market-thumb" style="flex: 0 0 80px;"><img src="assets/market_news_1.jpg" alt="" style="width: 100%; height: auto;"></div>
                    <div class="market-info" style="padding-left: 10px;"><p><a href="#" class="color-main hover-color-24h">Viettel Money ra mắt bảo hiểm chuyến đi cho khách hàng ePass...</a></p></div>
                </div>
                <div class="market-news-item d-flex">
                    <div class="market-thumb" style="flex: 0 0 80px;"><img src="assets/market_news_2.jpg" alt="" style="width: 100%; height: auto;"></div>
                    <div class="market-info" style="padding-left: 10px;"><p><a href="#" class="color-main hover-color-24h">Khoác hòa giữa kết nối và riêng tư ở The Fullton Edition</a></p></div>
                </div>
                <div class="xem-them" style="text-align: right; margin-top: 15px;"><a href="#" style="color: #007bff; font-weight: bold;">Xem thêm »</a></div>
            </div>
            
            <div class="latest-news-block" style="margin-top: 30px;">
                <h3 style="font-weight: bold; font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #78B43D; padding-bottom: 5px; color: #78B43D;">THẾ GIỚI</h3>
            </div>
        </aside>
    </div>
</body>
</html>