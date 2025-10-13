-- ===================================================================================
-- SCRIPT CÀI ĐẶT DATABASE WEBTHETHAO - PHIÊN BẢN HOÀN CHỈNH VÀ ĐA DẠNG
-- Tự động xóa database cũ nếu tồn tại để tránh xung đột.
-- ===================================================================================

-- XÓA DATABASE CŨ NẾU TỒN TẠI VÀ TẠO MỚI
DROP DATABASE IF EXISTS `webthethao`;
CREATE DATABASE `webthethao` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `webthethao`;

-- --------------------------------------------------------

--
-- Bảng Chuyên mục (Categories)
--
CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) UNIQUE NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','editor','user') NOT NULL DEFAULT 'user',
  `display_name` varchar(150) DEFAULT NULL,
  `email` varchar(200) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_users_username` (`username`),
  UNIQUE KEY `uniq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Bảng Bài viết (Articles)
--
CREATE TABLE `articles` (
  `article_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `summary` text,
  `content` text,
  `image_url` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE,
  FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lượt thích bài viết
CREATE TABLE `article_likes` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `article_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Lưu bài viết (bookmark)
CREATE TABLE `article_saves` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `article_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bài viết đã xem (lưu lần xem cuối)
CREATE TABLE `article_views` (
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `viewed_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`, `article_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bình luận bài viết
CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`article_id`) REFERENCES `articles` (`article_id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  INDEX (`article_id`),
  INDEX (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Dữ liệu mẫu
--

INSERT INTO `categories` (`category_id`, `name`, `slug`, `parent_id`) VALUES
(1, 'Thể thao', 'the-thao', NULL),
(2, 'Kinh doanh', 'kinh-doanh', NULL),
(3, 'Thế giới', 'the-gioi', NULL),
(4, 'Giải trí', 'giai-tri', NULL),
(5, 'Bóng đá', 'bong-da', 1),
(6, 'Quần vợt', 'quan-vot', 1),
(7, 'Đua xe', 'dua-xe', 1),
(8, 'Thị trường', 'thi-truong', 2),
(9, 'Startup', 'startup', 2),
(10, 'Video', 'video', NULL),
(11, 'Điện ảnh', 'dien-anh', 4),
(12, 'Âm nhạc', 'am-nhac', 4),
(13, 'Công nghệ', 'cong-nghe', NULL),
(14, 'Du lịch', 'du-lich', NULL);

INSERT INTO `articles` (`article_id`, `category_id`, `title`, `slug`, `summary`, `content`, `image_url`, `is_featured`, `created_at`) VALUES
(1, 5, 'Mbappe chính thức gia nhập Real Madrid với hợp đồng 5 năm', 'mbappe-gia-nhap-real-madrid', 'Sau nhiều năm chờ đợi, cuối cùng siêu sao người Pháp Kylian Mbappe đã trở thành người của Real Madrid.', '<p>Đây là một bản hợp đồng thế kỷ, hứa hẹn sẽ thay đổi cán cân quyền lực của bóng đá châu Âu trong nhiều năm tới.</p><p><img src=\"/webthethao_project/assets/uploads/378ec01e536cdcb8.png\"></p><p class=\"ql-align-center\">Hình ảnh vũ hát cực chill</p><p>Hey&nbsp;<strong>Quách Khải Hậu</strong>,</p><p>Welcome!</p><p>Thank you for joining&nbsp;<a href=\"https://education.github.com/globalcampus/student?email_referrer=true\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">GitHub Education</a>. GitHub Education helps students, teachers, and schools access the tools and resources they need to shape the next generation of software development.</p><p>Congratulations, you are now a GitHub Education student! You can now explore valuable offers provided by GitHub\'s partners in the GitHub Student Developer Pack, view student events, and much more when you sign in at:</p><p><a href=\"https://education.github.com/globalcampus/student?email_referrer=true\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://education.github.com/globalcampus/student?email_referrer=true</a></p><p>If you are renewing your membership please be aware that some of our partner offers are single-use and non-renewable, and that you should contact the partner directly if you have any questions.</p><p>Learn about GitHub Education\'s programs for students, like GitHub Campus Experts and our grants for first-time hackathons:</p><p><a href=\"https://education.github.com/students\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://education.github.com/students</a></p><p>Introduce yourself to the GitHub Education Community:</p><p><a href=\"https://github.com/orgs/github-community/discussions/categories/github-education\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://github.com/orgs/github-community/discussions/categories/github-education</a></p><p>Your teacher or faculty advisor can request private repositories for your class projects or student club. Send them to:</p><p><a href=\"https://help.github.com/categories/teaching-and-learning-with-github-education\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://help.github.com/categories/teaching-and-learning-with-github-education</a></p><p>Give the gift of GitHub Education to your entire school:</p><p><a href=\"https://education.github.com/partners/schools\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://education.github.com/partners/schools</a></p><p>Have an Octotastic day!</p><p class=\"ql-align-justify\">- The GitHub Education Team</p>', '/webthethao_project/assets/uploads/aca32bba04d677f9.png', 0, '2025-10-11 17:57:49'),
(2, 5, 'Kết quả V-League: HAGL chia điểm kịch tính với Hà Nội FC', 'ket-qua-vleague-hagl-hanoi', 'Trận cầu tâm điểm vòng 15 V-League đã diễn ra vô cùng hấp dẫn với màn rượt đuổi tỷ số ngoạn mục.', '<p>Chi tiết trận đấu... jbajDBJLSBJDA  SADSADADASDSAD </p>', '/webthethao_project/assets/uploads/b784a0a6deed5daa.png', 0, '2025-10-11 17:57:49'),
(3, 6, 'Carlos Alcaraz giành chức vô địch Wimbledon sau trận chung kết nghẹt thở', 'alcaraz-vo-dich-wimbledon', 'Tay vợt trẻ người Tây Ban Nha đã xuất sắc đánh bại đối thủ kỳ cựu để lần đầu tiên lên ngôi tại Wimbledon.', '<p><br></p><p>jnnkknkj</p>', '/webthethao_project/assets/uploads/63306f005d22c03d.png', 0, '2025-10-11 17:57:49'),
(4, 7, 'Verstappen về nhất tại Grand Prix Monaco', 'verstappen-ve-nhat-monaco-gp', 'Max Verstappen tiếp tục thể hiện phong độ hủy diệt khi không cho các đối thủ một cơ hội nào.', 'Chi tiết cuộc đua...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(5, 1, 'Lịch thi đấu Euro 2028 hôm nay', 'lich-thi-dau-euro-2028', 'Cập nhật lịch thi đấu, kênh trực tiếp các trận đấu trong khuôn khổ vòng chung kết Euro 2028.', '<p>Chi tiết lịch thi đấu...</p>', '/webthethao_project/assets/uploads/0bb230c8730dc244.png', 0, '2025-10-11 17:57:49'),
(6, 5, 'Phân tích chiến thuật: Liverpool đã vô hiệu hóa Man City như thế nào?', 'phan-tich-chien-thuat-liverpool-mancity', 'HLV Jurgen Klopp đã một lần nữa cho thấy tài năng của mình với một thế trận phòng ngự phản công bậc thầy.', '<p>Chi tiết phân tích...</p>', '/webthethao_project/assets/uploads/a20ae2f7b966e885.png', 0, '2025-10-11 17:57:49'),
(7, 6, 'Federer và Nadal: Nhìn lại cuộc đối đầu vĩ đại nhất lịch sử quần vợt', 'federer-nadal-cuoc-doi-dau-vi-dai', 'Họ không chỉ là đối thủ, họ còn là những người bạn và là nguồn cảm hứng cho hàng triệu người hâm mộ.', '<p>Những khoảnh khắc đáng nhớ...</p>', '/webthethao_project/assets/uploads/9105673755db159a.png', 0, '2025-10-11 17:57:49'),
(8, 8, 'VN-Index vượt mốc 1300 điểm', 'vnindex-vuot-1300-diem', 'Thị trường chứng khoán Việt Nam có một phiên giao dịch bùng nổ, chỉ số VN-Index đã vượt qua ngưỡng cản tâm lý quan trọng.', '<p>Phân tích thị trường...</p>', '/webthethao_project/assets/uploads/2ff79676e89d8e49.png', 0, '2025-10-11 17:57:49'),
(9, 9, 'Startup Việt huy động thành công 10 triệu USD vòng series A', 'startup-viet-goi-von-10-trieu-usd', 'Công ty công nghệ giáo dục Edutech vừa công bố hoàn tất vòng gọi vốn series A do các quỹ đầu tư lớn trong khu vực dẫn dắt.', '<p>Thông tin về startup...</p>', '/webthethao_project/assets/uploads/57799fb0fdddde04.png', 0, '2025-10-11 17:57:49'),
(10, 8, 'Bitcoin biến động mạnh, nhà đầu tư nên làm gì?', 'bitcoin-bien-dong-manh', 'Thị trường tiền điện tử đang trải qua một giai đoạn đầy biến động, đòi hỏi các nhà đầu tư phải hết sức cẩn trọng.', '<p>Lời khuyên từ chuyên gia...</p>', '/webthethao_project/assets/uploads/9ffcf07af01c46db.png', 0, '2025-10-11 17:57:49'),
(11, 2, 'Lãi suất ngân hàng dự báo sẽ tiếp tục tăng nhẹ', 'lai-suat-ngan-hang-tang-nhe', 'Ngân hàng nhà nước có thể sẽ có những điều chỉnh chính sách tiền tệ để kiểm soát lạm phát.', '<p>Phân tích từ các chuyên gia kinh tế...</p>', '/webthethao_project/assets/uploads/f420a8cc28fab7b0.png', 0, '2025-10-11 17:57:49'),
(12, 3, 'Hội nghị thượng đỉnh G7 bàn về các vấn đề khí hậu và an ninh toàn cầu', 'hoi-nghi-g7-khi-hau-an-ninh', 'Các nhà lãnh đạo của 7 nền kinh tế phát triển nhất thế giới đã nhóm họp tại Canada.', '<p>Nội dung chính của hội nghị...</p>', '/webthethao_project/assets/uploads/86c8a060862a5e56.png', 0, '2025-10-11 17:57:49'),
(13, 3, 'NASA công bố kế hoạch đưa người trở lại Mặt Trăng vào năm 2028', 'nasa-tro-lai-mat-trang-2028', 'Chương trình Artemis hứa hẹn sẽ mở ra một kỷ nguyên mới cho việc khám phá không gian của nhân loại.', '<p>Chi tiết kế hoạch...</p>', '/webthethao_project/assets/uploads/c44b46f2a1a6bbe2.png', 0, '2025-10-11 17:57:49'),
(14, 13, 'Apple ra mắt iPhone 18 với chip A20 Bionic và màn hình ProMotion 2.0', 'apple-ra-mat-iphone-18', 'Gã khổng lồ công nghệ vừa trình làng thế hệ iPhone mới với nhiều nâng cấp đáng giá về hiệu năng và camera.', 'Chi tiết về sản phẩm...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(15, 13, 'Cuộc đua phát triển AI tạo sinh: OpenAI, Google và những kẻ thách thức', 'cuoc-dua-ai-tao-sinh', 'Trí tuệ nhân tạo tạo sinh đang là chiến trường khốc liệt nhất của các ông lớn công nghệ.', 'Phân tích cuộc đua công nghệ...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(16, 11, 'Review phim \"Dune 2\": Siêu phẩm điện ảnh xứng đáng với mọi lời khen', 'review-dune-2', 'Denis Villeneuve một lần nữa đã không làm khán giả thất vọng với một tác phẩm hoành tráng và sâu sắc.', '<p>Đánh giá chi tiết bộ phim<img src=\"/webthethao_project/assets/uploads/95be56e6d38ba8ca.jpg\"><img src=\"/webthethao_project/assets/uploads/63628a7c8be24a93.jpg\"><img src=\"/webthethao_project/assets/uploads/fcedbb288ff59dd0.png\"><img src=\"/webthethao_project/assets/uploads/753ed9f0a3b1d250.jpg\"></p>', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(17, 12, 'Album mới của Sơn Tùng M-TP phá vỡ nhiều kỷ lục nhạc số', 'son-tung-mtp-album-moi-ky-luc', 'Sản phẩm âm nhạc mới nhất của nam ca sĩ đã nhanh chóng chiếm lĩnh các bảng xếp hạng trong và ngoài nước.', 'Thành tích của album...', 'assets/market_news_2.jpg', 0, '2025-10-11 17:57:49'),
(18, 11, 'Phim \"Lật Mặt 7\" của Lý Hải đạt doanh thu 500 tỷ đồng', 'lat-mat-7-doanh-thu-500-ty', 'Tác phẩm mới nhất của đạo diễn Lý Hải tiếp tục tạo nên một cơn sốt phòng vé chưa từng có tại Việt Nam.', 'Chi tiết về thành công của bộ phim...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(19, 12, 'Blackpink công bố tour diễn toàn cầu, có điểm dừng tại Việt Nam', 'blackpink-tour-toan-cau-viet-nam', 'Cộng đồng Blink Việt Nam đang vỡ òa trước thông tin nhóm nhạc nữ hàng đầu thế giới sẽ biểu diễn tại sân vận động Mỹ Đình.', 'Chi tiết về concert...', 'assets/market_news_3.jpg', 0, '2025-10-11 17:57:49'),
(20, 14, 'Top 5 địa điểm phải đến ở Hà Giang mùa lúa chín', 'top-5-dia-diem-ha-giang', 'Hà Giang tháng 9, tháng 10 đẹp như một bức tranh thủy mặc với những thửa ruộng bậc thang vàng óng.', 'Gợi ý lịch trình...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(21, 14, 'Kinh nghiệm du lịch Phú Quốc tự túc cho người mới đi lần đầu', 'kinh-nghiem-du-lich-phu-quoc', 'Tất tần tật những gì bạn cần biết về di chuyển, ăn ở, vui chơi tại Đảo Ngọc.', 'Hướng dẫn chi tiết...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(22, 10, 'Video: Top 5 bàn thắng đẹp nhất tuần qua', 'video-top-5-ban-thang-dep', 'Cùng chiêm ngưỡng lại những siêu phẩm làm nức lòng người hâm mộ trên khắp các sân cỏ thế giới.', 'Video tổng hợp...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(23, 10, 'Video: Khám phá hang Sơn Đoòng - kỳ quan thiên nhiên của thế giới', 'video-kham-pha-son-doong', 'Những thước phim ngoạn mục ghi lại vẻ đẹp hùng vĩ và choáng ngợp bên trong hang động lớn nhất thế giới.', 'Video du lịch khám phá...', 'assets/placeholder.jpg', 0, '2025-10-11 17:57:49'),
(24, 1, 'Không biết luôn', 'khong-biet-luon', 'Khong co gi dac biet', '<p>sdadasdsadasdd</p><p><img src=\"/webthethao_project/assets/uploads/f95984394c8438d5.png\"></p><p class=\"ql-align-center\">ssadsad</p><p>Hey&nbsp;<strong>Quách Khải Hậu</strong>,</p><p>Welcome!</p><p>Thank you for joining&nbsp;<a href=\"https://education.github.com/globalcampus/student?email_referrer=true\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">GitHub Education</a>. GitHub Education helps students, teachers, and schools access the tools and resources they need to shape the next generation of software development.</p><p>Congratulations, you are now a GitHub Education student! You can now explore valuable offers provided by GitHub\'s partners in the GitHub Student Developer Pack, view student events, and much more when you sign in at:</p><p><a href=\"https://education.github.com/globalcampus/student?email_referrer=true\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://education.github.com/globalcampus/student?email_referrer=true</a></p><p>If you are renewing your membership please be aware that some of our partner offers are single-use and non-renewable, and that you should contact the partner directly if you have any questions.</p><p>Learn about GitHub Education\'s programs for students, like GitHub Campus Experts and our grants for first-time hackathons:</p><p><a href=\"https://education.github.com/students\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://education.github.com/students</a></p><p>Introduce yourself to the GitHub Education Community:</p><p><a href=\"https://github.com/orgs/github-community/discussions/categories/github-education\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://github.com/orgs/github-community/discussions/categories/github-education</a></p><p>Your teacher or faculty advisor can request private repositories for your class projects or student club. Send them to:</p><p><a href=\"https://help.github.com/categories/teaching-and-learning-with-github-education\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://help.github.com/categories/teaching-and-learning-with-github-education</a></p><p>Give the gift of GitHub Education to your entire school:</p><p><a href=\"https://education.github.com/partners/schools\" rel=\"noopener noreferrer\" target=\"_blank\" style=\"color: rgb(65, 131, 196);\">https://education.github.com/partners/schools</a></p><p>Have an Octotastic day!</p><p class=\"ql-align-justify\">- The GitHub Education Team</p>', '/webthethao_project/assets/uploads/1d0138293e1897f4.png', 1, '2025-10-11 19:39:51');
