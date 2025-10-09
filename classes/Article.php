<?php
// Tên file: classes/Article.php
class Article {
    public $id;
    public $category_id;
    public $title;
    public $slug;
    public $summary;
    public $content;
    public $image_url;
    public $is_featured;
    public $created_at;
    
    // Các thuộc tính được join từ bảng categories
    public $category_name;
    public $category_slug;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
?>