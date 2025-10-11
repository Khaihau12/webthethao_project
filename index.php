<?php
// Tên file: index.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Article.php';
require_once __DIR__ . '/classes/ArticleRepository.php';
require_once __DIR__ . '/classes/CategoryRepository.php';

// Khởi tạo
$db = Database::getInstance();
$conn = $db->getConnection();
$articleRepo = new ArticleRepository($conn);
$categoryRepo = new CategoryRepository($conn);

// Lấy dữ liệu cho các khối
$featured_article = $articleRepo->getFeaturedArticle();
$top_main_articles = $articleRepo->getLatestArticles(4, true);
$sidebar_articles = $articleRepo->getLatestArticles(7, true);
$video_articles = $articleRepo->getArticlesByCategorySlug('video', 3);
$market_articles = $articleRepo->getArticlesByCategorySlug('thi-truong', 4);

// Lấy dữ liệu cho khối THỂ THAO
$the_thao_section = $articleRepo->getArticlesForCategorySection('the-thao', 1, 3);
$the_thao_sub_categories = $categoryRepo->getChildCategories('the-thao');

// Lấy dữ liệu cho khối KINH DOANH
$kinh_doanh_articles = $articleRepo->getArticlesByCategorySlug('kinh-doanh', 4);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức bóng đá, thể thao, giải trí | Đọc tin tức 24h mới nhất</title>
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
                        <a href="article.php?slug=<?php echo htmlspecialchars($featured_article->slug); ?>">
                            <img src="<?php echo htmlspecialchars($featured_article->image_url ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($featured_article->title); ?>" class="img-fluid hightl-img-big">
                        </a>
                        <h2 class="hightl-title-big">
                            <a href="article.php?slug=<?php echo htmlspecialchars($featured_article->slug); ?>" class="fw-bold color-main hover-color-24h"><?php echo htmlspecialchars($featured_article->title); ?></a>
                        </h2>
                        <p class="hightl-summary"><?php echo htmlspecialchars($featured_article->summary); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="hightl-24h-list" style="flex: 1; padding-left: 20px;">
                        <?php foreach ($top_main_articles as $article): ?>
                        <article class="hightl-24h-items" style="margin-bottom: 15px;">
                            <span class="hightl-24h-items-cate d-block mar-b-5">
                                <a href="category.php?slug=<?php echo htmlspecialchars($article->category_slug); ?>" class="color-24h"><?php echo htmlspecialchars($article->category_name); ?></a>
                            </span>
                            <h3>
                                <a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="d-block fw-medium color-main hover-color-24h"><?php echo htmlspecialchars($article->title); ?></a>
                            </h3>
                        </article>
                        <?php endforeach; ?>
                        
                        <div class="video-news-block" style="padding-top: 15px; border-top: 1px dashed #ccc;">
                            <header class="video-news-tit fw-bold" style="color: #c00;">Tin tức video</header>
                            <?php foreach ($video_articles as $article): ?>
                            <p class="video-item" style="margin-top: 5px;"><i class="fa fa-play-circle" style="color: #c00;"></i> 
                                <a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="color-main hover-color-24h">
                                    <?php echo htmlspecialchars($article->title); ?>
                                </a>
                            </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <section class="category-showcase-block">
                    <header class="category-showcase-header">
                        <h2 class="category-title"><a href="category.php?slug=the-thao">THỂ THAO</a></h2>
                        <nav class="sub-category-nav">
                            <?php foreach ($the_thao_sub_categories as $sub_cat): ?>
                                <a href="category.php?slug=<?php echo htmlspecialchars($sub_cat['slug']); ?>"><?php echo htmlspecialchars($sub_cat['name']); ?></a>
                            <?php endforeach; ?>
                        </nav>
                    </header>
                    <div class="category-showcase-content">
                        <?php if ($the_thao_section['main_article']): $main = $the_thao_section['main_article']; ?>
                        <article class="showcase-top-story">
                            <a href="article.php?slug=<?php echo htmlspecialchars($main->slug); ?>" class="story-image">
                                <img src="<?php echo htmlspecialchars($main->image_url ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($main->title); ?>">
                            </a>
                            <div class="story-content">
                                <h3><a href="article.php?slug=<?php echo htmlspecialchars($main->slug); ?>"><?php echo htmlspecialchars($main->title); ?></a></h3>
                                <p><?php echo htmlspecialchars($main->summary); ?></p>
                            </div>
                        </article>
                        <?php endif; ?>
                        <div class="showcase-bottom-stories">
                            <?php foreach($the_thao_section['sub_articles'] as $sub): ?>
                            <article class="story-small">
                                <a href="article.php?slug=<?php echo htmlspecialchars($sub->slug); ?>" class="story-image">
                                    <img src="<?php echo htmlspecialchars($sub->image_url ?? 'assets/placeholder.jpg'); ?>" alt="<?php echo htmlspecialchars($sub->title); ?>">
                                </a>
                                <h4><a href="article.php?slug=<?php echo htmlspecialchars($sub->slug); ?>"><?php echo htmlspecialchars($sub->title); ?></a></h4>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
                
                <hr style="margin: 30px 0; border: 0; border-top: 5px solid #eee;">
                
                <section class="cate-news-24h-r mar-t-40">
                    <div class="box-t d-flex align-items-center mar-b-15">
                        <header class="cate-news-24h-r__tit color-24h flex-auto pos-rel">
                            <h2 class="fw-bold text-uppercase">
                                <a class="color-green-custom" href="category.php?slug=kinh-doanh"> KINH DOANH </a>
                            </h2>
                        </header>
                    </div>
                    <div class="list-news-24h row">
                        <?php foreach ($kinh_doanh_articles as $article): ?>
                        <div class="col-6" style="margin-bottom: 20px;">
                            <article class="list-news-item d-flex">
                                <a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="list-news-img">
                                    <img src="<?php echo htmlspecialchars($article->image_url ?? 'assets/placeholder_small.jpg'); ?>" alt="<?php echo htmlspecialchars($article->title); ?>" class="img-fluid">
                                </a>
                                <div class="list-news-info">
                                    <h3 class="list-news-title"><a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="fw-bold color-main hover-color-24h"><?php echo htmlspecialchars($article->title); ?></a></h3>
                                </div>
                            </article>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                
            </div> 
            
            <div class="col-4 sidebar-column">
                <aside class="latest-news-block">
                    <header class="latest-news-tit fw-bold d-inline-block padd-t-10 mar-b-15">
                        <h2 class="fw-bold text-uppercase color-green-custom">Tin tức trong ngày</h2>
                    </header>
                    <div class="latest-news-list">
                        <?php foreach ($sidebar_articles as $article): ?>
                        <div class="sidebar-article">
                            <h4 style="font-size: 11px; color: #888; text-transform: uppercase;"><?php echo htmlspecialchars($article->category_name); ?></h4>
                            <p><a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>" class="color-main hover-color-24h"><?php echo htmlspecialchars($article->title); ?></a></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </aside>
                
                <hr style="margin: 20px 0;">

                <aside class="market-news-block">
                    <header class="latest-news-tit fw-bold d-inline-block padd-t-10 mar-b-15">
                        <h2 class="fw-bold text-uppercase color-green-custom">Tin tức thị trường</h2>
                    </header>
                    <div class="ttdn-24h-b__slide">
                        <?php foreach ($market_articles as $article): ?>
                        <article class="tttt-24h-r-sma d-flex">
                            <figure class="tttt-24h-r-sma__img pos-rel mar-r-10">
                                <a href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>"> 
                                    <img src="<?php echo htmlspecialchars($article->image_url ?? 'assets/placeholder_market.jpg'); ?>" alt="<?php echo htmlspecialchars($article->title); ?>" class="width-100" /> 
                                </a> 
                            </figure> 
                            <header class="tttt-24h-r-sma__tit flex-1 mw-0"> 
                                <h3><a class="d-block fw-medium color-main hover-color-24h" href="article.php?slug=<?php echo htmlspecialchars($article->slug); ?>"><?php echo htmlspecialchars($article->title); ?></a></h3> 
                            </header> 
                        </article>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</body>
</html>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php $conn->close(); ?>