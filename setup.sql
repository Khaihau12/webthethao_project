CREATE DATABASE IF NOT EXISTS `webthethao`;  
USE `webthethao`;

-- Bảng lưu trữ tin tức chính
CREATE TABLE IF NOT EXISTS `articles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) UNIQUE,           -- Đường dẫn thân thiện
    `summary` TEXT,
    `content` TEXT,                       -- Nội dung chi tiết
    `image_url` VARCHAR(255),
    `is_featured` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng lưu trữ các ảnh được chèn bên trong bài viết (tạo trước khi chèn dữ liệu ảnh)
CREATE TABLE IF NOT EXISTS `article_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `article_id` INT NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `caption` VARCHAR(255) DEFAULT NULL,
    `alt_text` VARCHAR(255) DEFAULT NULL,
    `position_key` VARCHAR(50) DEFAULT NULL,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (`article_id`),
    CONSTRAINT `fk_article_images_article` FOREIGN KEY (`article_id`) REFERENCES `articles`(`id`) ON DELETE CASCADE
);

-- Dữ liệu mẫu
INSERT INTO `articles` (`category`, `title`, `slug`, `summary`, `content`, `is_featured`, `image_url`) VALUES
('Thời sự', 'Nữ tài xế ô tô 7 chỗ tông tử vong chủ tịch xã sẽ phải đối diện mức án nào?', 'nu-tai-xe-tong-tu-vong-chu-tich-xa', 'Nội dung tóm tắt về vụ việc nữ tài xế gây tai nạn nghiêm trọng.', 'Sáng 4/10, UBND xã Đắk Lắk đã có báo cáo nhanh về vụ việc...', FALSE, 'assets/map_image.jpg'),
('Thế giới', 'Siêu bão tạo sóng thần 12 mét, khiến hơn 200.000 người thiệt mạng', 'sieu-bao-song-than-200-nghin-nguoi-thiet-mang', 'Thiệt hại kinh hoàng từ cơn siêu bão lịch sử đổ bộ vào khu vực Đông Nam Á.', 'Nội dung chi tiết của bài báo 2...', FALSE, 'assets/market_news_2.jpg'),
('Kinh Doanh', 'Giá vàng hôm nay 6-10: Vừa mở cửa, đồng loạt tăng mạnh', 'gia-vang-hom-nay-6-10-tang-manh', 'Giá vàng thế giới và trong nước có những diễn biến tích cực ngay đầu phiên giao dịch.', 'Nội dung chi tiết của bài báo 3...', FALSE, 'assets/market_news_1.jpg'),
('Giải trí', 'NSND Trần Hiếu: Người nghệ sĩ tài hoa và những lần dặn cưới đời', 'nsnd-tran-hieu-dam-cuoi', 'Những câu chuyện thú vị về cuộc sống và sự nghiệp của nghệ sĩ nhân dân Trần Hiếu.', 'Nội dung chi tiết của bài báo 4...', FALSE, 'assets/market_news_3.jpg'),
('Thị Trường', 'Bão Matmo đã suy yếu thành áp thấp nhiệt đới', 'bao-matmo-suy-yeu-ap-thap-nhiet-doi', 'Theo Trung tâm Dự báo Khí tượng Thủy văn Quốc gia...', 'Theo Trung tâm Dự báo Khí tượng Thủy văn Quốc gia, sáng nay (6/10), sau khi đi sâu vào đất liền tỉnh Quảng Tây (Trung Quốc), bão số 11 đã suy yếu thành áp thấp nhiệt đới.
Theo Trung tâm Dự báo Khí tượng Thủy văn Quốc gia, sáng nay (6/10), sau khi đi sâu vào đất liền tỉnh Quảng Tây (Trung Quốc), bão số 11 đã suy yếu thành áp thấp nhiệt đới.

Hồi 7 giờ ngày 6/10, vị trí tâm áp thấp nhiệt đới ở vào khoảng 22,0 độ Vĩ Bắc; 107,1 độ Kinh Đông, trên khu vực phía Nam tỉnh Quảng Tây (Trung Quốc). Sức gió mạnh nhất vùng gần tâm áp thấp nhiệt đới mạnh cấp 7 (50-61km/giờ), giật cấp 9. Di chuyển theo hướng Tây, tốc độ 20-25km/h.
Hồi 19 giờ ngày 6/10, vị trí tâm bão ở khoảng 22,5 độ Vĩ Bắc; 104,8 độ Kinh Đông, trên khu vực vùng núi Bắc Bộ.Bão di chuyển theo hướng Tây Tây Bắc với tốc độ 20–25km/h và suy yếu dần thành vùng áp thấp (cấp <6).Phạm vi ảnh hưởng cảnh báo cấp 3: gồm khu vực Bắc Vịnh Bắc Bộ, đất liền các tỉnh Quảng Ninh và Lạng Sơn, nằm phía Bắc vĩ tuyến 20,0°N và phía Tây kinh tuyến 109,0°E.

Trong sáng nay (6/10), khu vực Bắc vịnh Bắc Bộ (bao gồm đặc khu Bạch Long Vỹ) gió mạnh cấp 6, giật cấp 8, sóng biển cao 2,0-3,0m, biển động (nguy hiểm đối với tàu thuyền).

Trên đất liền, trong sáng nay (6/10), khu vực Quảng Ninh và Lạng Sơn có gió mạnh cấp 5, có nơi cấp 6, giật cấp 7-8.

Từ sáng 6/10 đến hết đêm 7/10, ở khu vực vùng núi, trung du Bắc Bộ có mưa to và dông, lượng mưa phổ biến từ 100-200mm, cục bộ có nơi mưa rất to trên 300mm. Cảnh báo nguy cơ mưa có cường suất lớn (>150mm/3h); khu vực Đồng Bằng Bắc Bộ, Thanh Hóa có mưa vừa, mưa to với lượng mưa phổ biến 50-150mm, cục bộ có nơi mưa rất to trên 200mm.

Khu vực Hà Nội cần đề phòng dông, lốc và gió giật mạnh. Dự báo từ sáng 6/10 đến hết ngày 7/10 có mưa vừa, mưa to và dông, với lượng mưa phổ biến 50-100mm, cục bộ có nơi trên 150mm. Trong mưa dông có khả năng xảy ra lốc, sét và gió giật mạnh', TRUE, 'assets/market_news_4.jpg');

-- Chèn token ảnh bản đồ ngay sau đoạn nêu vị trí lúc 7 giờ; token [IMAGE:storm-map] sẽ được inject thành <img> khi hiển thị
UPDATE articles SET content = REPLACE(content, 'Hồi 7 giờ ngày 6/10, vị trí tâm áp thấp nhiệt đới ở vào khoảng 22,0 độ Vĩ Bắc; 107,1 độ Kinh Đông, trên khu vực phía Nam tỉnh Quảng Tây (Trung Quốc). Sức gió mạnh nhất vùng gần tâm áp thấp nhiệt đới mạnh cấp 7 (50-61km/giờ), giật cấp 9. Di chuyển theo hướng Tây, tốc độ 20-25km/h.', CONCAT('Hồi 7 giờ ngày 6/10, vị trí tâm áp thấp nhiệt đới ở vào khoảng 22,0 độ Vĩ Bắc; 107,1 độ Kinh Đông, trên khu vực phía Nam tỉnh Quảng Tây (Trung Quốc). Sức gió mạnh nhất vùng gần tâm áp thấp nhiệt đới mạnh cấp 7 (50-61km/giờ), giật cấp 9. Di chuyển theo hướng Tây, tốc độ 20-25km/h.', '\n\n[IMAGE:storm-map]')) WHERE slug = 'bao-matmo-suy-yeu-ap-thap-nhiet-doi';

-- Thêm bản ghi ảnh cho token storm-map (article_id = 5 theo thứ tự INSERTs ở trên)
INSERT INTO article_images (article_id, file_path, caption, alt_text, position_key, display_order) VALUES
(5, 'assets/map_image.jpg', 'Bản đồ đường đi và vùng ảnh hưởng của bão số 11', 'Bản đồ đường đi bão số 11', 'storm-map', 0);

-- Thêm vài bản ghi mẫu cho mục Video và Tin nóng để hiển thị ở sidebar và các khối khác
INSERT INTO `articles` (`category`, `title`, `slug`, `summary`, `content`, `is_featured`, `image_url`) VALUES
('Video', 'Tổng hợp bàn thắng vòng 7 V-League', 'video-banthang-vleague-vong-7', 'Những pha lập công đẹp của vòng 7.', 'Video tổng hợp...', FALSE, 'assets/map_image.jpg'),
('Tin nóng', 'Tai nạn giao thông nghiêm trọng trên cao tốc', 'tai-nan-cao-toc', 'Thông tin ban đầu về vụ tai nạn...', 'Chi tiết vụ việc...', FALSE, 'assets/placeholder.jpg');

-- Bảng lưu trữ các ảnh được chèn bên trong bài viết
-- ...existing file...