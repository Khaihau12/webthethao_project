# WebTheThao - Website tin tức PHP cơ bản

Dự án là một website tin tức đơn giản viết bằng PHP + MySQL (chạy trên XAMPP). Phù hợp cho người mới học lập trình web cơ bản.

## Hướng dẫn cài đặt

1) **Khởi động XAMPP**: Bật Apache + MySQL
2) **Import database**: 
   - Mở `phpMyAdmin` (http://localhost/phpmyadmin)
   - Chạy file `setup.sql` để tạo database `webthethao`
3) **Mở website**: http://localhost/webthethao_project/

## Cấu hình

- File cấu hình: `config.php`
- Database: `webthethao`
- User: `root` (không mật khẩu)

## Tính năng

- ✅ Trang chủ (danh sách bài viết mới nhất)
- ✅ Chi tiết bài viết
- ✅ Danh mục bài viết
- ✅ Đăng ký tài khoản
- ✅ Đăng nhập/Đăng xuất
- ✅ Trang tài khoản (đổi mật khẩu, xem bài đã lưu)
- ✅ Quản trị (admin/editor)

## Lưu ý

**Đây là phiên bản CƠ BẢN dành cho học tập:**
- Code đơn giản, dễ hiểu
- Không có các tính năng bảo mật phức tạp
- Phù hợp với môn Lập trình Web cơ bản

## Khắc phục lỗi

- **Không kết nối được DB**: Kiểm tra MySQL đã chạy, file `config.php` đúng
- **Ảnh không hiển thị**: Kiểm tra thư mục `assets/uploads/`
- **Lỗi 404**: Đảm bảo thư mục nằm trong `C:\xampp\htdocs\`
