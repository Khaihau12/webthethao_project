<?php
// Tên file: classes/UserRepository.php

/**
 * UserRepository
 * - Cung cấp các thao tác truy vấn/ghi dữ liệu liên quan đến người dùng (users table).
 * - Sử dụng prepared statements để an toàn và tránh SQL injection.
 */
class UserRepository {
    /** @var mysqli */
    private $conn;

    /**
     * Khởi tạo repository với kết nối MySQLi.
     *
     * @param mysqli $conn Kết nối cơ sở dữ liệu đã sẵn sàng.
     */
    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    /**
     * Đếm tổng số người dùng trong hệ thống (mọi role).
     *
     * @return int Tổng số bản ghi trong bảng users.
     */
    public function countUsers() {
        $sql = "SELECT COUNT(*) as cnt FROM users";
        $res = $this->conn->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)$row['cnt'];
        }
        return 0;
    }

    /**
     * Đếm số tài khoản có role = 'admin'.
     *
     * @return int Số lượng admin hiện có.
     */
    public function countAdmins() {
        $sql = "SELECT COUNT(*) as cnt FROM users WHERE role = 'admin'";
        $res = $this->conn->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)$row['cnt'];
        }
        return 0;
    }

    /**
     * Tìm user theo username.
     *
     * @param string $username Tên đăng nhập cần tìm.
     * @return User|null Trả về đối tượng User nếu tìm thấy, ngược lại null.
     */
    public function findByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? new User($row) : null;
    }

    /**
     * Tìm user theo id.
     *
     * @param int $id ID người dùng.
     * @return User|null Trả về User nếu có, ngược lại null.
     */
    public function findById($id) {
    $sql = "SELECT user_id AS id, username, password_hash, role, display_name, email, created_at FROM users WHERE user_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? new User($row) : null;
    }

    /**
     * Tìm user theo email.
     *
     * @param string|null $email Địa chỉ email; nếu rỗng sẽ trả về null ngay.
     * @return User|null Trả về User nếu có, ngược lại null.
     */
    public function findByEmail($email) {
        if ($email === null || $email === '') return null;
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? new User($row) : null;
    }

    /**
     * Tạo người dùng mới với mật khẩu được băm an toàn.
     *
     * @param string $username
     * @param string $password Mật khẩu thuần; sẽ được băm bằng password_hash.
     * @param string $role Vai trò: admin|editor|user (mặc định 'user').
     * @param string|null $display_name Tên hiển thị (tùy chọn).
     * @param string|null $email Email (tùy chọn).
     * @return bool true nếu tạo thành công, false nếu thất bại.
     */
    public function create($username, $password, $role = 'user', $display_name = null, $email = null) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password_hash, role, display_name, email) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("sssss", $username, $password_hash, $role, $display_name, $email);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Cập nhật mật khẩu (băm) và vai trò cho người dùng chỉ định.
     *
     * @param int $user_id ID người dùng.
     * @param string $new_password Mật khẩu mới (sẽ được băm password_hash).
     * @param string $role Vai trò mới: admin|editor|user.
     * @return bool true nếu cập nhật thành công, ngược lại false.
     */
    public function updatePasswordAndRole($user_id, $new_password, $role) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password_hash = ?, role = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssi', $hash, $role, $user_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Chỉ thay đổi role của người dùng.
     *
     * @param int $user_id ID người dùng.
     * @param string $role Vai trò mới: admin|editor|user.
     * @return bool true nếu cập nhật thành công, ngược lại false.
     */
    public function updateRole($user_id, $role) {
    $sql = "UPDATE users SET role = ? WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('si', $role, $user_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>