<?php
// Tên file: classes/User.php
class User {
    public $id;
    public $username;
    public $password_hash;
    public $role; // admin | editor
    public $display_name;
    public $email;
    public $created_at;

    public function __construct($data = []) {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
?>