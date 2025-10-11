<?php
// Tên file: classes/Helpers.php
class Helpers {
    public static function slugify($text) {
        $text = trim($text);
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('~[áàảãạăắằẳẵặâấầẩẫậ]~u', 'a', $text);
        $text = preg_replace('~[éèẻẽẹêếềểễệ]~u', 'e', $text);
        $text = preg_replace('~[íìỉĩị]~u', 'i', $text);
        $text = preg_replace('~[óòỏõọôốồổỗộơớờởỡợ]~u', 'o', $text);
        $text = preg_replace('~[úùủũụưứừửữự]~u', 'u', $text);
        $text = preg_replace('~[ýỳỷỹỵ]~u', 'y', $text);
        $text = preg_replace('~đ~u', 'd', $text);
        $text = preg_replace('~[^a-z0-9]+~u', '-', $text);
        $text = trim($text, '-');
        return $text;
    }

    public static function csrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
    }
}
?>