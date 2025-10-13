<?php
// Tên file: classes/Auth.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/Helpers.php';

/**
 * Auth
 * - Xử lý đăng nhập/đăng xuất, đọc user hiện tại từ session,
 *   và các guard kiểm tra quyền truy cập (admin/editor).
 */
class Auth {
    private $conn;
    private $userRepo;

    /**
     * Khởi tạo Auth với kết nối DB và đảm bảo session đã bắt đầu.
     * @param mysqli $conn
     */
    public function __construct(mysqli $conn) {
        $this->conn = $conn;
        $this->userRepo = new UserRepository($conn);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Đăng nhập với username/password. Lưu user_id và user_role vào session nếu thành công.
     * @param string $username
     * @param string $password
     * @return bool true nếu thành công; false nếu sai thông tin.
     */
    public function login($username, $password) {
        // Câu 2 (Đăng nhập):
        // - Tìm user theo username
        // - Dùng password_verify để so sánh mật khẩu
        // - Nếu đúng, lưu user_id & user_role vào session để các trang khác nhận biết đã đăng nhập
        $user = $this->userRepo->findByUsername($username);
        if (!$user) { return false; }
        if (password_verify($password, $user->password_hash)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            return true;
        }
        return false;
    }

    /**
     * Đăng xuất: xóa session và cookie phiên làm việc nếu có.
     * @return void
     */
    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Lấy đối tượng User đang đăng nhập (hoặc null nếu chưa đăng nhập).
     * @return User|null
     */
    public function currentUser() {
        // Trả về đối tượng User từ session hiện tại (nếu có)
        if (!empty($_SESSION['user_id'])) {
            return $this->userRepo->findById((int)$_SESSION['user_id']);
        }
        return null;
    }

    /**
     * Kiểm tra trạng thái đăng nhập hiện tại.
     * @return bool
     */
    public function isLoggedIn() {
        return !empty($_SESSION['user_id']);
    }

    /**
     * Bảo vệ trang chỉ dành cho admin. Redirect về trang đăng nhập nếu không đủ quyền.
     * @return void
     */
    public function requireAdmin() {
        // Guard trang admin: nếu chưa đăng nhập hoặc không phải admin => chuyển về trang login admin
        if (!$this->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_URL . '/admin/login.php');
            exit;
        }
    }

    /**
     * Bảo vệ trang dành cho admin hoặc editor. Redirect nếu chưa đăng nhập hoặc sai vai trò.
     * @return void
     */
    public function requireAdminOrEditor() {
        // Guard trang quản trị chung: cho phép admin hoặc editor
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . '/admin/login.php');
            exit;
        }
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'admin' && $role !== 'editor') {
            header('Location: ' . BASE_URL . '/admin/login.php');
            exit;
        }
    }

    /**
     * Tạo admin đầu tiên nếu hệ thống chưa có admin nào.
     * - Nếu username đã tồn tại, trả về false.
     * - Nếu đã có admin, trả về true (không cần tạo thêm).
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function bootstrapFirstAdmin($username, $password) {
        // Chỉ cho tạo admin nếu CHƯA có tài khoản admin nào
        if ($this->userRepo->countAdmins() === 0) {
            // Nếu username đã tồn tại (dù là user/editor), KHÔNG tạo và trả về false
            $existing = $this->userRepo->findByUsername($username);
            if ($existing) { return false; }
            // Tạo admin mới với username chưa tồn tại
            return $this->userRepo->create($username, $password, 'admin', $username, null);
        }
        return true; // Đã có admin rồi => bỏ qua
    }
}
?>