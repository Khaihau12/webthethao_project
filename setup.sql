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
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) UNIQUE NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Bảng Bài viết (Articles)
--
CREATE TABLE `articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) UNIQUE NOT NULL,
  `summary` text,
  `content` text,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Dữ liệu mẫu
--

INSERT INTO `categories` (`id`, `name`, `slug`, `parent_id`) VALUES
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

INSERT INTO `articles` (`category_id`, `title`, `slug`, `summary`, `content`, `is_featured`, `image_url`) VALUES
-- Tin nổi bật
(5, 'Mbappe chính thức gia nhập Real Madrid với hợp đồng 5 năm', 'mbappe-gia-nhap-real-madrid', 'Sau nhiều năm chờ đợi, cuối cùng siêu sao người Pháp Kylian Mbappe đã trở thành người của Real Madrid.', 'Đây là một bản hợp đồng thế kỷ, hứa hẹn sẽ thay đổi cán cân quyền lực của bóng đá châu Âu trong nhiều năm tới.', 1, 'assets/market_news_3.jpg'),

-- Tin Thể thao
(5, 'Kết quả V-League: HAGL chia điểm kịch tính với Hà Nội FC', 'ket-qua-vleague-hagl-hanoi', 'Trận cầu tâm điểm vòng 15 V-League đã diễn ra vô cùng hấp dẫn với màn rượt đuổi tỷ số ngoạn mục.', 'Chi tiết trận đấu...', 0, 'assets/placeholder.jpg'),
(6, 'Carlos Alcaraz giành chức vô địch Wimbledon sau trận chung kết nghẹt thở', 'alcaraz-vo-dich-wimbledon', 'Tay vợt trẻ người Tây Ban Nha đã xuất sắc đánh bại đối thủ kỳ cựu để lần đầu tiên lên ngôi tại Wimbledon.', 'Diễn biến trận chung kết...', 0, 'assets/placeholder.jpg'),
(7, 'Verstappen về nhất tại Grand Prix Monaco', 'verstappen-ve-nhat-monaco-gp', 'Max Verstappen tiếp tục thể hiện phong độ hủy diệt khi không cho các đối thủ một cơ hội nào.', 'Chi tiết cuộc đua...', 0, 'assets/placeholder.jpg'),
(1, 'Lịch thi đấu Euro 2028 hôm nay', 'lich-thi-dau-euro-2028', 'Cập nhật lịch thi đấu, kênh trực tiếp các trận đấu trong khuôn khổ vòng chung kết Euro 2028.', 'Chi tiết lịch thi đấu...', 0, 'assets/market_news_4.jpg'),
(5, 'Phân tích chiến thuật: Liverpool đã vô hiệu hóa Man City như thế nào?', 'phan-tich-chien-thuat-liverpool-mancity', 'HLV Jurgen Klopp đã một lần nữa cho thấy tài năng của mình với một thế trận phòng ngự phản công bậc thầy.', 'Chi tiết phân tích...', 0, 'assets/market_news_2.jpg'),
(6, 'Federer và Nadal: Nhìn lại cuộc đối đầu vĩ đại nhất lịch sử quần vợt', 'federer-nadal-cuoc-doi-dau-vi-dai', 'Họ không chỉ là đối thủ, họ còn là những người bạn và là nguồn cảm hứng cho hàng triệu người hâm mộ.', 'Những khoảnh khắc đáng nhớ...', 0, 'assets/placeholder.jpg'),

-- Tin Kinh doanh
(8, 'VN-Index vượt mốc 1300 điểm', 'vnindex-vuot-1300-diem', 'Thị trường chứng khoán Việt Nam có một phiên giao dịch bùng nổ, chỉ số VN-Index đã vượt qua ngưỡng cản tâm lý quan trọng.', 'Phân tích thị trường...', 0, 'assets/market_news_1.jpg'),
(9, 'Startup Việt huy động thành công 10 triệu USD vòng series A', 'startup-viet-goi-von-10-trieu-usd', 'Công ty công nghệ giáo dục Edutech vừa công bố hoàn tất vòng gọi vốn series A do các quỹ đầu tư lớn trong khu vực dẫn dắt.', 'Thông tin về startup...', 0, 'assets/placeholder.jpg'),
(8, 'Bitcoin biến động mạnh, nhà đầu tư nên làm gì?', 'bitcoin-bien-dong-manh', 'Thị trường tiền điện tử đang trải qua một giai đoạn đầy biến động, đòi hỏi các nhà đầu tư phải hết sức cẩn trọng.', 'Lời khuyên từ chuyên gia...', 0, 'assets/market_news_1.jpg'),
(2, 'Lãi suất ngân hàng dự báo sẽ tiếp tục tăng nhẹ', 'lai-suat-ngan-hang-tang-nhe', 'Ngân hàng nhà nước có thể sẽ có những điều chỉnh chính sách tiền tệ để kiểm soát lạm phát.', 'Phân tích từ các chuyên gia kinh tế...', 0, 'assets/placeholder.jpg'),

-- Tin Thế giới
(3, 'Hội nghị thượng đỉnh G7 bàn về các vấn đề khí hậu và an ninh toàn cầu', 'hoi-nghi-g7-khi-hau-an-ninh', 'Các nhà lãnh đạo của 7 nền kinh tế phát triển nhất thế giới đã nhóm họp tại Canada.', 'Nội dung chính của hội nghị...', 0, 'assets/placeholder.jpg'),
(3, 'NASA công bố kế hoạch đưa người trở lại Mặt Trăng vào năm 2028', 'nasa-tro-lai-mat-trang-2028', 'Chương trình Artemis hứa hẹn sẽ mở ra một kỷ nguyên mới cho việc khám phá không gian của nhân loại.', 'Chi tiết kế hoạch...', 0, 'assets/placeholder.jpg'),

-- Tin Công nghệ
(13, 'Apple ra mắt iPhone 18 với chip A20 Bionic và màn hình ProMotion 2.0', 'apple-ra-mat-iphone-18', 'Gã khổng lồ công nghệ vừa trình làng thế hệ iPhone mới với nhiều nâng cấp đáng giá về hiệu năng và camera.', 'Chi tiết về sản phẩm...', 0, 'assets/placeholder.jpg'),
(13, 'Cuộc đua phát triển AI tạo sinh: OpenAI, Google và những kẻ thách thức', 'cuoc-dua-ai-tao-sinh', 'Trí tuệ nhân tạo tạo sinh đang là chiến trường khốc liệt nhất của các ông lớn công nghệ.', 'Phân tích cuộc đua công nghệ...', 0, 'assets/placeholder.jpg'),

-- Tin Giải trí
(11, 'Review phim "Dune 2": Siêu phẩm điện ảnh xứng đáng với mọi lời khen', 'review-dune-2', 'Denis Villeneuve một lần nữa đã không làm khán giả thất vọng với một tác phẩm hoành tráng và sâu sắc.', 'Đánh giá chi tiết bộ phim...', 0, 'assets/placeholder.jpg'),
(12, 'Album mới của Sơn Tùng M-TP phá vỡ nhiều kỷ lục nhạc số', 'son-tung-mtp-album-moi-ky-luc', 'Sản phẩm âm nhạc mới nhất của nam ca sĩ đã nhanh chóng chiếm lĩnh các bảng xếp hạng trong và ngoài nước.', 'Thành tích của album...', 0, 'assets/market_news_2.jpg'),
(11, 'Phim "Lật Mặt 7" của Lý Hải đạt doanh thu 500 tỷ đồng', 'lat-mat-7-doanh-thu-500-ty', 'Tác phẩm mới nhất của đạo diễn Lý Hải tiếp tục tạo nên một cơn sốt phòng vé chưa từng có tại Việt Nam.', 'Chi tiết về thành công của bộ phim...', 0, 'assets/placeholder.jpg'),
(12, 'Blackpink công bố tour diễn toàn cầu, có điểm dừng tại Việt Nam', 'blackpink-tour-toan-cau-viet-nam', 'Cộng đồng Blink Việt Nam đang vỡ òa trước thông tin nhóm nhạc nữ hàng đầu thế giới sẽ biểu diễn tại sân vận động Mỹ Đình.', 'Chi tiết về concert...', 0, 'assets/market_news_3.jpg'),

-- Tin Du lịch
(14, 'Top 5 địa điểm phải đến ở Hà Giang mùa lúa chín', 'top-5-dia-diem-ha-giang', 'Hà Giang tháng 9, tháng 10 đẹp như một bức tranh thủy mặc với những thửa ruộng bậc thang vàng óng.', 'Gợi ý lịch trình...', 0, 'assets/placeholder.jpg'),
(14, 'Kinh nghiệm du lịch Phú Quốc tự túc cho người mới đi lần đầu', 'kinh-nghiem-du-lich-phu-quoc', 'Tất tần tật những gì bạn cần biết về di chuyển, ăn ở, vui chơi tại Đảo Ngọc.', 'Hướng dẫn chi tiết...', 0, 'assets/placeholder.jpg'),

-- Tin Video
(10, 'Video: Top 5 bàn thắng đẹp nhất tuần qua', 'video-top-5-ban-thang-dep', 'Cùng chiêm ngưỡng lại những siêu phẩm làm nức lòng người hâm mộ trên khắp các sân cỏ thế giới.', 'Video tổng hợp...', 0, 'assets/placeholder.jpg'),
(10, 'Video: Khám phá hang Sơn Đoòng - kỳ quan thiên nhiên của thế giới', 'video-kham-pha-son-doong', 'Những thước phim ngoạn mục ghi lại vẻ đẹp hùng vĩ và choáng ngợp bên trong hang động lớn nhất thế giới.', 'Video du lịch khám phá...', 0, 'assets/placeholder.jpg');