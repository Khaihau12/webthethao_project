<?php
// Tên file: index.php
require_once __DIR__ . '/includes/db_config.php';

// LẤY DỮ LIỆU ĐỘNG CHO TẤT CẢ CÁC KHỐI
$featured_article = getFeaturedArticle($conn);
$top_main_articles = getTopMainArticles($conn, 4); 
$video_articles = getVideoArticles($conn, 3); 
$business_articles = getBusinessArticles($conn, 4); 
$sidebar_articles = getSidebarArticles($conn); 
$market_articles = getMarketArticles($conn, 4); 

$conn->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức bóng đá, thể thao, giải trí | Đọc tin tức 24h mới nhất</title>
    <meta name="description" content="Tin tức 24h về bóng đá, thể thao, giải trí. Tin tức online 24 giờ, tình hình Việt Nam(VN), thế giới. Xem video bóng đá tổng hợp tại 24h." />
    <meta name="keywords" content="tin tức 24h, tin tuc 24h, bóng đá, thể thao, giải trí" />
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <div class="container main-content-24h" style="padding-top: 20px;">
        <div class="row d-flex">
            
            <div class="col-8 main-column main-column-pad">
                
                <section class="hightl-24h-block d-flex">
                    <?php if ($featured_article): ?>
                    <div class="hightl-24h-big hightl-24h-big--col">
                        <a href="article.php?slug=<?php echo htmlspecialchars($featured_article['slug']); ?>">
                            <img src="<?php echo htmlspecialchars($featured_article['image_url'] ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($featured_article['title']); ?>" class="img-fluid hightl-img-big" onerror="this.onerror=null;this.src='assets/placeholder.jpg';">
                        </a>
                        <h2 class="hightl-title-big">
                            <a href="article.php?slug=<?php echo htmlspecialchars($featured_article['slug']); ?>" class="fw-bold color-main hover-color-24h">
                                <?php echo htmlspecialchars($featured_article['title']); ?>
                            </a>
                        </h2>
                        <p class="hightl-summary"><?php echo htmlspecialchars($featured_article['summary']); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="hightl-24h-list" style="flex: 1; padding-left: 20px;">
                        <?php foreach ($top_main_articles as $article): ?>
                        <article class="hightl-24h-items" style="margin-bottom: 15px;">
                            <span class="hightl-24h-items-cate d-block mar-b-5">
                                <a href="#" class="color-24h"><?php echo htmlspecialchars($article['category']); ?></a>
                            </span>
                            <h3>
                                <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="d-block fw-medium color-main hover-color-24h">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a>
                            </h3>
                        </article>
                        <?php endforeach; ?>
                        
                        <div class="video-news-block" style="padding-top: 15px; border-top: 1px dashed #ccc;">
                            <header class="video-news-tit fw-bold" style="color: #c00;">Tin tức video</header>
                            <?php if (!empty($video_articles)): ?>
                                <?php foreach ($video_articles as $article): ?>
                                <p class="video-item" style="margin-top: 5px;"><i class="fa fa-play-circle" style="color: #c00;"></i> 
                                    <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="color-main hover-color-24h">
                                        <?php echo htmlspecialchars($article['title']); ?>
                                    </a>
                                </p>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="video-item">Chưa có tin tức video nào.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                
                <!-- Tin tức trong ngày: feature + three small items -->
                <section class="tin-tuc-trong-ngay mar-t-40">
                    <header class="tin-tuc-tit">
                        <h2>TIN TỨC TRONG NGÀY</h2>
                        <nav class="tin-tuc-nav">
                            <a href="#">An ninh - Xã hội</a>
                            <a href="#">Nhịp sống 24H</a>
                        </nav>
                    </header>

                    <?php if (!empty($sidebar_articles)): ?>
                    <?php $ft = array_shift($sidebar_articles); ?>
                    <div class="tin-tuc-grid">
                        <article class="tin-feature">
                            <a href="article.php?slug=<?php echo htmlspecialchars($ft['slug']); ?>">
                                <img src="<?php echo htmlspecialchars($ft['image_url'] ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($ft['title']); ?>" onerror="this.onerror=null;this.src='assets/placeholder.jpg';">
                            </a>
                            <div class="tin-feature-body">
                                <h3><a href="article.php?slug=<?php echo htmlspecialchars($ft['slug']); ?>"><?php echo htmlspecialchars($ft['title']); ?></a></h3>
                                <p class="muted"><?php echo htmlspecialchars($ft['summary'] ?? ''); ?></p>
                            </div>
                        </article>
                    </div>

                    <div class="tin-small-list">
                        <?php for ($i=0; $i<3; $i++): if (empty($sidebar_articles[$i])) break; $s = $sidebar_articles[$i]; ?>
                        <article class="tin-small-item">
                            <a href="article.php?slug=<?php echo htmlspecialchars($s['slug']); ?>">
                                <img src="<?php echo htmlspecialchars($s['image_url'] ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($s['title']); ?>" onerror="this.onerror=null;this.src='assets/placeholder.jpg';">
                            </a>
                            <h4><a href="article.php?slug=<?php echo htmlspecialchars($s['slug']); ?>"><?php echo htmlspecialchars($s['title']); ?></a></h4>
                        </article>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                </section>

                <hr style="margin: 30px 0; border: 0; border-top: 5px solid #eee;">
                
                <section class="cate-news-24h-r mar-t-40">
                    <div class="box-t d-flex align-items-center mar-b-15">
                        <header class="cate-news-24h-r__tit color-24h flex-auto pos-rel">
                            <h2 class="fw-bold text-uppercase">
                                <a class="color-green-custom" href="#"> KINH DOANH </a>
                            </h2>
                        </header>
                    </div>
                    
                    <div class="list-news-24h row">
                        <?php if (!empty($business_articles)): ?>
                            <?php foreach ($business_articles as $article): ?>
                            <div class="col-6" style="margin-bottom: 20px;">
                                <article class="list-news-item d-flex">
                                    <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="list-news-img">
                                        <img src="<?php echo htmlspecialchars($article['image_url'] ?? 'assets/placeholder_small.jpg'); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="img-fluid">
                                    </a>
                                    <div class="list-news-info">
                                        <h3 class="list-news-title"><a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="fw-bold color-main hover-color-24h"><?php echo htmlspecialchars($article['title']); ?></a></h3>
                                    </div>
                                </article>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="col-12">Chưa có tin tức Kinh Doanh.</p>
                        <?php endif; ?>
                    </div>
                </section>
                
            </div> <div class="col-4 sidebar-column">
                
                <aside class="latest-news-block">
                    <header class="latest-news-tit fw-bold d-inline-block padd-t-10 mar-b-15">
                        <h2 class="fw-bold text-uppercase color-green-custom">Tin tức trong ngày</h2>
                    </header>
                    
                    <div class="latest-news-list">
                        <?php if (!empty($sidebar_articles)): ?>
                            <?php foreach ($sidebar_articles as $article): ?>
                            <div class="sidebar-article">
                                <h4 style="font-size: 11px; color: #888; text-transform: uppercase;"><?php echo htmlspecialchars($article['category']); ?></h4>
                                <p><a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>" class="color-main hover-color-24h">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </a></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Chưa có tin tức trong ngày.</p>
                        <?php endif; ?>
                    </div>
                </aside>
                
                <hr style="margin: 20px 0;">

                <aside class="market-news-block">
                    <header class="latest-news-tit fw-bold d-inline-block padd-t-10 mar-b-15">
                        <h2 class="fw-bold text-uppercase color-green-custom">Tin tức thị trường</h2>
                    </header>
                    
                    <div class="ttdn-24h-b__slide">
                        <?php if (!empty($market_articles)): ?>
                            <?php foreach ($market_articles as $article): ?>
                            <article class="tttt-24h-r-sma d-flex">
                                <figure class="tttt-24h-r-sma__img pos-rel mar-r-10">
                                    <a href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>"> 
                                        <img src="<?php echo htmlspecialchars($article['image_url'] ?? 'assets/placeholder_market.jpg'); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="width-100" /> 
                                    </a> 
                                </figure> 
                                <header class="tttt-24h-r-sma__tit flex-1 mw-0"> 
                                    <h3> 
                                        <a class="d-block fw-medium color-main hover-color-24h" href="article.php?slug=<?php echo htmlspecialchars($article['slug']); ?>">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a> 
                                    </h3> 
                                </header> 
                            </article>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>Chưa có tin tức thị trường.</p>
                        <?php endif; ?>
                    </div>
                </aside>

            </div> </div> </div> </body>
</html>