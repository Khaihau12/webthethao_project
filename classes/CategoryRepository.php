<?php
// Tên file: classes/CategoryRepository.php (MỚI)
class CategoryRepository {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả các chuyên mục từ CSDL
     */
    public function getAllCategories() {
        $sql = "SELECT id, name, slug, parent_id FROM categories ORDER BY parent_id, name ASC";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        $stmt->close();
        return $categories;
    }
    /**
     * [MỚI] Lấy các chuyên mục con dựa trên slug của chuyên mục cha
     */
    public function getChildCategories($parent_slug) {
        $sql = "SELECT c2.name, c2.slug 
                FROM categories c1
                JOIN categories c2 ON c1.id = c2.parent_id
                WHERE c1.slug = ?
                ORDER BY c2.name ASC";
        
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        
        $stmt->bind_param("s", $parent_slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $children = [];
        while ($row = $result->fetch_assoc()) {
            $children[] = $row;
        }
        $stmt->close();
        return $children;
    }
}
?>