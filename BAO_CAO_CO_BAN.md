# BÁO CÁO THIẾT KẾ VÀ XÂY DỰNG CÁC CHỨC NĂNG

**Đề tài:** Website Tin Thể Thao  
**Công nghệ:** PHP, MySQL  
**Môi trường:** XAMPP (Apache + MySQL)  
**Mức độ:** Cơ bản (dành cho người mới học lập trình web)

---

## Câu 2: Đăng ký và Đăng nhập

### 1. Giới thiệu

Chức năng đăng ký và đăng nhập giúp người dùng tạo tài khoản và truy cập vào các tính năng dành riêng cho thành viên như lưu bài viết, xem lịch sử, đổi mật khẩu.

### 2. Thiết kế cơ sở dữ liệu

**Bảng `users`:**

| Tên cột | Kiểu dữ liệu | Mô tả |
|---------|--------------|-------|
| user_id | INT (PRIMARY KEY, AUTO_INCREMENT) | Mã người dùng |
| username | VARCHAR(50) UNIQUE | Tên đăng nhập |
| email | VARCHAR(100) UNIQUE | Email |
| password_hash | VARCHAR(255) | Mật khẩu đã mã hóa |
| role | ENUM('user','editor','admin') | Vai trò |
| created_at | TIMESTAMP | Ngày đăng ký |

**Câu lệnh SQL:**
```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'editor', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Cách hoạt động

#### 3.1. Đăng ký (user_register.php)

**Luồng xử lý:**
1. Người dùng điền form: username, email, password, confirm_password
2. Kiểm tra username đã tồn tại chưa
3. Kiểm tra email đã được dùng chưa
4. Mã hóa mật khẩu bằng `password_hash()`
5. Lưu thông tin vào bảng `users`
6. Chuyển sang trang đăng nhập

**Code minh họa (đơn giản):**
```php
<?php
// Kết nối database
$conn = mysqli_connect('localhost', 'root', '', 'webthethao');

// Nhận dữ liệu từ form
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

// Kiểm tra password khớp
if ($password !== $confirm) {
    echo "Mật khẩu không khớp!";
    exit;
}

// Kiểm tra username đã tồn tại
$check = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
if (mysqli_num_rows($check) > 0) {
    echo "Tên đăng nhập đã tồn tại";
    exit;
}

// Kiểm tra email đã tồn tại
$check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
if (mysqli_num_rows($check) > 0) {
    echo "Email đã được sử dụng";
    exit;
}

// Mã hóa mật khẩu
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Lưu vào database
$sql = "INSERT INTO users (username, email, password_hash, role) 
        VALUES ('$username', '$email', '$password_hash', 'user')";
mysqli_query($conn, $sql);

echo "Đăng ký thành công!";
header("Location: user_login.php");
?>
```

**Form HTML:**
```html
<form method="POST" action="user_register.php">
    <h2>Đăng ký tài khoản</h2>
    
    <label>Username:</label>
    <input type="text" name="username" required>
    
    <label>Email:</label>
    <input type="email" name="email" required>
    
    <label>Mật khẩu:</label>
    <input type="password" name="password" required>
    
    <label>Xác nhận mật khẩu:</label>
    <input type="password" name="confirm_password" required>
    
    <button type="submit">Đăng ký</button>
</form>
<p>Đã có tài khoản? <a href="user_login.php">Đăng nhập</a></p>
```

#### 3.2. Đăng nhập (user_login.php)

**Luồng xử lý:**
1. Người dùng nhập username và password
2. Tìm user trong database theo username
3. So sánh mật khẩu bằng `password_verify()`
4. Nếu đúng: lưu thông tin vào `$_SESSION`
5. Chuyển về trang chủ

**Code minh họa:**
```php
<?php
session_start();

// Kết nối database
$conn = mysqli_connect('localhost', 'root', '', 'webthethao');

// Nhận dữ liệu
$username = trim($_POST['username']);
$password = $_POST['password'];

// Tìm user trong database
$sql = "SELECT * FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Kiểm tra mật khẩu
if (!$user || !password_verify($password, $user['password_hash'])) {
    echo "Sai tên đăng nhập hoặc mật khẩu";
    exit;
}

// Lưu vào session
$_SESSION['user_id'] = $user['user_id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

// Chuyển về trang chủ
header("Location: index.php");
?>
```

**Form HTML:**
```html
<form method="POST" action="user_login.php">
    <h2>Đăng nhập</h2>
    
    <label>Username:</label>
    <input type="text" name="username" required>
    
    <label>Mật khẩu:</label>
    <input type="password" name="password" required>
    
    <button type="submit">Đăng nhập</button>
</form>
<p>Chưa có tài khoản? <a href="user_register.php">Đăng ký</a></p>
```

#### 3.3. Đăng xuất (user_logout.php)

**Code:**
```php
<?php
session_start();
session_destroy();
header("Location: index.php");
?>
```

#### 3.4. Hiển thị menu theo trạng thái (header.php)

**Code:**
```php
<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<nav>
    <a href="index.php">Trang chủ</a>
    
    <?php if ($isLoggedIn): ?>
        <!-- Đã đăng nhập -->
        <a href="account.php">Tài khoản (<?= $_SESSION['username'] ?>)</a>
        <a href="user_logout.php">Đăng xuất</a>
    <?php else: ?>
        <!-- Chưa đăng nhập -->
        <a href="user_register.php">Đăng ký</a>
        <a href="user_login.php">Đăng nhập</a>
    <?php endif; ?>
</nav>
```

### 4. Demo

**Bước 1: Đăng ký tài khoản mới**
- Truy cập: `http://localhost/webthethao_project/user_register.php`
- Nhập thông tin:
  - Username: `testuser`
  - Email: `test@example.com`
  - Password: `123456`
  - Confirm Password: `123456`
- Kết quả: Tạo tài khoản thành công, chuyển sang trang đăng nhập

**Bước 2: Đăng nhập**
- Truy cập: `http://localhost/webthethao_project/user_login.php`
- Nhập:
  - Username: `testuser`
  - Password: `123456`
- Kết quả: Đăng nhập thành công, menu hiển thị "Tài khoản (testuser)" và "Đăng xuất"

**Bước 3: Kiểm tra trạng thái**
- Quan sát menu đã đổi: không còn "Đăng ký", "Đăng nhập"
- Bấm "Tài khoản" → vào được trang tài khoản cá nhân
- Bấm "Đăng xuất" → menu trở về "Đăng ký" và "Đăng nhập"

---

## Câu 3: Cập nhật Profile User

### 1. Giới thiệu

Trang tài khoản cho phép người dùng:
- Xem thông tin cá nhân (username, email, vai trò, ngày tham gia)
- Đổi mật khẩu
- Xem bài viết đã lưu
- Xem lịch sử bài viết đã xem

### 2. Thiết kế cơ sở dữ liệu

**Bảng `article_saves` (bài viết đã lưu):**

| Tên cột | Kiểu dữ liệu | Mô tả |
|---------|--------------|-------|
| user_id | INT | Mã người dùng |
| article_id | INT | Mã bài viết |
| created_at | TIMESTAMP | Thời gian lưu |

**Bảng `article_views` (lịch sử xem):**

| Tên cột | Kiểu dữ liệu | Mô tả |
|---------|--------------|-------|
| user_id | INT | Mã người dùng |
| article_id | INT | Mã bài viết |
| viewed_at | TIMESTAMP | Thời gian xem |

**Câu lệnh SQL:**
```sql
CREATE TABLE article_saves (
    user_id INT,
    article_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, article_id)
);

CREATE TABLE article_views (
    user_id INT,
    article_id INT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, article_id)
);
```

### 3. Cách hoạt động

#### 3.1. Hiển thị thông tin tài khoản (account.php)

**Code:**
```php
<?php
session_start();

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// Kết nối database
$conn = mysqli_connect('localhost', 'root', '', 'webthethao');

// Lấy thông tin user
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);
?>

<h2>Thông tin tài khoản</h2>
<p><strong>Username:</strong> <?= $user['username'] ?></p>
<p><strong>Email:</strong> <?= $user['email'] ?></p>
<p><strong>Vai trò:</strong> <?= $user['role'] ?></p>
<p><strong>Ngày tham gia:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
```

#### 3.2. Đổi mật khẩu

**Luồng xử lý:**
1. Nhập mật khẩu cũ, mật khẩu mới, xác nhận mật khẩu mới
2. Kiểm tra mật khẩu cũ có đúng không
3. Kiểm tra mật khẩu mới khớp với xác nhận
4. Mã hóa mật khẩu mới
5. Cập nhật vào database

**Code:**
```php
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Lấy mật khẩu hiện tại từ database
    $sql = "SELECT password_hash FROM users WHERE user_id = {$_SESSION['user_id']}";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    // Kiểm tra mật khẩu cũ
    if (!password_verify($old_password, $user['password_hash'])) {
        echo "Mật khẩu cũ không đúng";
        exit;
    }
    
    // Kiểm tra mật khẩu mới khớp
    if ($new_password !== $confirm_password) {
        echo "Mật khẩu mới không khớp";
        exit;
    }
    
    // Mã hóa và cập nhật
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password_hash = '$new_hash' WHERE user_id = {$_SESSION['user_id']}";
    mysqli_query($conn, $sql);
    
    echo "Đổi mật khẩu thành công!";
}
?>

<h3>Đổi mật khẩu</h3>
<form method="POST">
    <label>Mật khẩu cũ:</label>
    <input type="password" name="old_password" required>
    
    <label>Mật khẩu mới:</label>
    <input type="password" name="new_password" required>
    
    <label>Xác nhận mật khẩu mới:</label>
    <input type="password" name="confirm_password" required>
    
    <button type="submit">Đổi mật khẩu</button>
</form>
```

#### 3.3. Hiển thị bài viết đã lưu

**Code:**
```php
<?php
$user_id = $_SESSION['user_id'];

$sql = "SELECT a.title, a.slug, a.image_url, s.created_at as saved_at
        FROM article_saves s
        JOIN articles a ON s.article_id = a.article_id
        WHERE s.user_id = $user_id
        ORDER BY s.created_at DESC
        LIMIT 10";
$result = mysqli_query($conn, $sql);
?>

<h3>Bài viết đã lưu</h3>

<?php if (mysqli_num_rows($result) == 0): ?>
    <p>Chưa có bài viết nào được lưu.</p>
<?php else: ?>
    <?php while ($article = mysqli_fetch_assoc($result)): ?>
        <div class="article-item">
            <img src="<?= $article['image_url'] ?>" width="100">
            <h4>
                <a href="article.php?slug=<?= $article['slug'] ?>">
                    <?= $article['title'] ?>
                </a>
            </h4>
            <p>Lưu lúc: <?= date('d/m/Y H:i', strtotime($article['saved_at'])) ?></p>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
```

#### 3.4. Hiển thị lịch sử xem

**Code:**
```php
<?php
$sql = "SELECT a.title, a.slug, a.image_url, v.viewed_at
        FROM article_views v
        JOIN articles a ON v.article_id = a.article_id
        WHERE v.user_id = $user_id
        ORDER BY v.viewed_at DESC
        LIMIT 10";
$result = mysqli_query($conn, $sql);
?>

<h3>Lịch sử xem</h3>

<?php if (mysqli_num_rows($result) == 0): ?>
    <p>Chưa xem bài viết nào.</p>
<?php else: ?>
    <?php while ($article = mysqli_fetch_assoc($result)): ?>
        <div class="article-item">
            <img src="<?= $article['image_url'] ?>" width="100">
            <h4>
                <a href="article.php?slug=<?= $article['slug'] ?>">
                    <?= $article['title'] ?>
                </a>
            </h4>
            <p>Xem lúc: <?= date('d/m/Y H:i', strtotime($article['viewed_at'])) ?></p>
        </div>
    <?php endwhile; ?>
<?php endif; ?>
```

### 4. Demo

**Bước 1: Vào trang tài khoản**
- Đăng nhập trước (bằng tài khoản đã tạo ở Câu 2)
- Truy cập: `http://localhost/webthethao_project/account.php`
- Thấy thông tin: username, email, vai trò, ngày tham gia

**Bước 2: Đổi mật khẩu**
- Cuộn xuống phần "Đổi mật khẩu"
- Nhập:
  - Mật khẩu cũ: `123456`
  - Mật khẩu mới: `newpass123`
  - Xác nhận mật khẩu mới: `newpass123`
- Bấm "Đổi mật khẩu"
- Kết quả: "Đổi mật khẩu thành công!"

**Bước 3: Kiểm tra mật khẩu mới**
- Đăng xuất
- Đăng nhập lại bằng mật khẩu mới: `newpass123`
- Kết quả: Đăng nhập thành công

**Bước 4: Xem bài đã lưu (nếu có)**
- Trước đó, vào trang chi tiết bài viết và bấm nút "Lưu bài viết"
- Vào trang Tài khoản
- Cuộn xuống phần "Bài viết đã lưu"
- Thấy danh sách các bài đã lưu

---

## Câu 4: Hiển thị danh sách (loại tin)

### 1. Giới thiệu

Chức năng hiển thị:
- Menu danh mục (categories) trên header của mọi trang
- Danh sách bài viết theo từng danh mục
- Danh sách bài viết mới nhất trên trang chủ

### 2. Thiết kế cơ sở dữ liệu

**Bảng `categories` (danh mục):**

| Tên cột | Kiểu dữ liệu | Mô tả |
|---------|--------------|-------|
| category_id | INT (PRIMARY KEY) | Mã danh mục |
| name | VARCHAR(100) | Tên danh mục |
| slug | VARCHAR(120) UNIQUE | Slug (dùng trong URL) |
| parent_id | INT | Mã danh mục cha (nếu có) |

**Bảng `articles` (bài viết):**

| Tên cột | Kiểu dữ liệu | Mô tả |
|---------|--------------|-------|
| article_id | INT (PRIMARY KEY) | Mã bài viết |
| category_id | INT | Mã danh mục |
| title | VARCHAR(255) | Tiêu đề |
| slug | VARCHAR(255) UNIQUE | Slug |
| content | TEXT | Nội dung |
| summary | TEXT | Tóm tắt |
| image_url | VARCHAR(255) | Ảnh đại diện |
| author_id | INT | Mã tác giả |
| created_at | TIMESTAMP | Ngày đăng |

**Câu lệnh SQL:**
```sql
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE NOT NULL,
    parent_id INT DEFAULT NULL
);

CREATE TABLE articles (
    article_id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    summary TEXT,
    image_url VARCHAR(255),
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Cách hoạt động

#### 3.1. Hiển thị menu danh mục (includes/header.php)

**Code:**
```php
<?php
// Kết nối database
$conn = mysqli_connect('localhost', 'root', '', 'webthethao');

// Lấy danh sách danh mục (chỉ lấy danh mục gốc, không có parent)
$sql = "SELECT * FROM categories WHERE parent_id IS NULL ORDER BY name";
$result = mysqli_query($conn, $sql);
?>

<header>
    <h1>Tin Thể Thao</h1>
    <nav>
        <a href="index.php">Trang chủ</a>
        
        <?php while ($cat = mysqli_fetch_assoc($result)): ?>
            <a href="category.php?slug=<?= $cat['slug'] ?>">
                <?= $cat['name'] ?>
            </a>
        <?php endwhile; ?>
        
        <!-- Menu đăng nhập/đăng ký (đã làm ở Câu 2) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="account.php">Tài khoản</a>
            <a href="user_logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="user_register.php">Đăng ký</a>
            <a href="user_login.php">Đăng nhập</a>
        <?php endif; ?>
    </nav>
</header>
```

#### 3.2. Trang danh mục (category.php)

**Luồng xử lý:**
1. Nhận slug từ URL: `category.php?slug=bong-da`
2. Tìm thông tin danh mục theo slug
3. Lấy danh sách bài viết thuộc danh mục đó
4. Hiển thị danh sách

**Code:**
```php
<?php
session_start();
include 'includes/header.php';

// Nhận slug từ URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    echo "Không tìm thấy danh mục";
    exit;
}

// Tìm danh mục
$sql = "SELECT * FROM categories WHERE slug = '$slug'";
$result = mysqli_query($conn, $sql);
$category = mysqli_fetch_assoc($result);

if (!$category) {
    echo "Danh mục không tồn tại";
    exit;
}

// Lấy bài viết thuộc danh mục
$category_id = $category['category_id'];
$sql = "SELECT a.*, c.name as category_name, u.username as author_name
        FROM articles a
        JOIN categories c ON a.category_id = c.category_id
        JOIN users u ON a.author_id = u.user_id
        WHERE a.category_id = $category_id
        ORDER BY a.created_at DESC
        LIMIT 20";
$result = mysqli_query($conn, $sql);
?>

<main>
    <h1><?= $category['name'] ?></h1>
    <p>Tổng số: <?= mysqli_num_rows($result) ?> bài viết</p>
    
    <?php if (mysqli_num_rows($result) == 0): ?>
        <p>Chưa có bài viết nào trong danh mục này.</p>
    <?php else: ?>
        <div class="article-list">
            <?php while ($article = mysqli_fetch_assoc($result)): ?>
                <article>
                    <?php if ($article['image_url']): ?>
                        <img src="<?= $article['image_url'] ?>" alt="" width="200">
                    <?php endif; ?>
                    
                    <h2>
                        <a href="article.php?slug=<?= $article['slug'] ?>">
                            <?= $article['title'] ?>
                        </a>
                    </h2>
                    
                    <p><?= substr($article['summary'], 0, 150) ?>...</p>
                    
                    <p class="meta">
                        Danh mục: <?= $article['category_name'] ?> |
                        Tác giả: <?= $article['author_name'] ?> |
                        Ngày đăng: <?= date('d/m/Y', strtotime($article['created_at'])) ?>
                    </p>
                </article>
                <hr>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>
```

#### 3.3. Trang chủ - Bài viết mới nhất (index.php)

**Code:**
```php
<?php
session_start();
include 'includes/header.php';

// Lấy 15 bài mới nhất
$sql = "SELECT a.*, c.name as category_name, u.username as author_name
        FROM articles a
        JOIN categories c ON a.category_id = c.category_id
        JOIN users u ON a.author_id = u.user_id
        ORDER BY a.created_at DESC
        LIMIT 15";
$result = mysqli_query($conn, $sql);
?>

<main>
    <h1>Tin Thể Thao Mới Nhất</h1>
    
    <div class="article-grid">
        <?php while ($article = mysqli_fetch_assoc($result)): ?>
            <article class="article-card">
                <?php if ($article['image_url']): ?>
                    <img src="<?= $article['image_url'] ?>" alt="">
                <?php endif; ?>
                
                <h2>
                    <a href="article.php?slug=<?= $article['slug'] ?>">
                        <?= $article['title'] ?>
                    </a>
                </h2>
                
                <p><?= substr($article['summary'], 0, 100) ?>...</p>
                
                <p class="meta">
                    <span><?= $article['category_name'] ?></span> -
                    <span><?= $article['author_name'] ?></span> -
                    <span><?= date('d/m/Y', strtotime($article['created_at'])) ?></span>
                </p>
            </article>
        <?php endwhile; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
```

#### 3.4. Phân trang (tùy chọn mở rộng)

**Code:**
```php
<?php
// Số bài mỗi trang
$limit = 10;

// Trang hiện tại
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Đếm tổng số bài
$count_sql = "SELECT COUNT(*) as total FROM articles WHERE category_id = $category_id";
$count_result = mysqli_query($conn, $count_sql);
$total = mysqli_fetch_assoc($count_result)['total'];
$totalPages = ceil($total / $limit);

// Lấy bài theo trang
$sql = "SELECT * FROM articles 
        WHERE category_id = $category_id
        ORDER BY created_at DESC
        LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

// Hiển thị phân trang
if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?slug=<?= $slug ?>&page=<?= $i ?>" 
               class="<?= $i == $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>
<?php endif; ?>
```

### 4. Demo

**Bước 1: Xem menu danh mục**
- Truy cập trang chủ: `http://localhost/webthethao_project/index.php`
- Quan sát thanh menu: có các danh mục như "Bóng đá", "Bóng rổ", "Tennis"...

**Bước 2: Click vào một danh mục**
- Bấm vào "Bóng đá"
- Chuyển đến: `category.php?slug=bong-da`
- Thấy:
  - Tiêu đề: "Bóng đá"
  - Danh sách bài viết thuộc danh mục Bóng đá
  - Mỗi bài có: ảnh, tiêu đề, tóm tắt, tên tác giả, ngày đăng

**Bước 3: Xem chi tiết bài viết**
- Bấm vào tiêu đề một bài viết
- Chuyển đến: `article.php?slug=<slug-bai-viet>`
- Đọc nội dung đầy đủ của bài viết

**Bước 4: Quay lại trang chủ**
- Bấm "Trang chủ" trên menu
- Thấy danh sách bài viết mới nhất (từ tất cả các danh mục)

**Bước 5: Thử danh mục rỗng (nếu có)**
- Tạo danh mục mới chưa có bài viết (trong admin)
- Truy cập danh mục đó
- Thấy thông báo: "Chưa có bài viết nào trong danh mục này."

---

## Kết luận

### Công nghệ sử dụng
- **Backend**: PHP (cơ bản)
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL)
- **Frontend**: HTML, CSS (cơ bản)

### Các chức năng đã hoàn thành

| Câu | Chức năng | File chính | Mô tả |
|-----|-----------|------------|-------|
| 2 | Đăng ký, Đăng nhập | `user_register.php`, `user_login.php`, `user_logout.php` | Tạo tài khoản, đăng nhập, đăng xuất |
| 3 | Cập nhật Profile | `account.php` | Xem thông tin, đổi mật khẩu, xem bài đã lưu/đã xem |
| 4 | Hiển thị danh sách | `category.php`, `index.php`, `includes/header.php` | Menu danh mục, lọc bài theo danh mục |

### Cấu trúc thư mục

```
webthethao_project/
├── index.php              # Trang chủ (bài mới nhất)
├── user_register.php      # Đăng ký
├── user_login.php         # Đăng nhập
├── user_logout.php        # Đăng xuất
├── account.php            # Tài khoản cá nhân
├── category.php           # Danh mục
├── article.php            # Chi tiết bài viết
├── config.php             # Cấu hình database
├── setup.sql              # Script tạo database
├── includes/
│   ├── header.php         # Menu (gọi ở mọi trang)
│   └── footer.php         # Footer
├── classes/               # Các class PHP (nếu dùng OOP)
├── css/
│   └── style.css          # CSS
└── assets/
    └── uploads/           # Thư mục lưu ảnh
```

### Hướng dẫn cài đặt

**Bước 1: Cài đặt XAMPP**
- Download XAMPP từ: https://www.apachefriends.org/
- Cài đặt và khởi động Apache + MySQL

**Bước 2: Tạo database**
- Mở trình duyệt, vào: `http://localhost/phpmyadmin`
- Tạo database mới tên: `webthethao`
- Import file `setup.sql` (hoặc chạy các câu lệnh CREATE TABLE)

**Bước 3: Cấu hình kết nối**
- Mở file `config.php`
- Kiểm tra thông tin kết nối:
```php
<?php
$conn = mysqli_connect('localhost', 'root', '', 'webthethao');

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
?>
```

**Bước 4: Chạy website**
- Copy thư mục `webthethao_project` vào `C:\xampp\htdocs\`
- Mở trình duyệt, truy cập: `http://localhost/webthethao_project/`

**Bước 5: Tạo dữ liệu mẫu**
- Tạo vài danh mục (bóng đá, bóng rổ, tennis...)
- Tạo vài bài viết cho mỗi danh mục
- Đăng ký tài khoản để test các chức năng

### Lưu ý khi sử dụng

- **Mật khẩu**: Luôn được mã hóa bằng `password_hash()`, không lưu dạng plain text
- **Session**: Phải gọi `session_start()` ở đầu mỗi trang cần dùng session
- **SQL**: Trong thực tế nên dùng prepared statements để bảo mật hơn, nhưng đây là phiên bản cơ bản cho người mới học

### Các tính năng có thể mở rộng

- Tìm kiếm bài viết
- Hệ thống bình luận
- Upload avatar cho user
- Quản trị viên (admin panel)
- Thống kê lượt xem
- Chia sẻ lên mạng xã hội

---

**Ngày hoàn thành:** Tháng 10/2025  
**Phiên bản:** 1.0 (Cơ bản)
