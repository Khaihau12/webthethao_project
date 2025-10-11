# Chọn image PHP có sẵn Apache
FROM php:8.2-apache

# Copy toàn bộ code từ thư mục hiện tại vào thư mục web của Apache
COPY . /var/www/html/

# Cài thêm extension PHP cần thiết (vd: MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Mở port 80 để web có thể truy cập
EXPOSE 80
