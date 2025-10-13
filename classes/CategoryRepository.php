<?php
// Tên file: classes/CategoryRepository.php (MỚI)
/**
 * CategoryRepository
 * - Truy vấn chuyên mục, bao gồm cả danh sách, con theo slug, và CRUD cho admin.
 */
class CategoryRepository {
    private $conn;

    /** @param mysqli $conn Kết nối CSDL */
    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    /**
     * Lấy tất cả các chuyên mục từ CSDL.
     * @return array<int, array{name:string,slug:string,id:int,parent_id:?int}>
     */
    public function getAllCategories() {
    $sql = "SELECT category_id AS id, name, slug, parent_id FROM categories ORDER BY parent_id, name ASC";
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
     * Lấy các chuyên mục con dựa trên slug của chuyên mục cha.
     * @param string $parent_slug Slug của chuyên mục cha.
     * @return array<int, array{name:string,slug:string}>
     */
    public function getChildCategories($parent_slug) {
    $sql = "SELECT c2.name, c2.slug 
        FROM categories c1
        JOIN categories c2 ON c1.category_id = c2.parent_id
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

    // ================
    // CRUD cho quản trị
    // ================
    /**
     * Liệt kê chuyên mục cho trang quản trị (phân trang đơn giản).
     * @param int $limit
     * @param int $offset
     * @return array<int, array>
     */
    public function listAll($limit = 100, $offset = 0) {
    $sql = "SELECT category_id AS id, name, slug, parent_id FROM categories ORDER BY parent_id, name LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $cats = [];
        while ($row = $result->fetch_assoc()) {
            $cats[] = $row;
        }
        $stmt->close();
        return $cats;
    }

    /**
     * Tạo chuyên mục mới.
     * @return bool true nếu thành công.
     */
    public function create($name, $slug, $parent_id = null) {
        $sql = "INSERT INTO categories (name, slug, parent_id) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        if ($parent_id === '' || $parent_id === null) {
            $null = null;
            $stmt->bind_param('ssi', $name, $slug, $null);
        } else {
            $pid = (int)$parent_id;
            $stmt->bind_param('ssi', $name, $slug, $pid);
        }
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Cập nhật chuyên mục theo id.
     * @return bool true nếu thành công.
     */
    public function update($id, $name, $slug, $parent_id = null) {
    $sql = "UPDATE categories SET name = ?, slug = ?, parent_id = ? WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $pid = ($parent_id === '' || $parent_id === null) ? null : (int)$parent_id;
        $id = (int)$id;
        $stmt->bind_param('ssii', $name, $slug, $pid, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Xóa chuyên mục theo id.
     * @return bool true nếu xóa thành công.
     */
    public function delete($id) {
    $sql = "DELETE FROM categories WHERE category_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $id = (int)$id;
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>