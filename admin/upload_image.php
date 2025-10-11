<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Helpers.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    $auth = new Auth($conn);
    $auth->requireAdminOrEditor();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok'=>false,'error'=>'Method not allowed']);
        exit;
    }
    if (!Helpers::verifyCsrf($_POST['csrf'] ?? '')) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Invalid CSRF']);
        exit;
    }
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'No file uploaded']);
        exit;
    }

    $file = $_FILES['image'];
    if ($file['size'] > 2*1024*1024) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'File too large (>2MB)']);
        exit;
    }

    // Validate MIME/type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpg', 'image/gif' => 'gif'];
    if (!isset($allowed[$mime])) {
        http_response_code(400);
        echo json_encode(['ok'=>false,'error'=>'Unsupported file type']);
        exit;
    }

    // Ensure upload dir
    $uploadDir = realpath(__DIR__ . '/../assets');
    if ($uploadDir === false) { mkdir(__DIR__ . '/../assets', 0777, true); $uploadDir = realpath(__DIR__ . '/../assets'); }
    $targetDir = $uploadDir . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($targetDir)) { mkdir($targetDir, 0777, true); }

    // Generate safe filename
    $ext = $allowed[$mime];
    $basename = bin2hex(random_bytes(8));
    $filename = $basename . '.' . $ext;
    $dest = $targetDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500);
        echo json_encode(['ok'=>false,'error'=>'Failed to save file']);
        exit;
    }

    // Optional optimization: auto-rotate JPEG, and resize large JPEG/PNG down to max 1600px
    try {
        if (function_exists('getimagesize')) {
            $info = @getimagesize($dest);
            if ($info) {
                $w = $info[0] ?? 0; $h = $info[1] ?? 0; $type = $info[2] ?? null;
                $max = 1600;
                $needsResize = ($w > $max || $h > $max);
                if (in_array($mime, ['image/jpeg','image/png'])) {
                    if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
                        $src = @imagecreatefromjpeg($dest);
                        if ($src) {
                            // Autorotate based on EXIF
                            if (function_exists('exif_read_data')) {
                                $exif = @exif_read_data($dest);
                                $ort = (int)($exif['Orientation'] ?? 1);
                                if ($ort === 3 && function_exists('imagerotate')) { $src = imagerotate($src, 180, 0); }
                                elseif ($ort === 6 && function_exists('imagerotate')) { $src = imagerotate($src, -90, 0); $w = $info[1]; $h = $info[0]; }
                                elseif ($ort === 8 && function_exists('imagerotate')) { $src = imagerotate($src, 90, 0); $w = $info[1]; $h = $info[0]; }
                            }
                            if ($needsResize && function_exists('imagecopyresampled')) {
                                $ratio = min($max/$w, $max/$h);
                                $nw = max(1, (int)floor($w*$ratio));
                                $nh = max(1, (int)floor($h*$ratio));
                                $dst = imagecreatetruecolor($nw, $nh);
                                imagecopyresampled($dst, $src, 0,0,0,0, $nw,$nh,$w,$h);
                                @imagejpeg($dst, $dest, 85);
                                imagedestroy($dst);
                            } else {
                                @imagejpeg($src, $dest, 85);
                            }
                            imagedestroy($src);
                        }
                    } elseif ($mime === 'image/png' && function_exists('imagecreatefrompng')) {
                        $src = @imagecreatefrompng($dest);
                        if ($src) {
                            if ($needsResize && function_exists('imagecopyresampled')) {
                                $ratio = min($max/$w, $max/$h);
                                $nw = max(1, (int)floor($w*$ratio));
                                $nh = max(1, (int)floor($h*$ratio));
                                $dst = imagecreatetruecolor($nw, $nh);
                                imagealphablending($dst, false); imagesavealpha($dst, true);
                                imagecopyresampled($dst, $src, 0,0,0,0, $nw,$nh,$w,$h);
                                @imagepng($dst, $dest, 6);
                                imagedestroy($dst);
                            } else {
                                @imagepng($src, $dest, 6);
                            }
                            imagedestroy($src);
                        }
                    }
                }
            }
        }
    } catch (Throwable $opt) { /* ignore optimization errors */ }

    // Return URL relative to web root
    $base = defined('BASE_URL') ? BASE_URL : '';
    $url = $base . '/assets/uploads/' . $filename;
    echo json_encode(['ok'=>true,'url'=>$url]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Server error']);
}
