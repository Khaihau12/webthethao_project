<?php
// Tên file: config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'webthethao');
// Optional: TinyMCE API key (free tier available at tiny.cloud). Leave empty to auto-fallback to Quill editor.
if (!defined('TINYMCE_API_KEY')) {
	define('TINYMCE_API_KEY', '');
}
// Base URL for generating absolute links (avoid hardcoding paths)
if (!defined('BASE_URL')) {
    define('BASE_URL', '/webthethao_project');
}
?>