<?php
// Tên file: classes/InteractionRepository.php
/**
 * InteractionRepository
 * - Quản lý các tương tác của người dùng với bài viết: like, save, view, comment.
 */
class InteractionRepository {
    private $conn;
    /** @param mysqli $conn Kết nối CSDL */
    public function __construct(mysqli $conn) { $this->conn = $conn; }

    // Likes
    /**
     * Bật/tắt trạng thái like cho một bài viết bởi user.
     * @return bool true nếu sau thao tác là đã like; false nếu là bỏ like.
     */
    public function toggleLike($user_id, $article_id) {
        if ($this->isLiked($user_id, $article_id)) {
            $stmt = $this->conn->prepare("DELETE FROM article_likes WHERE user_id = ? AND article_id = ?");
            $stmt->bind_param('ii', $user_id, $article_id);
            $stmt->execute();
            $stmt->close();
            return false; // now unliked
        } else {
            $stmt = $this->conn->prepare("INSERT INTO article_likes (user_id, article_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
            $stmt->bind_param('ii', $user_id, $article_id);
            $stmt->execute();
            $stmt->close();
            return true; // now liked
        }
    }
    /**
     * Kiểm tra user đã like bài viết hay chưa.
     * @return bool
     */
    public function isLiked($user_id, $article_id) {
        $stmt = $this->conn->prepare("SELECT 1 FROM article_likes WHERE user_id = ? AND article_id = ? LIMIT 1");
        $stmt->bind_param('ii', $user_id, $article_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    /**
     * Đếm số lượt like của một bài viết.
     * @return int
     */
    public function likeCount($article_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM article_likes WHERE article_id = ?");
        $stmt->bind_param('i', $article_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Saves
    /**
     * Bật/tắt lưu (bookmark) bài viết.
     * @return bool true nếu sau thao tác là đã lưu; false nếu là bỏ lưu.
     */
    public function toggleSave($user_id, $article_id) {
        if ($this->isSaved($user_id, $article_id)) {
            $stmt = $this->conn->prepare("DELETE FROM article_saves WHERE user_id = ? AND article_id = ?");
            $stmt->bind_param('ii', $user_id, $article_id);
            $stmt->execute();
            $stmt->close();
            return false;
        } else {
            $stmt = $this->conn->prepare("INSERT INTO article_saves (user_id, article_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
            $stmt->bind_param('ii', $user_id, $article_id);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    }
    /**
     * Kiểm tra user đã lưu bài hay chưa.
     * @return bool
     */
    public function isSaved($user_id, $article_id) {
        $stmt = $this->conn->prepare("SELECT 1 FROM article_saves WHERE user_id = ? AND article_id = ? LIMIT 1");
        $stmt->bind_param('ii', $user_id, $article_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    /**
     * Liệt kê các bài viết đã lưu của user theo thời gian lưu mới nhất.
     * @return array danh sách record bài viết (mảng kết hợp)
     */
    public function listSaved($user_id, $limit = 50, $offset = 0) {
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at
        FROM article_saves s JOIN articles a ON s.article_id = a.article_id WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }
    /**
     * Đếm tổng số bài viết đã lưu của user.
     * @return int
     */
    public function countSaved($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM article_saves WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Views
    /**
     * Ghi nhận lần xem gần nhất cho (user, article). Upsert bằng ON DUPLICATE KEY.
     * @return void
     */
    public function touchView($user_id, $article_id) {
        $stmt = $this->conn->prepare("INSERT INTO article_views (user_id, article_id, viewed_at) VALUES (?, ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
        $stmt->bind_param('ii', $user_id, $article_id);
        $stmt->execute();
        $stmt->close();
    }
    /**
     * Liệt kê các bài viết đã xem (gần nhất trước).
     * @return array danh sách record bài viết (mảng kết hợp)
     */
    public function listViewed($user_id, $limit = 50, $offset = 0) {
    $sql = "SELECT a.article_id AS id, a.category_id, a.title, a.slug, a.summary, a.content, a.image_url, a.is_featured, a.created_at
        FROM article_views v JOIN articles a ON v.article_id = a.article_id WHERE v.user_id = ? ORDER BY v.viewed_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }
    /**
     * Đếm tổng số bài viết đã xem của user.
     * @return int
     */
    public function countViewed($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM article_views WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Comments
    /**
     * Thêm bình luận vào bài viết.
     * @return bool true nếu thêm thành công.
     */
    public function addComment($user_id, $article_id, $content) {
        $sql = "INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iis', $article_id, $user_id, $content);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    /**
     * Lấy danh sách bình luận của bài viết, kèm username/display_name.
     * @return array
     */
    public function getComments($article_id, $limit = 50, $offset = 0) {
    $sql = "SELECT c.comment_id, c.article_id, c.user_id, c.content, c.created_at, u.username, u.display_name FROM comments c JOIN users u ON c.user_id = u.user_id WHERE c.article_id = ? ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $article_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }
}
?>