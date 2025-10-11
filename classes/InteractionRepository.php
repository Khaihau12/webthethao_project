<?php
// Tên file: classes/InteractionRepository.php
class InteractionRepository {
    private $conn;
    public function __construct(mysqli $conn) { $this->conn = $conn; }

    // Likes
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
    public function isLiked($user_id, $article_id) {
        $stmt = $this->conn->prepare("SELECT 1 FROM article_likes WHERE user_id = ? AND article_id = ? LIMIT 1");
        $stmt->bind_param('ii', $user_id, $article_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    public function likeCount($article_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM article_likes WHERE article_id = ?");
        $stmt->bind_param('i', $article_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Saves
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
    public function isSaved($user_id, $article_id) {
        $stmt = $this->conn->prepare("SELECT 1 FROM article_saves WHERE user_id = ? AND article_id = ? LIMIT 1");
        $stmt->bind_param('ii', $user_id, $article_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res && $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }
    public function listSaved($user_id, $limit = 50, $offset = 0) {
        $sql = "SELECT a.* FROM article_saves s JOIN articles a ON s.article_id = a.id WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }
    public function countSaved($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM article_saves WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Views
    public function touchView($user_id, $article_id) {
        $stmt = $this->conn->prepare("INSERT INTO article_views (user_id, article_id, viewed_at) VALUES (?, ?, CURRENT_TIMESTAMP) ON DUPLICATE KEY UPDATE viewed_at = CURRENT_TIMESTAMP");
        $stmt->bind_param('ii', $user_id, $article_id);
        $stmt->execute();
        $stmt->close();
    }
    public function listViewed($user_id, $limit = 50, $offset = 0) {
        $sql = "SELECT a.* FROM article_views v JOIN articles a ON v.article_id = a.id WHERE v.user_id = ? ORDER BY v.viewed_at DESC LIMIT ? OFFSET ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iii', $user_id, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        $stmt->close();
        return $rows;
    }
    public function countViewed($user_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as cnt FROM article_views WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    // Comments
    public function addComment($user_id, $article_id, $content) {
        $sql = "INSERT INTO comments (article_id, user_id, content) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('iis', $article_id, $user_id, $content);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
    public function getComments($article_id, $limit = 50, $offset = 0) {
        $sql = "SELECT c.*, u.username, u.display_name FROM comments c JOIN users u ON c.user_id = u.id WHERE c.article_id = ? ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
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