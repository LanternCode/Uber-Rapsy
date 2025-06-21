const input = document.getElementById('thumbnail_input');
const previewImage = document.getElementById('previewImage');
const errorMsg = document.getElementById('errorMsg');

input.addEventListener('change', () => {
    const file = input.files[0];
    errorMsg.textContent = '';
    previewImage.src = '';

    if (!file) return;

    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    const maxSize = 10 * 1024 * 1024; // 10MB

    //Check file type
    if (!validTypes.includes(file.type)) {
        errorMsg.textContent = 'Invalid file type.';
        input.value = '';
        return;
    }

    //Check file size
    if (file.size > maxSize) {
        errorMsg.textContent = 'File exceeds the 10MB size limit.';
        input.value = '';
        return;
    }

    const img = new Image();
    const reader = new FileReader();

    reader.onload = function (e) {
        img.src = e.target.result;
    };

    img.onload = function () {
        const { width, height } = img;

        // Check image dimensions
        if (width < 320 || height < 180 || width > 3840 || height > 2160) {
            errorMsg.textContent = 'Rozdzielczość miniaturki musi wynosić minimum 320x180 pikseli i maksimum 3840x2160 pikseli.';
            input.value = '';
            return;
        }

        previewImage.src = img.src;
    };

    reader.readAsDataURL(file);
});

document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.querySelector('input[name="songTitle"]');
    const authorInput = document.querySelector('input[name="songAuthor"]');
    const yearInput = document.querySelector('input[name="songReleaseYear"]');
    const thumbnailInput = document.querySelector('input[name="songThumbnailLink"]');

    const previewTitle = document.querySelector('.song-title');
    const previewAuthors = document.querySelector('.song-authors');
    const previewYear = document.querySelector('.song-year');
    const thumbnailPreview = document.getElementById('previewImage');

    function updateTitle() {
        const title = titleInput.value.trim();
        previewTitle.textContent = title || 'Tytuł';
    }

    function updateAuthors() {
        const author = authorInput.value.trim();
        previewAuthors.textContent = author || 'Autorzy';
    }

    function updateYear() {
        const year = yearInput.value.trim();
        previewYear.textContent = year ? `(${year})` : '(Rok Wydania)';
    }

    function updateThumbnail() {
        const link = thumbnailInput.value.trim();
        if (!link) {
            thumbnailPreview.src = 'default';
            return;
        }

        //If the link doesn't point to a valid image, use the default image
        thumbnailPreview.src = link;
        thumbnailPreview.onerror = () => {
            thumbnailPreview.src = 'thumbnails/default.png';
        };
    }

    titleInput.addEventListener('input', updateTitle);
    authorInput.addEventListener('input', updateAuthors);
    yearInput.addEventListener('input', updateYear);
    thumbnailInput.addEventListener('input', updateThumbnail);
});
