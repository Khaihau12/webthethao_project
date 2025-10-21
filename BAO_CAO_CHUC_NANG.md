# BÁO CÁO THIẾT KẾ VÀ XÂY DỰNG CÁC CHỨC NĂNG
**Đề tài:** Website Tin Thể Thao  
**Công nghệ:** PHP, MySQL (XAMPP)

---

## Câu 2: Thiết kế và xây dựng chức năng Đăng ký, Đăng nhập

### 1. TỔNG QUAN

#### 1.1. Mục đích
- Cho phép người dùng tạo tài khoản mới
- Đăng nhập vào hệ thống
- Phân biệt người dùng đã đăng nhập và khách

#### 1.2. Vị trí trong web
- **Đăng ký**: `user_register.php`
- **Đăng nhập**: `user_login.php`  
- **Đăng xuất**: `user_logout.php`
- **Menu**: Hiển thị "Đăng ký", "Đăng nhập" khi chưa đăng nhập

### 2. THIẾT KẾ

#### 2.1. Cơ sở dữ liệu

**Bảng `users`:**
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

**Giải thích:**
- `user_id`: Mã số người dùng (tự động tăng)
- `username`: Tên đăng nhập (không trùng)
- `email`: Email (không trùng)
- `password_hash`: Mật khẩu đã mã hóa
- `role`: Vai trò (user/editor/admin)

#### 2.2. Cấu trúc file

```
user_register.php    → Trang đăng ký
user_login.php       → Trang đăng nhập
user_logout.php      → Xử lý đăng xuất
classes/Auth.php     → Xử lý xác thực
classes/UserRepository.php → Truy vấn user
```

#### 2.3. Luồng hoạt động

**Đăng ký:**
```
1. User điền form (username, email, password)
2. Kiểm tra username/email đã tồn tại chưa
3. Mã hóa mật khẩu
4. Lưu vào database
5. Chuyển sang trang đăng nhập
```

**Đăng nhập:**
```
1. User nhập username + password
2. Tìm user trong database
3. So sánh mật khẩu
4. Lưu thông tin vào SESSION
5. Chuyển về trang chủ
```

### 3. XÂY DỰNG

#### 3.1. Đăng ký (user_register.php)

**Code chính:**

```php
// Nhận dữ liệu từ form
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
    $error = "Yêu cầu không hợp lệ";
}

// Bước 2: Lấy và làm sạch dữ liệu
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Bước 3: Validate dữ liệu
- Kiểm tra không để trống
- Kiểm tra độ dài username (3-50 ký tự)
- Kiểm tra định dạng email hợp lệ
- Kiểm tra độ dài password (tối thiểu 6 ký tự)
- Kiểm tra password khớp với confirm_password

// Bước 4: Kiểm tra trùng lặp
$userRepo = new UserRepository($db);
if ($userRepo->findByUsername($username)) {
    $error = "Tên đăng nhập đã tồn tại";
}
if ($userRepo->findByEmail($email)) {
    $error = "Email đã được sử dụng";
}

// Bước 5: Mã hóa mật khẩu và lưu
$password_hash = password_hash($password, PASSWORD_DEFAULT);
$user_id = $userRepo->create($username, $email, $password_hash);

// Bước 6: Thông báo thành công và chuyển hướng
$success = "Đăng ký thành công! Vui lòng đăng nhập.";
header("Location: user_login.php");
```

**Bảo mật:**
- CSRF Token: Chống Cross-Site Request Forgery
- password_hash(): Mã hóa mật khẩu bằng bcrypt (mặc định)
- Prepared Statements: Chống SQL Injection
- Validate input: Ngăn dữ liệu không hợp lệ

#### 3.2. Chức năng Đăng nhập

**File: `user_login.php`**

**Các bước xử lý:**

1. **Hiển thị form đăng nhập (GET request):**
   - Tạo CSRF token
   - Hiển thị form với các trường: login (username hoặc email), password

2. **Xử lý đăng nhập (POST request):**

```php
// Bước 1: Kiểm tra CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $error = "Yêu cầu không hợp lệ";
}

// Bước 2: Lấy thông tin đăng nhập
$login = trim($_POST['login']); // username hoặc email
$password = $_POST['password'];

// Bước 3: Tìm user trong database
$userRepo = new UserRepository($db);
$user = $userRepo->findByUsername($login);
if (!$user) {
    $user = $userRepo->findByEmail($login);
}

// Bước 4: Xác thực mật khẩu
if (!$user || !password_verify($password, $user->password_hash)) {
    $error = "Thông tin đăng nhập không chính xác";
    exit;
}

// Bước 5: Tạo phiên đăng nhập
$_SESSION['user_id'] = $user->id;
$_SESSION['username'] = $user->username;
$_SESSION['role'] = $user->role;

// Bước 6: Bảo mật session
session_regenerate_id(true); // Tạo session ID mới, chống session fixation

// Bước 7: Chuyển hướng
header("Location: index.php");
```

**Class Auth.php - Quản lý xác thực:**

```php
class Auth {
    // Lấy thông tin user hiện tại từ session
    public static function currentUser() {
        if (isset($_SESSION['user_id'])) {
            $db = Database::getInstance();
            $userRepo = new UserRepository($db);
            return $userRepo->findById($_SESSION['user_id']);
        }
        return null;
    }
    
    // Kiểm tra đã đăng nhập chưa
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    
    // Yêu cầu đăng nhập (dùng cho các trang bảo vệ)
    public static function requireLogin() {
        if (!self::check()) {
            header("Location: user_login.php");
            exit;
        }
    }
    
    // Kiểm tra quyền admin
    public static function requireAdmin() {
        self::requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            header("Location: index.php");
            exit;
        }
    }
}
```

#### 3.3. Chức năng Đăng xuất

**File: `user_logout.php`**

```php
session_start();

// Xóa tất cả dữ liệu session
$_SESSION = array();

// Hủy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Hủy session
session_destroy();

// Chuyển về trang chủ
header("Location: index.php");
exit;
```

#### 3.4. Hiển thị menu theo trạng thái

**File: `includes/header.php`**

```php
<?php
// Kiểm tra trạng thái đăng nhập
$currentUser = Auth::currentUser();
?>

<nav>
    <!-- Menu danh mục -->
    <?php foreach ($categories as $cat): ?>
        <a href="category.php?slug=<?= $cat->slug ?>"><?= $cat->name ?></a>
    <?php endforeach; ?>
    
    <!-- Menu user -->
    <?php if ($currentUser): ?>
        <!-- Đã đăng nhập -->
        <a href="account.php">Tài khoản (<?= htmlspecialchars($currentUser->username) ?>)</a>
        <a href="user_logout.php">Đăng xuất</a>
    <?php else: ?>
        <!-- Chưa đăng nhập -->
        <a href="user_register.php">Đăng ký</a>
        <a href="user_login.php">Đăng nhập</a>
    <?php endif; ?>
</nav>
```

### 4. BẢO MẬT

#### 4.1. Các biện pháp bảo mật đã áp dụng

1. **CSRF Protection (Cross-Site Request Forgery)**
   - Token ngẫu nhiên được tạo và lưu trong session
   - Mỗi form POST kiểm tra token trước khi xử lý
   ```php
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   ```

2. **Password Hashing**
   - Sử dụng `password_hash()` với thuật toán bcrypt
   - Không lưu mật khẩu dạng plain text
   - Xác thực bằng `password_verify()`

3. **SQL Injection Prevention**
   - Sử dụng Prepared Statements với bind parameters
   ```php
   $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
   $stmt->bind_param("s", $username);
   ```

4. **Session Security**
   - `session_regenerate_id(true)` sau khi đăng nhập
   - Kiểm tra session timeout
   - HttpOnly cookies (nếu cấu hình)

5. **Input Validation**
   - Validate độ dài, định dạng email, ký tự đặc biệt
   - Trim whitespace, chống XSS với `htmlspecialchars()`

6. **Error Handling**
   - Thông báo lỗi chung chung (không tiết lộ chi tiết hệ thống)
   - Log lỗi riêng biệt cho admin

### 5. DEMO CHỨC NĂNG

#### 5.1. Các bước demo Đăng ký

1. **Truy cập trang đăng ký:**
   - URL: `http://localhost/webthethao_project/user_register.php`
   
2. **Điền thông tin:**
   - Username: `testuser`
   - Email: `testuser@example.com`
   - Password: `123456`
   - Confirm Password: `123456`

3. **Submit form:**
   - Hệ thống kiểm tra:
     - ✓ CSRF token hợp lệ
     - ✓ Username chưa tồn tại
     - ✓ Email chưa được sử dụng
     - ✓ Password đủ mạnh
   
4. **Kết quả:**
   - Thông báo: "Đăng ký thành công!"
   - Chuyển hướng đến trang đăng nhập
   - Database có thêm 1 record mới trong bảng `users`

#### 5.2. Các bước demo Đăng nhập

1. **Truy cập trang đăng nhập:**
   - URL: `http://localhost/webthethao_project/user_login.php`

2. **Điền thông tin:**
   - Login: `testuser` (hoặc `testuser@example.com`)
   - Password: `123456`

3. **Submit form:**
   - Hệ thống kiểm tra:
     - ✓ Tìm thấy user
     - ✓ Mật khẩu khớp (password_verify)
   
4. **Kết quả:**
   - Session được tạo với `user_id`, `username`, `role`
   - Menu đổi thành "Tài khoản", "Đăng xuất"
   - Chuyển hướng về trang chủ

#### 5.3. Các bước demo Đăng xuất

1. **Bấm "Đăng xuất" trên menu**
2. **Hệ thống:**
   - Xóa toàn bộ session
   - Hủy session ID
3. **Kết quả:**
   - Menu trở lại "Đăng ký", "Đăng nhập"
   - Không thể truy cập các trang yêu cầu đăng nhập

#### 5.4. Demo kiểm tra bảo mật

**Test 1: Đăng ký với username trùng**
- Input: Username đã tồn tại
- Kết quả: "Tên đăng nhập đã tồn tại"

**Test 2: Đăng nhập sai mật khẩu**
- Input: Password sai
- Kết quả: "Thông tin đăng nhập không chính xác"

**Test 3: Truy cập trang bảo vệ khi chưa đăng nhập**
- URL: `account.php`
- Kết quả: Tự động chuyển đến `user_login.php`

---

## Câu 3: Thiết kế và xây dựng chức năng Cập nhật Profile User

### 1. TỔNG QUAN CHỨC NĂNG

#### 1.1. Mục đích
- Cho phép người dùng quản lý thông tin cá nhân
- Thay đổi mật khẩu
- Xem lịch sử tương tác (bài viết đã lưu, đã xem)

#### 1.2. Vị trí trong web
- **URL**: `http://localhost/webthethao_project/account.php`
- **Menu**: Link "Tài khoản" xuất hiện khi đã đăng nhập
- **Yêu cầu**: Phải đăng nhập mới truy cập được

### 2. THIẾT KẾ HỆ THỐNG

#### 2.1. Cơ sở dữ liệu

**Bảng liên quan:**

1. **Bảng `users`** (đã mô tả ở Câu 2)
   - Lưu thông tin cơ bản: username, email, password_hash, role

2. **Bảng `article_saves`** (bài viết đã lưu):
```sql
CREATE TABLE article_saves (
    user_id INT,
    article_id INT,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, article_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (article_id) REFERENCES articles(article_id)
);
```

3. **Bảng `article_views`** (lịch sử xem):
```sql
CREATE TABLE article_views (
    user_id INT,
    article_id INT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, article_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (article_id) REFERENCES articles(article_id)
);
```

#### 2.2. Kiến trúc chức năng

**Các thành phần:**

1. **Presentation Layer:**
   - `account.php`: Trang tài khoản (hiển thị thông tin + form đổi mật khẩu)
   - Tab/section: Thông tin, Đổi mật khẩu, Bài đã lưu, Bài đã xem

2. **Business Logic Layer:**
   - `classes/UserRepository.php`: Cập nhật thông tin user
   - `classes/InteractionRepository.php`: Lấy danh sách saved/viewed

3. **Data Access Layer:**
   - Prepared Statements cho UPDATE, SELECT

#### 2.3. Sơ đồ luồng cập nhật mật khẩu

```
User -> Form đổi mật khẩu (account.php)
  -> Kiểm tra CSRF Token
  -> Kiểm tra đã đăng nhập (Auth::requireLogin)
  -> Lấy user_id từ session
  -> Nhập: old_password, new_password, confirm_password
  -> UserRepository::findById(user_id) (lấy password_hash hiện tại)
  -> password_verify(old_password, current_hash)
  -> Validate new_password (độ dài, khớp confirm)
  -> password_hash(new_password)
  -> UserRepository::updatePassword(user_id, new_hash)
  -> Thông báo thành công
```

### 3. XÂY DỰNG CHỨC NĂNG

#### 3.1. Trang tài khoản (account.php)

**Cấu trúc trang:**

1. **Phần thông tin cơ bản:**
```php
<?php
require_once 'config.php';
require_once 'classes/Auth.php';

// Yêu cầu đăng nhập
Auth::requireLogin();

$currentUser = Auth::currentUser();
?>

<div class="account-info">
    <h2>Thông tin tài khoản</h2>
    <p><strong>Username:</strong> <?= htmlspecialchars($currentUser->username) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($currentUser->email) ?></p>
    <p><strong>Vai trò:</strong> <?= htmlspecialchars($currentUser->role) ?></p>
    <p><strong>Ngày tham gia:</strong> <?= date('d/m/Y', strtotime($currentUser->created_at)) ?></p>
</div>
```

2. **Form đổi mật khẩu:**
```php
<?php
// Xử lý POST đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    
    // Bước 1: Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Yêu cầu không hợp lệ";
    } else {
        // Bước 2: Lấy dữ liệu
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Bước 3: Lấy thông tin user hiện tại
        $db = Database::getInstance();
        $userRepo = new UserRepository($db);
        $user = $userRepo->findById($_SESSION['user_id']);
        
        // Bước 4: Xác thực mật khẩu cũ
        if (!password_verify($old_password, $user->password_hash)) {
            $error = "Mật khẩu cũ không chính xác";
        }
        // Bước 5: Validate mật khẩu mới
        elseif (strlen($new_password) < 6) {
            $error = "Mật khẩu mới phải có ít nhất 6 ký tự";
        }
        elseif ($new_password !== $confirm_password) {
            $error = "Mật khẩu mới không khớp";
        }
        // Bước 6: Cập nhật mật khẩu
        else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $userRepo->updatePassword($_SESSION['user_id'], $new_hash);
            $success = "Đổi mật khẩu thành công!";
        }
    }
}

// Tạo CSRF token mới cho form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!-- Form HTML -->
<form method="POST" class="change-password-form">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="action" value="change_password">
    
    <h3>Đổi mật khẩu</h3>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div>
        <label>Mật khẩu cũ:</label>
        <input type="password" name="old_password" required>
    </div>
    
    <div>
        <label>Mật khẩu mới:</label>
        <input type="password" name="new_password" required>
    </div>
    
    <div>
        <label>Xác nhận mật khẩu mới:</label>
        <input type="password" name="confirm_password" required>
    </div>
    
    <button type="submit">Đổi mật khẩu</button>
</form>
```

3. **Danh sách bài viết đã lưu:**
```php
<?php
$db = Database::getInstance();
$interactionRepo = new InteractionRepository($db);
$savedArticles = $interactionRepo->listSaved($_SESSION['user_id']);
?>

<div class="saved-articles">
    <h3>Bài viết đã lưu (<?= count($savedArticles) ?>)</h3>
    
    <?php if (empty($savedArticles)): ?>
        <p>Chưa có bài viết nào được lưu.</p>
    <?php else: ?>
        <div class="article-list">
            <?php foreach ($savedArticles as $article): ?>
                <div class="article-card">
                    <img src="<?= htmlspecialchars($article->thumbnail) ?>" alt="">
                    <h4><a href="article.php?slug=<?= $article->slug ?>"><?= htmlspecialchars($article->title) ?></a></h4>
                    <p class="meta">Lưu lúc: <?= date('d/m/Y H:i', strtotime($article->saved_at)) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
```

4. **Danh sách bài viết đã xem:**
```php
<?php
$viewedArticles = $interactionRepo->listViewed($_SESSION['user_id'], 10);
?>

<div class="viewed-articles">
    <h3>Lịch sử xem (<?= count($viewedArticles) ?>)</h3>
    
    <?php if (empty($viewedArticles)): ?>
        <p>Chưa xem bài viết nào.</p>
    <?php else: ?>
        <div class="article-list">
            <?php foreach ($viewedArticles as $article): ?>
                <div class="article-card">
                    <img src="<?= htmlspecialchars($article->thumbnail) ?>" alt="">
                    <h4><a href="article.php?slug=<?= $article->slug ?>"><?= htmlspecialchars($article->title) ?></a></h4>
                    <p class="meta">Xem lúc: <?= date('d/m/Y H:i', strtotime($article->viewed_at)) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
```

#### 3.2. Class UserRepository - Phương thức cập nhật

**File: `classes/UserRepository.php`**

```php
class UserRepository {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Cập nhật mật khẩu user
     */
    public function updatePassword($user_id, $new_password_hash) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_password_hash, $user_id);
        return $stmt->execute();
    }
    
    /**
     * Cập nhật email (tùy chọn mở rộng)
     */
    public function updateEmail($user_id, $new_email) {
        // Kiểm tra email trùng
        if ($this->findByEmail($new_email)) {
            return false; // Email đã tồn tại
        }
        
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        $stmt->bind_param("si", $new_email, $user_id);
        return $stmt->execute();
    }
}
```

#### 3.3. Class InteractionRepository - Lấy lịch sử

**File: `classes/InteractionRepository.php`**

```php
class InteractionRepository {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Lấy danh sách bài viết đã lưu
     */
    public function listSaved($user_id, $limit = 20) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT 
                a.article_id AS id,
                a.title,
                a.slug,
                a.thumbnail,
                a.excerpt,
                s.saved_at
            FROM article_saves s
            JOIN articles a ON s.article_id = a.article_id
            WHERE s.user_id = ?
            ORDER BY s.saved_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $articles = [];
        while ($row = $result->fetch_object()) {
            $articles[] = $row;
        }
        return $articles;
    }
    
    /**
     * Lấy lịch sử bài viết đã xem
     */
    public function listViewed($user_id, $limit = 20) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT 
                a.article_id AS id,
                a.title,
                a.slug,
                a.thumbnail,
                a.excerpt,
                v.viewed_at
            FROM article_views v
            JOIN articles a ON v.article_id = a.article_id
            WHERE v.user_id = ?
            ORDER BY v.viewed_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("ii", $user_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $articles = [];
        while ($row = $result->fetch_object()) {
            $articles[] = $row;
        }
        return $articles;
    }
    
    /**
     * Đếm số bài đã lưu
     */
    public function countSaved($user_id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM article_saves WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_object()->total;
    }
}
```

### 4. BẢO MẬT

#### 4.1. Các biện pháp bảo mật

1. **Authentication Required:**
   - Mỗi request kiểm tra `Auth::requireLogin()`
   - Không cho phép truy cập khi chưa đăng nhập

2. **Authorization:**
   - Chỉ được cập nhật thông tin của chính mình
   - Sử dụng `user_id` từ session (không từ input)

3. **CSRF Protection:**
   - Token cho mọi form POST
   - Kiểm tra token trước khi xử lý

4. **Password Verification:**
   - Yêu cầu nhập mật khẩu cũ trước khi đổi
   - `password_verify()` để xác thực

5. **Input Validation:**
   - Kiểm tra độ dài, định dạng
   - Escape output với `htmlspecialchars()`

### 5. DEMO CHỨC NĂNG

#### 5.1. Demo xem thông tin tài khoản

1. **Đăng nhập vào hệ thống**
   - URL: `http://localhost/webthethao_project/user_login.php`
   - Nhập username và password

2. **Vào trang Tài khoản**
   - Click "Tài khoản" trên menu
   - Hoặc truy cập: `http://localhost/webthethao_project/account.php`

3. **Quan sát thông tin:**
   - Username, Email, Vai trò, Ngày tham gia
   - Số bài viết đã lưu, đã xem

#### 5.2. Demo đổi mật khẩu

1. **Tại trang Tài khoản, tìm form "Đổi mật khẩu"**

2. **Điền thông tin:**
   - Mật khẩu cũ: `123456`
   - Mật khẩu mới: `newpass123`
   - Xác nhận mật khẩu mới: `newpass123`

3. **Submit form**

4. **Kết quả:**
   - Thành công: "Đổi mật khẩu thành công!"
   - Database cập nhật `password_hash` mới
   - Có thể đăng nhập bằng mật khẩu mới

#### 5.3. Demo test bảo mật

**Test 1: Nhập sai mật khẩu cũ**
- Input: Mật khẩu cũ sai
- Kết quả: "Mật khẩu cũ không chính xác"

**Test 2: Mật khẩu mới không khớp**
- Input: new_password ≠ confirm_password
- Kết quả: "Mật khẩu mới không khớp"

**Test 3: Truy cập khi chưa đăng nhập**
- Truy cập trực tiếp `account.php` khi chưa đăng nhập
- Kết quả: Tự động chuyển đến `user_login.php`

#### 5.4. Demo xem bài đã lưu/đã xem

1. **Lưu một bài viết:**
   - Vào trang chi tiết bài viết (article.php)
   - Click nút "Lưu bài viết"
   - Hệ thống ghi vào bảng `article_saves`

2. **Xem lịch sử:**
   - Vào trang Tài khoản
   - Cuộn xuống phần "Bài viết đã lưu"
   - Thấy danh sách bài vừa lưu

3. **Lịch sử xem tự động:**
   - Mỗi lần xem bài viết, hệ thống tự động ghi vào `article_views`
   - Vào "Lịch sử xem" để thấy các bài đã đọc

---

## Câu 4: Thiết kế và xây dựng chức năng Hiển thị danh sách (loại tin)

### 1. TỔNG QUAN CHỨC NĂNG

#### 1.1. Mục đích
- Hiển thị menu danh mục (categories) trên header
- Lọc và hiển thị danh sách bài viết theo từng danh mục
- Hỗ trợ điều hướng và phân loại nội dung
- Phân trang và sắp xếp bài viết

#### 1.2. Vị trí trong web
- **Menu danh mục**: Thanh điều hướng (header) trên mọi trang
- **Trang danh mục**: `http://localhost/webthethao_project/category.php?slug=<slug-danh-muc>`
- **Ví dụ**: `category.php?slug=bong-da` (danh mục Bóng đá)

### 2. THIẾT KẾ HỆ THỐNG

#### 2.1. Cơ sở dữ liệu

**Bảng `categories`:**
```sql
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(category_id)
);
```

**Giải thích:**
- `category_id`: Khóa chính
- `name`: Tên hiển thị (ví dụ: "Bóng đá")
- `slug`: Tên rút gọn cho URL (ví dụ: "bong-da")
- `parent_id`: Danh mục cha (hỗ trợ phân cấp: Thể thao > Bóng đá)

**Bảng `articles`:**
```sql
CREATE TABLE articles (
    article_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    excerpt TEXT,
    thumbnail VARCHAR(255),
    category_id INT,
    author_id INT,
    status ENUM('draft', 'published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (author_id) REFERENCES users(user_id)
);
```

**Mối quan hệ:**
- Một `category` có nhiều `articles` (1-n)
- Một `article` thuộc một `category`

#### 2.2. Kiến trúc hệ thống

**Phân lớp:**

1. **Presentation Layer:**
   - `includes/header.php`: Hiển thị menu danh mục
   - `category.php`: Trang danh sách bài viết theo danh mục
   - `index.php`: Trang chủ (hiển thị bài mới nhất)

2. **Business Logic Layer:**
   - `classes/CategoryRepository.php`: Truy vấn danh mục
   - `classes/ArticleRepository.php`: Truy vấn bài viết theo danh mục

3. **Data Access Layer:**
   - JOIN giữa `articles` và `categories`
   - Prepared Statements

#### 2.3. Sơ đồ luồng hiển thị danh sách

```
User click menu danh mục (header)
  -> Chuyển đến category.php?slug=<slug>
  -> CategoryRepository::findBySlug(slug) (lấy thông tin danh mục)
  -> CategoryRepository::getChildren(category_id) (lấy danh mục con)
  -> Thu thập tất cả category_ids (cha + con)
  -> ArticleRepository::getArticlesByCategorySlug(slug)
      -> JOIN articles với categories
      -> WHERE category_id IN (category_ids)
      -> AND status = 'published'
      -> ORDER BY created_at DESC
      -> LIMIT/OFFSET (phân trang)
  -> Render danh sách bài viết
```

### 3. XÂY DỰNG CHỨC NĂNG

#### 3.1. Hiển thị menu danh mục

**File: `includes/header.php`**

```php
<?php
// Lấy danh sách danh mục cha (parent_id = NULL)
$db = Database::getInstance();
$categoryRepo = new CategoryRepository($db);
$categories = $categoryRepo->getAllParent(); // Lấy danh mục gốc
?>

<header>
    <div class="logo">
        <a href="index.php">Tin Thể Thao</a>
    </div>
    
    <nav class="main-nav">
        <!-- Menu danh mục -->
        <?php foreach ($categories as $category): ?>
            <a href="category.php?slug=<?= htmlspecialchars($category->slug) ?>" 
               class="nav-link">
                <?= htmlspecialchars($category->name) ?>
            </a>
        <?php endforeach; ?>
        
        <!-- Menu user (đã mô tả ở Câu 2) -->
        <?php if (Auth::currentUser()): ?>
            <a href="account.php">Tài khoản</a>
            <a href="user_logout.php">Đăng xuất</a>
        <?php else: ?>
            <a href="user_register.php">Đăng ký</a>
            <a href="user_login.php">Đăng nhập</a>
        <?php endif; ?>
    </nav>
</header>
```

**Class CategoryRepository - Lấy danh mục:**

```php
class CategoryRepository {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Lấy tất cả danh mục cha (không có parent)
     */
    public function getAllParent() {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT category_id AS id, name, slug, parent_id
            FROM categories
            WHERE parent_id IS NULL
            ORDER BY name ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_object()) {
            $categories[] = $row;
        }
        return $categories;
    }
    
    /**
     * Tìm danh mục theo slug
     */
    public function findBySlug($slug) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT category_id AS id, name, slug, parent_id
            FROM categories
            WHERE slug = ?
        ");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        return $stmt->get_result()->fetch_object();
    }
    
    /**
     * Lấy danh mục con
     */
    public function getChildren($parent_id) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT category_id AS id, name, slug, parent_id
            FROM categories
            WHERE parent_id = ?
        ");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $children = [];
        while ($row = $result->fetch_object()) {
            $children[] = $row;
        }
        return $children;
    }
}
```

#### 3.2. Trang danh sách bài viết theo danh mục

**File: `category.php`**

```php
<?php
require_once 'config.php';
require_once 'classes/Database.php';
require_once 'classes/CategoryRepository.php';
require_once 'classes/ArticleRepository.php';

// Bước 1: Lấy slug từ URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: index.php");
    exit;
}

// Bước 2: Tìm thông tin danh mục
$db = Database::getInstance();
$categoryRepo = new CategoryRepository($db);
$category = $categoryRepo->findBySlug($slug);

if (!$category) {
    // Danh mục không tồn tại
    http_response_code(404);
    echo "Danh mục không tồn tại";
    exit;
}

// Bước 3: Lấy danh sách bài viết theo danh mục
$articleRepo = new ArticleRepository($db);
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // Số bài mỗi trang

$articles = $articleRepo->getArticlesByCategorySlug($slug, $page, $limit);
$totalArticles = $articleRepo->countArticlesByCategory($category->id);
$totalPages = ceil($totalArticles / $limit);

// Bước 4: Hiển thị
include 'includes/header.php';
?>

<main>
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Trang chủ</a> &raquo; 
            <?= htmlspecialchars($category->name) ?>
        </div>
        
        <!-- Tiêu đề danh mục -->
        <h1><?= htmlspecialchars($category->name) ?></h1>
        <p class="category-meta"><?= $totalArticles ?> bài viết</p>
        
        <!-- Danh sách bài viết -->
        <?php if (empty($articles)): ?>
            <p class="no-content">Chưa có bài viết nào trong danh mục này.</p>
        <?php else: ?>
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <article class="article-card">
                        <a href="article.php?slug=<?= htmlspecialchars($article->slug) ?>">
                            <img src="<?= htmlspecialchars($article->thumbnail) ?>" 
                                 alt="<?= htmlspecialchars($article->title) ?>">
                        </a>
                        
                        <div class="card-content">
                            <h2>
                                <a href="article.php?slug=<?= htmlspecialchars($article->slug) ?>">
                                    <?= htmlspecialchars($article->title) ?>
                                </a>
                            </h2>
                            
                            <p class="excerpt"><?= htmlspecialchars($article->excerpt) ?></p>
                            
                            <div class="meta">
                                <span class="category"><?= htmlspecialchars($article->category_name) ?></span>
                                <span class="author">Bởi <?= htmlspecialchars($article->author_name) ?></span>
                                <span class="date"><?= date('d/m/Y', strtotime($article->created_at)) ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            
            <!-- Phân trang -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?slug=<?= $slug ?>&page=<?= $page - 1 ?>">&laquo; Trước</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?slug=<?= $slug ?>&page=<?= $i ?>" 
                           class="<?= $i === $page ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?slug=<?= $slug ?>&page=<?= $page + 1 ?>">Sau &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
```

#### 3.3. Class ArticleRepository - Lấy bài viết theo danh mục

**File: `classes/ArticleRepository.php`**

```php
class ArticleRepository {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Lấy bài viết theo slug danh mục (bao gồm cả danh mục con)
     */
    public function getArticlesByCategorySlug($slug, $page = 1, $limit = 10) {
        $conn = $this->db->getConnection();
        
        // Bước 1: Lấy category_id từ slug
        $categoryRepo = new CategoryRepository($this->db);
        $category = $categoryRepo->findBySlug($slug);
        
        if (!$category) {
            return [];
        }
        
        // Bước 2: Lấy tất cả category_id (cha + con)
        $category_ids = [$category->id];
        $children = $categoryRepo->getChildren($category->id);
        foreach ($children as $child) {
            $category_ids[] = $child->id;
        }
        
        // Bước 3: Tạo placeholders cho IN clause
        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
        
        // Bước 4: Tính offset
        $offset = ($page - 1) * $limit;
        
        // Bước 5: Query bài viết
        $sql = "
            SELECT 
                a.article_id AS id,
                a.title,
                a.slug,
                a.excerpt,
                a.thumbnail,
                a.created_at,
                c.name AS category_name,
                u.username AS author_name
            FROM articles a
            JOIN categories c ON a.category_id = c.category_id
            JOIN users u ON a.author_id = u.user_id
            WHERE a.category_id IN ($placeholders)
              AND a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $stmt = $conn->prepare($sql);
        
        // Bind category_ids (dynamic)
        $types = str_repeat('i', count($category_ids)) . 'ii'; // i cho category_ids, ii cho limit/offset
        $params = array_merge($category_ids, [$limit, $offset]);
        $stmt->bind_param($types, ...$params);
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $articles = [];
        while ($row = $result->fetch_object()) {
            $articles[] = $row;
        }
        return $articles;
    }
    
    /**
     * Đếm số bài viết theo danh mục
     */
    public function countArticlesByCategory($category_id) {
        $conn = $this->db->getConnection();
        
        // Lấy category_id của danh mục con
        $categoryRepo = new CategoryRepository($this->db);
        $category_ids = [$category_id];
        $children = $categoryRepo->getChildren($category_id);
        foreach ($children as $child) {
            $category_ids[] = $child->id;
        }
        
        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
        
        $sql = "
            SELECT COUNT(*) as total
            FROM articles
            WHERE category_id IN ($placeholders)
              AND status = 'published'
        ";
        
        $stmt = $conn->prepare($sql);
        $types = str_repeat('i', count($category_ids));
        $stmt->bind_param($types, ...$category_ids);
        $stmt->execute();
        
        return $stmt->get_result()->fetch_object()->total;
    }
    
    /**
     * Lấy bài viết mới nhất (trang chủ)
     */
    public function getLatestArticles($limit = 10) {
        $conn = $this->db->getConnection();
        $stmt = $conn->prepare("
            SELECT 
                a.article_id AS id,
                a.title,
                a.slug,
                a.excerpt,
                a.thumbnail,
                a.created_at,
                c.name AS category_name,
                u.username AS author_name
            FROM articles a
            JOIN categories c ON a.category_id = c.category_id
            JOIN users u ON a.author_id = u.user_id
            WHERE a.status = 'published'
            ORDER BY a.created_at DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $articles = [];
        while ($row = $result->fetch_object()) {
            $articles[] = $row;
        }
        return $articles;
    }
}
```

### 4. TỐI ƯU HÓA VÀ MỞ RỘNG

#### 4.1. Các điểm tối ưu đã áp dụng

1. **Database Indexing:**
   - Index trên `slug` (UNIQUE) để tìm kiếm nhanh
   - Index trên `category_id`, `status` để JOIN/WHERE hiệu quả
   ```sql
   CREATE INDEX idx_articles_category ON articles(category_id);
   CREATE INDEX idx_articles_status ON articles(status);
   ```

2. **Pagination:**
   - Giới hạn số bài mỗi trang (LIMIT/OFFSET)
   - Tránh tải toàn bộ database

3. **JOIN Optimization:**
   - Chỉ SELECT các cột cần thiết
   - JOIN một lần để lấy đủ thông tin (category, author)

4. **Query Caching (tùy chọn):**
   - Cache danh sách danh mục (ít thay đổi)
   - Cache số lượng bài viết

#### 4.2. Các tính năng mở rộng

1. **Breadcrumb:**
   - Hiển thị đường dẫn: Trang chủ > Thể thao > Bóng đá

2. **Filter nâng cao:**
   - Lọc theo thời gian (tuần này, tháng này...)
   - Sắp xếp theo lượt xem, lượt thích

3. **SEO:**
   - Meta tags cho từng danh mục
   - Canonical URL
   - Schema.org markup

4. **Responsive Design:**
   - Grid layout tự động điều chỉnh theo màn hình
   - Mobile-friendly navigation

### 5. DEMO CHỨC NĂNG

#### 5.1. Demo menu danh mục

1. **Truy cập trang chủ:**
   - URL: `http://localhost/webthethao_project/index.php`

2. **Quan sát menu:**
   - Thanh điều hướng hiển thị các danh mục: "Bóng đá", "Bóng rổ", "Tennis"...
   - Mỗi link dẫn đến `category.php?slug=<slug>`

#### 5.2. Demo lọc bài viết theo danh mục

1. **Click vào danh mục "Bóng đá":**
   - Chuyển đến: `category.php?slug=bong-da`

2. **Quan sát kết quả:**
   - Tiêu đề trang: "Bóng đá"
   - Breadcrumb: "Trang chủ » Bóng đá"
   - Danh sách bài viết thuộc danh mục Bóng đá (và danh mục con nếu có)
   - Mỗi bài hiển thị: thumbnail, tiêu đề, excerpt, tên tác giả, ngày đăng

3. **Thử phân trang:**
   - Nếu có > 10 bài viết, thanh phân trang xuất hiện
   - Click "Trang 2" để xem các bài tiếp theo

#### 5.3. Demo danh mục rỗng

1. **Tạo danh mục mới chưa có bài viết:**
   - Vào admin: `admin/categories.php`
   - Thêm danh mục "Cầu lông"

2. **Truy cập danh mục:**
   - URL: `category.php?slug=cau-long`

3. **Kết quả:**
   - Hiển thị: "Chưa có bài viết nào trong danh mục này."
   - Không bị lỗi, xử lý trạng thái trống một cách thân thiện

#### 5.4. Demo danh mục phân cấp

1. **Tạo cấu trúc:**
   - Danh mục cha: "Thể thao"
   - Danh mục con: "Bóng đá", "Bóng rổ"

2. **Thêm bài viết:**
   - Bài A: thuộc danh mục "Bóng đá"
   - Bài B: thuộc danh mục "Thể thao"

3. **Truy cập "Thể thao":**
   - Hiển thị cả Bài A và Bài B (bao gồm cả danh mục con)

4. **Truy cập "Bóng đá":**
   - Chỉ hiển thị Bài A

---

## KẾT LUẬN

### 1. Tổng kết các chức năng đã xây dựng

| Câu hỏi | Chức năng | File chính | Class liên quan |
|---------|-----------|------------|-----------------|
| Câu 2 | Đăng ký, Đăng nhập | `user_register.php`, `user_login.php` | `Auth`, `UserRepository` |
| Câu 3 | Cập nhật Profile | `account.php` | `UserRepository`, `InteractionRepository` |
| Câu 4 | Hiển thị danh sách | `category.php`, `includes/header.php` | `CategoryRepository`, `ArticleRepository` |

### 2. Công nghệ sử dụng

- **Backend**: PHP 7.4+
- **Database**: MySQL 8.0
- **Security**: CSRF Token, password_hash, Prepared Statements
- **Architecture**: Repository Pattern, MVC-like structure
- **Frontend**: HTML5, CSS3 (responsive)

### 3. Điểm mạnh của hệ thống

1. **Bảo mật:**
   - CSRF protection cho mọi form
   - Password hashing với bcrypt
   - SQL Injection prevention
   - Session security

2. **Khả năng mở rộng:**
   - Repository pattern dễ thay đổi database
   - Phân tầng rõ ràng (Presentation, Business, Data)
   - Hỗ trợ phân cấp danh mục

3. **Hiệu năng:**
   - Pagination giảm tải database
   - Index trên các cột quan trọng
   - JOIN tối ưu

4. **Trải nghiệm người dùng:**
   - Thông báo lỗi rõ ràng
   - Xử lý trạng thái trống
   - Menu động theo trạng thái đăng nhập

### 4. Hướng phát triển

1. **Ngắn hạn:**
   - Thêm avatar cho user
   - Cho phép cập nhật email
   - Thêm tìm kiếm bài viết

2. **Dài hạn:**
   - Hệ thống comment
   - Notifications
   - RESTful API
   - Admin dashboard với thống kê

---

**Ngày hoàn thành:** <?= date('d/m/Y') ?>  
**Phiên bản:** 1.0
