import tinymce from 'tinymce/tinymce';

// Importuj theme a pluginy
import 'tinymce/themes/silver';
import 'tinymce/icons/default';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/table';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/wordcount';

document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: 'textarea.tinymce-editor',
        height: 400,
        menubar: false,
        plugins: [
            'lists', 'link', 'image', 'table', 'code', 'fullscreen', 'paste', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | ' +
            'alignleft aligncenter alignright alignjustify | ' +
            'bullist numlist outdent indent | link image table | code fullscreen',
        content_css: '/build/app.css', // nebo cesta k tvému CSS
        language: 'cs', // nebo dynamicky podle locale
        paste_as_text: true,
        image_advtab: true,

        // PrestaShop-like konfigurace
        toolbar_mode: 'sliding',
        branding: false,
        promotion: false,

        // Validní HTML elementy
        valid_elements: 'p,br,strong/b,em/i,u,strike,a[href|target|title],ul,ol,li,' +
            'h1,h2,h3,h4,h5,h6,table,thead,tbody,tr,th,td,img[src|alt|width|height],' +
            'div[class|style],span[class|style]',

        // Upload obrázků (pokud potřebuješ)
        images_upload_handler: function (blobInfo, success, failure) {
            let formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            fetch('/admin/upload-image', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(result => {
                    success(result.location);
                })
                .catch(() => {
                    failure('Upload failed');
                });
        }
    });
});
