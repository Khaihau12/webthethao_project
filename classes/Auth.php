<?php
// Tên file: classes/Auth.php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/User.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/Helpers.php';

class Auth {
    private $conn;
    private $userRepo;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
        $this->userRepo = new UserRepository($conn);
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password) {
        $user = $this->userRepo->findByUsername($username);
        if (!$user) { return false; }
        if (password_verify($password, $user->password_hash)) {
            $_SESSION['user_id'] = $user->id;
            $_SESSION['user_role'] = $user->role;
            return true;
        }
        return false;
    }

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

    public function currentUser() {
        if (!empty($_SESSION['user_id'])) {
            return $this->userRepo->findById((int)$_SESSION['user_id']);
        }
        return null;
    }

    public function isLoggedIn() {
        return !empty($_SESSION['user_id']);
    }

    public function requireAdmin() {
        if (!$this->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: /webthethao_project/admin/login.php');
            exit;
        }
    }

    public function requireAdminOrEditor() {
        if (!$this->isLoggedIn()) {
            header('Location: /webthethao_project/admin/login.php');
            exit;
        }
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'admin' && $role !== 'editor') {
            header('Location: /webthethao_project/admin/login.php');
            exit;
        }
    }

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