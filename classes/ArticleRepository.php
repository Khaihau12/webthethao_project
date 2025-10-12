<?php
// Tên file: classes/ArticleRepository.php
class ArticleRepository {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

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

    public function getFeaturedArticle() {
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug,
                       u.id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
                FROM articles a JOIN categories c ON a.category_id = c.id
                LEFT JOIN users u ON a.author_id = u.id
                WHERE a.is_featured = 1 ORDER BY a.created_at DESC LIMIT 1";
        $articles = $this->fetchArticles($sql);
        return !empty($articles) ? $articles[0] : null;
    }

    public function getLatestArticles($limit = 10, $exclude_featured = true) {
    $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug,
               u.id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.id 
        LEFT JOIN users u ON a.author_id = u.id ";
        if ($exclude_featured) {
            $sql .= "WHERE a.is_featured = 0 ";
        }
        $sql .= "ORDER BY a.created_at DESC LIMIT ?";
        return $this->fetchArticles($sql, [$limit], "i");
    }
    
    public function getArticlesByCategorySlug($slug, $limit = 10) {
        // 1. Tìm ID của chuyên mục cha và tất cả chuyên mục con của nó
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE slug = ?");
        $stmt->bind_param("s", $slug);
        $stmt->execute();
        $parent_result = $stmt->get_result();
        if ($parent_result->num_rows === 0) return [];
        $parent_id = $parent_result->fetch_assoc()['id'];
        
        $stmt = $this->conn->prepare("SELECT id FROM categories WHERE parent_id = ?");
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
        $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug
                FROM articles a JOIN categories c ON a.category_id = c.id
                WHERE a.category_id IN ($placeholders) ORDER BY a.created_at DESC LIMIT ?";
                
        return $this->fetchArticles($sql, $params, $types);
    }

    public function getArticleBySlug($slug) {
    $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug,
               u.id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.author_id = u.id
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
    public function listAll($limit = 50, $offset = 0, $search = '') {
    $sql = "SELECT a.*, c.name as category_name, c.slug as category_slug,
               u.id AS author_id, u.username AS author_username, COALESCE(u.display_name, u.username) AS author_name
        FROM articles a JOIN categories c ON a.category_id = c.id
        LEFT JOIN users u ON a.author_id = u.id";
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

    public function update($id, $data) {
        $sql = "UPDATE articles SET category_id = ?, title = ?, slug = ?, summary = ?, content = ?, image_url = ?, is_featured = ?, author_id = ? WHERE id = ?";
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

    public function delete($id) {
        $sql = "DELETE FROM articles WHERE id = ?";
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