<?php
// Tên file: classes/Editor.php
class Editor {
    public static function renderTinyMCE($selector, $csrfToken) {
        $selectorJs = json_encode($selector);
        $csrfJs = json_encode($csrfToken);
    $base = defined('BASE_URL') ? BASE_URL : '';
    $baseJs = json_encode($base);
        $apiKey = defined('TINYMCE_API_KEY') ? TINYMCE_API_KEY : '';
        if (!empty($apiKey)) {
            $src = 'https://cdn.tiny.cloud/1/' . htmlspecialchars($apiKey, ENT_QUOTES) . '/tinymce/6/tinymce.min.js';
        } else {
            $src = 'https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js';
        }
        $tinymce = <<<HTML
<script src="$src" referrerpolicy="origin"></script>
<script>
tinymce.init({
  selector: $selectorJs,
  height: 500,
  menubar: false,
  plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table code help wordcount',
  toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | removeformat | code',
  automatic_uploads: true,
  paste_data_images: true,
  images_file_types: 'jpg,jpeg,png,gif',
  convert_urls: false,
  images_upload_handler: function (blobInfo, success, failure, progress) {
    var form = new FormData();
    form.append('csrf', $csrfJs);
    form.append('image', blobInfo.blob(), blobInfo.filename());
    fetch(($baseJs||'') + '/admin/upload_image.php', { method: 'POST', body: form })
      .then(async res => { const data = await res.json(); return { ok: res.ok, data }; })
      .then(({ok, data}) => {
        if (!ok || !data || !data.ok || !data.url) { throw new Error((data && data.error) || 'Upload failed'); }
        success(data.url);
      })
      .catch(err => failure(err.message || 'Upload error'));
  }
});
</script>
HTML;
        if (!empty($apiKey)) return $tinymce;

        // Fallback: Quill (no API key required)
        $quill = <<<HTML
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<style>.ql-editor{min-height:380px}#__quill__{border:1px solid #ddd;border-radius:6px;margin-top:6px}</style>
<div id="__quill__"></div>
<script>
  (function(){
    var textarea = document.querySelector($selectorJs);
    var initial = textarea.value || '';
    var container = document.getElementById('__quill__');
    // Hide original textarea; we'll sync content back to it before submit
    if (textarea) { textarea.style.display = 'none'; }
    var toolbarOptions = [['bold','italic','underline'],[{ 'list': 'ordered'}, { 'list': 'bullet' }],[{ 'header': [1,2,3, false] }],['link','image'],[{ 'align': [] }],['clean']];
    var quill = new Quill(container, { theme: 'snow', modules: { toolbar: toolbarOptions } });
    quill.clipboard.dangerouslyPasteHTML(initial);
    // Image handler override to POST to our upload endpoint
    var toolbar = quill.getModule('toolbar');
    toolbar.addHandler('image', function(){
      var input = document.createElement('input');
      input.type = 'file'; input.accept = 'image/*';
      input.onchange = function(){
        var f = input.files[0]; if(!f) return;
        if(f.size > 2*1024*1024) { alert('Kích thước ảnh > 2MB'); return; }
        var form = new FormData();
        form.append('csrf', $csrfJs);
        form.append('image', f);
        fetch(($baseJs||'') + '/admin/upload_image.php', { method:'POST', body: form }).then(async function(res){
          var data = await res.json();
          if(!res.ok || !data.ok) { alert(data.error||'Upload thất bại'); return; }
          var range = quill.getSelection(true);
          quill.insertEmbed(range.index, 'image', data.url, 'user');
        }).catch(function(){ alert('Lỗi mạng khi tải ảnh'); });
      };
      input.click();
    });
    // Sync back to textarea on change
    quill.on('text-change', function(){ textarea.value = quill.root.innerHTML; });
    // Also ensure sync on form submit
    var form = textarea && textarea.form;
    if (form) { form.addEventListener('submit', function(){ textarea.value = quill.root.innerHTML; }); }
  })();
</script>
HTML;
        // Render both: TinyMCE (no-key) will show warning; we prefer Quill fallback without key.
        return $quill;
    }
}
?>