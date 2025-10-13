# WebTheThao - Website tin tức PHP (chạy local)

Dự án là một website tin tức nhỏ viết bằng PHP + MySQL (chạy tốt trên XAMPP/WAMP hoặc bất kỳ môi trường PHP + MySQL cục bộ). Dữ liệu mẫu và helper đã được cấu hình để hiển thị bài viết động từ bảng `articles`.

Hướng dẫn cài đặt nhanh

1) Khởi động XAMPP và bật Apache + MySQL.
2) Import cơ sở dữ liệu:
   - Mở `phpMyAdmin` và chạy file `setup.sql` ở thư mục gốc dự án, hoặc chạy từ terminal PowerShell:

```powershell
mysql -u root -p < .\setup.sql
```

3) Đặt dự án vào web root (đã ở sẵn: `c:\xampp\htdocs\webthethao_project`).
4) Mở trình duyệt: http://localhost/webthethao_project/index.php

Ghi chú
- Ảnh mẫu nằm trong thư mục `assets/`. Bạn có thể thay thế bằng ảnh của bạn.
- Cấu hình kết nối CSDL nằm ở `config.php` (tài khoản mặc định thường là `root`/trống). Hãy điều chỉnh theo máy của bạn.

Tính năng có sẵn (tóm tắt)
- Trang chủ, trang bài viết, trang chuyên mục.
- Đăng ký/Đăng nhập người dùng, trang tài khoản (đổi mật khẩu).
- Quản lý bài viết/chuyên mục (admin/editor) nếu đã bật module quản trị.

Mẹo khi gặp lỗi thường gặp
- Không vào được DB: kiểm tra MySQL đã chạy và thông số trong `config.php` đúng tên database đã import từ `setup.sql`.
- Ảnh không hiển thị: kiểm tra đường dẫn `assets/` hoặc cập nhật `image_url` trong DB.
