<?php
// Tên file: classes/Database.php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli("dpg-d3jmool6ubrc73cv4qm0-a", "project_141i_user", "57LJ3gV9jFPNb1gB8W0aq1TTI9tTyzZ8", "project");

        if ($this->conn->connect_error) {
            die("Kết nối database thất bại: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4");
    }

    public static function getInstance() {
        if (self::$instance == null) {
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
