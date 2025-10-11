<?php
// Tên file: classes/MediaManager.php
class MediaManager {
    private static function uploadsDir() {
        $dir = realpath(__DIR__ . '/../assets/uploads');
        if ($dir === false) {
            $base = realpath(__DIR__ . '/../assets');
            if ($base === false) { @mkdir(__DIR__ . '/../assets', 0777, true); $base = realpath(__DIR__ . '/../assets'); }
            $dir = $base . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($dir)) @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    public static function isUploadUrl($url) {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        return strpos($path, '/assets/uploads/') !== false;
    }

    public static function filenameFromUrl($url) {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        return basename($path);
    }

    public static function extractUploadFilenamesFromHtml($html) {
        $files = [];
        if (!is_string($html) || $html === '') return $files;
        if (preg_match_all('#<img[^>]+src=["\']([^"\']+)["\']#i', $html, $m)) {
            foreach ($m[1] as $src) {
                if (self::isUploadUrl($src)) {
                    $files[] = self::filenameFromUrl($src);
                }
            }
        }
        // Also check <source> in case of picture/video sources
        if (preg_match_all('#<source[^>]+src=["\']([^"\']+)["\']#i', $html, $m2)) {
            foreach ($m2[1] as $src) {
                if (self::isUploadUrl($src)) {
                    $files[] = self::filenameFromUrl($src);
                }
            }
        }
        // Normalize unique
        $files = array_values(array_unique(array_filter($files)));
        return $files;
    }

    public static function deleteIfUnreferenced(mysqli $conn, $filename, $skipArticleId = 0) {
        // Only allow our generated filenames (hex16 + extension) to be deleted
        if (!preg_match('/^[a-f0-9]{16}\.(?:jpg|jpeg|png|gif)$/i', $filename)) {
            return false;
        }
        // Check other article references
        $like = '%' . $filename . '%';
        $sql = "SELECT COUNT(*) AS cnt FROM articles WHERE id <> ? AND (content LIKE ? OR image_url LIKE ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param('iss', $skipArticleId, $like, $like);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $cnt = (int)($res['cnt'] ?? 0);
        if ($cnt > 0) return false; // still in use elsewhere

        $uploads = self::uploadsDir();
        $full = $uploads . DIRECTORY_SEPARATOR . $filename;
        if (is_file($full)) {
            @unlink($full);
            return !file_exists($full);
        }
        return false;
    }

        // Render a small upload/picker UI and bind it to a text input selector
        public static function renderQuickImagePicker($inputSelector, $csrfToken) {
                $selectorJs = json_encode($inputSelector);
                $csrfJs = json_encode($csrfToken);
            $base = defined('BASE_URL') ? BASE_URL : '';
            $baseJs = json_encode($base);
                $html = <<<HTML
<style>
.img-picker-actions{ display:flex; gap:8px; margin-top:6px; align-items:center }
.img-picker-thumb{ display:none; width: 140px; height: auto; border:1px solid #eee; border-radius:4px }
.img-picker-btn{ padding:6px 10px; border:1px solid #ddd; background:#fff; border-radius:6px; cursor:pointer }
</style>
<div class="img-picker-actions">
    <button type="button" class="img-picker-btn" id="imgPickBtn">Tải ảnh lên…</button>
    <img id="imgPreview" class="img-picker-thumb" alt="preview" />
    <input type="file" id="imgFile" accept="image/*" style="display:none" />
</div>
<script>
(function(){
    var btn = document.getElementById('imgPickBtn');
    var file = document.getElementById('imgFile');
    var prev = document.getElementById('imgPreview');
    var input = document.querySelector($selectorJs);
    if(!btn || !file || !input) return;
    btn.addEventListener('click', function(){ file.click(); });
    file.addEventListener('change', function(){
        var f = file.files && file.files[0]; if(!f) return;
        if (f.size > 2*1024*1024) { alert('Kích thước ảnh > 2MB'); return; }
        var form = new FormData(); form.append('csrf', $csrfJs); form.append('image', f);
        btn.disabled = true; btn.textContent = 'Đang tải…';
    fetch(($baseJs||'') + '/admin/upload_image.php', { method:'POST', body: form })
            .then(async function(res){ var data = await res.json(); return { ok: res.ok, data: data }; })
            .then(function(r){
                if(!r.ok || !r.data || !r.data.ok || !r.data.url){ alert(r.data && r.data.error || 'Upload thất bại'); return; }
                input.value = r.data.url; prev.src = r.data.url; prev.style.display = 'block';
            })
            .catch(function(){ alert('Lỗi mạng khi tải ảnh'); })
            .finally(function(){ btn.disabled = false; btn.textContent = 'Tải ảnh lên…'; file.value = ''; });
    });
})();
</script>
HTML;
                return $html;
        }

                // Render a minimal image selector bound to a hidden input: only shows
                // - when empty: an Upload button
                // - when set: the image preview and a Delete button
                public static function renderImageSelector($inputSelector, $csrfToken) {
                        $selectorJs = json_encode($inputSelector);
                        $csrfJs = json_encode($csrfToken);
                    $base = defined('BASE_URL') ? BASE_URL : '';
                    $baseJs = json_encode($base);
                        // unique suffix for DOM ids based on selector
                        $suffix = substr(md5($inputSelector), 0, 8);
                        $btnId = 'imgUpBtn_' . $suffix;
                        $fileId = 'imgFile_' . $suffix;
                        $imgId = 'imgPrev_' . $suffix;
                        $delId = 'imgDel_' . $suffix;
                        $wrapId = 'imgWrap_' . $suffix;
                        $html = <<<HTML
        <style>
        #$wrapId{ display:flex; flex-direction:column; gap:8px; margin-top:6px }
        #$imgId{ max-width: 260px; height:auto; border:1px solid #eee; border-radius:6px; display:none }
        #$btnId, #$delId{ width:max-content; padding:6px 10px; border:1px solid #ddd; background:#fff; border-radius:6px; cursor:pointer }
        #$delId{ display:none; color:#a00; border-color:#f0c0c0; }
        </style>
        <div id="$wrapId">
            <button type="button" id="$btnId">Tải ảnh lên…</button>
            <img id="$imgId" alt="preview" />
            <button type="button" id="$delId">Xóa ảnh</button>
            <input type="file" id="$fileId" accept="image/*" style="display:none" />
        </div>
        <script>
        (function(){
            var input = document.querySelector($selectorJs);
            var btn = document.getElementById('$btnId');
            var file = document.getElementById('$fileId');
            var img = document.getElementById('$imgId');
            var del = document.getElementById('$delId');
            if(!input || !btn || !file || !img || !del) return;
            function refresh(){
                var url = input.value && input.value.trim();
                if(url){ img.src = url; img.style.display = 'block'; del.style.display = 'inline-block'; btn.style.display = 'none'; }
                else { img.style.display = 'none'; del.style.display = 'none'; btn.style.display = 'inline-block'; }
            }
            refresh();
            btn.addEventListener('click', function(){ file.click(); });
            file.addEventListener('change', function(){
                var f = file.files && file.files[0]; if(!f) return;
                if (f.size > 2*1024*1024) { alert('Kích thước ảnh > 2MB'); return; }
                var form = new FormData(); form.append('csrf', $csrfJs); form.append('image', f);
                btn.disabled = true; btn.textContent = 'Đang tải…';
                fetch(($baseJs||'') + '/admin/upload_image.php', { method:'POST', body: form })
                    .then(async function(res){ var data = await res.json(); return { ok: res.ok, data: data }; })
                    .then(function(r){
                        if(!r.ok || !r.data || !r.data.ok || !r.data.url){ alert(r.data && r.data.error || 'Upload thất bại'); return; }
                        input.value = r.data.url; refresh();
                    })
                    .catch(function(){ alert('Lỗi mạng khi tải ảnh'); })
                    .finally(function(){ btn.disabled = false; btn.textContent = 'Tải ảnh lên…'; file.value = ''; });
            });
            del.addEventListener('click', function(){
                // just clear field; deletion of old file will be handled on save (server checks references)
                input.value = ''; refresh();
            });
        })();
        </script>
        HTML;
                        return $html;
                }
}
?>