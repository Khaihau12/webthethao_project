<?php
// Tên file: classes/UserRepository.php
class UserRepository {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function countUsers() {
        $sql = "SELECT COUNT(*) as cnt FROM users";
        $res = $this->conn->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)$row['cnt'];
        }
        return 0;
    }

    public function countAdmins() {
        $sql = "SELECT COUNT(*) as cnt FROM users WHERE role = 'admin'";
        $res = $this->conn->query($sql);
        if ($res) {
            $row = $res->fetch_assoc();
            return (int)$row['cnt'];
        }
        return 0;
    }

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

    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row ? new User($row) : null;
    }

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

    public function updatePasswordAndRole($user_id, $new_password, $role) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password_hash = ?, role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('ssi', $hash, $role, $user_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function updateRole($user_id, $role) {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('si', $role, $user_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>