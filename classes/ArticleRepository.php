<?php
// Tên file: classes/ArticleRepository.php
/**
 * ArticleRepository
 * - Truy vấn và CRUD bài viết, phục vụ cả trang công khai và trang quản trị.
 */
class ArticleRepository {
    private $conn;

    /** @param mysqli $conn Kết nối CSDL */
    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    /**
     * Helper thực thi truy vấn và ánh xạ kết quả thành mảng Article.
     * @param string $sql
     * @param array $params
     * @param string $types Chuỗi kiểu bind_param (vd: 'iis')
     * @return Article[]
     */
    private function fetchArticles($sql, $params = [], $types = "") {
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return [];
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $articles = [];
        while ($row = $result->fetch_assoc()) {
            $articles[] = new Article($row);
        }
        $stmt->close();
        return $articles;
    }

    /**
     * Lấy 1 bài viết nổi bật nhất (is_featured=1) mới nhất.
     * @return Article|null
     */
    public function getFeaturedArticle() {
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at,
               c.name as category_name, c.slug as category_slug,
               u.user_id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
                WHERE a.is_featured = 1 ORDER BY a.created_at DESC LIMIT 1";
        $articles = $this->fetchArticles($sql);
        return !empty($articles) ? $articles[0] : null;
    }

    /**
     * Lấy danh sách bài viết mới nhất.
     * @param int $limit Số lượng cần lấy.
     * @param bool $exclude_featured Bỏ qua bài nổi bật hay không.
     * @return Article[]
     */
    public function getLatestArticles($limit = 10, $exclude_featured = true) {
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at,
               c.name as category_name, c.slug as category_slug,
               u.user_id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.category_id 
        LEFT JOIN users u ON a.author_id = u.user_id ";
        if ($exclude_featured) {
            $sql .= "WHERE a.is_featured = 0 ";
        }
        $sql .= "ORDER BY a.created_at DESC LIMIT ?";
        return $this->fetchArticles($sql, [$limit], "i");
    }
    
    /**
     * Lấy bài theo slug chuyên mục, bao gồm cả các chuyên mục con trực tiếp.
     * @param string $slug Slug chuyên mục cha.
     * @param int $limit Số lượng bài tối đa.
     * @return Article[]
     */
    public function getArticlesByCategorySlug($slug, $limit = 10) {
        // 1. Tìm ID của chuyên mục cha và tất cả chuyên mục con của nó
    $stmt = $this->conn->prepare("SELECT category_id AS id FROM categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $parent_result = $stmt->get_result();
        if ($parent_result->num_rows === 0) return [];
        $parent_id = $parent_result->fetch_assoc()['id'];
        
    $stmt = $this->conn->prepare("SELECT category_id AS id FROM categories WHERE parent_id = ?");
        $stmt->bind_param("i", $parent_id);
        $stmt->execute();
        $child_result = $stmt->get_result();
        $category_ids = [$parent_id]; // Bao gồm cả chính nó
        while($row = $child_result->fetch_assoc()) {
            $category_ids[] = $row['id'];
        }
        $stmt->close();
        
        // 2. Tạo chuỗi placeholder (?, ?, ?) cho câu lệnh IN
        $placeholders = implode(',', array_fill(0, count($category_ids), '?'));
        $types = str_repeat('i', count($category_ids)) . 'i';
        $params = array_merge($category_ids, [$limit]);

        // 3. Truy vấn các bài viết có category_id nằm trong danh sách ID đã tìm được
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at,
               c.name as category_name, c.slug as category_slug
        FROM articles a JOIN categories c ON a.category_id = c.category_id
                WHERE a.category_id IN ($placeholders) ORDER BY a.created_at DESC LIMIT ?";
                
        return $this->fetchArticles($sql, $params, $types);
    }

    /**
     * Lấy bài viết theo slug.
     * @param string $slug
     * @return Article|null
     */
    public function getArticleBySlug($slug) {
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at,
               c.name as category_name, c.slug as category_slug,
        u.user_id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id
                WHERE a.slug = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row ? new Article($row) : null;
    }
    
    /**
     * Lấy dữ liệu cho khối chuyên mục: 1 bài chính + N bài phụ.
     * @return array{main_article: ?Article, sub_articles: Article[]}
     */
    public function getArticlesForCategorySection($category_slug, $main_limit = 1, $sub_limit = 3) {
        $total_limit = $main_limit + $sub_limit;
        $all_articles = $this->getArticlesByCategorySlug($category_slug, $total_limit);
        
        if (empty($all_articles)) {
            return ['main_article' => null, 'sub_articles' => []];
        }
        $main_article = array_slice($all_articles, 0, $main_limit);
        $sub_articles = array_slice($all_articles, $main_limit, $sub_limit);
        return [
            'main_article' => !empty($main_article) ? $main_article[0] : null,
            'sub_articles' => $sub_articles
        ];
    }

    // =====================
    // CRUD cho trang quản trị
    // =====================
    /**
     * Danh sách bài cho quản trị (có tìm kiếm theo tiêu đề, phân trang).
     * @return Article[]
     */
    public function listAll($limit = 50, $offset = 0, $search = '') {
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at,
               c.name as category_name, c.slug as category_slug,
        u.user_id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.category_id
        LEFT JOIN users u ON a.author_id = u.user_id";
        $params = [];
        $types = '';
        if ($search !== '') {
            $sql .= " WHERE a.title LIKE ?";
            $params[] = '%' . $search . '%';
            $types .= 's';
        }
        $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit; $types .= 'i';
        $params[] = $offset; $types .= 'i';
        return $this->fetchArticles($sql, $params, $types);
    }

    /**
     * Tạo bài viết mới.
     * @param array $data Trường bắt buộc: category_id, title, slug. Tùy chọn: summary, content, image_url, is_featured, author_id
     * @return bool true nếu tạo thành công.
     */
    public function create($data) {
        $sql = "INSERT INTO articles (category_id, title, slug, summary, content, image_url, is_featured, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $category_id = (int)$data['category_id'];
        $title = $data['title'];
        $slug = $data['slug'];
        $summary = $data['summary'] ?? null;
        $content = $data['content'] ?? null;
        $image_url = $data['image_url'] ?? null;
        $is_featured = !empty($data['is_featured']) ? 1 : 0;
        $author_id = isset($data['author_id']) ? (int)$data['author_id'] : null;
        $stmt->bind_param('isssssii', $category_id, $title, $slug, $summary, $content, $image_url, $is_featured, $author_id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Cập nhật bài viết theo id.
     * @param int $id
     * @param array $data
     * @return bool true nếu cập nhật thành công.
     */
    public function update($id, $data) {
    $sql = "UPDATE articles SET category_id = ?, title = ?, slug = ?, summary = ?, content = ?, image_url = ?, is_featured = ?, author_id = ? WHERE article_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) return false;
        $category_id = (int)$data['category_id'];
        $title = $data['title'];
        $slug = $data['slug'];
        $summary = $data['summary'] ?? null;
        $content = $data['content'] ?? null;
        $image_url = $data['image_url'] ?? null;
        $is_featured = !empty($data['is_featured']) ? 1 : 0;
        $id = (int)$id;
        $author_id = isset($data['author_id']) ? (int)$data['author_id'] : null;
        $stmt->bind_param('isssssiii', $category_id, $title, $slug, $summary, $content, $image_url, $is_featured, $author_id, $id);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Xóa bài viết theo id.
     * @param int $id
     * @return bool true nếu xóa thành công.
     */
    public function delete($id) {
    $sql = "DELETE FROM articles WHERE article_id = ?";
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