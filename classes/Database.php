<?php
// Tên file: classes/Database.php
require_once __DIR__ . '/../config.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // Kết nối sử dụng hằng số cấu hình, không hardcode
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($this->conn->connect_error) {
            // Gợi ý chạy setup.sql nếu database chưa tồn tại
            die("Kết nối database thất bại: " . $this->conn->connect_error . " (Hãy kiểm tra config.php hoặc chạy setup.sql)");
        }
        // Đảm bảo charset đúng để lưu tiếng Việt và emoji
        $this->conn->set_charset('utf8mb4');
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    // Ngăn chặn việc clone đối tượng
    private function __clone() {}
}
?>
