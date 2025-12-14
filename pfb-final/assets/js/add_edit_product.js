document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('product_image');
    const imagePreviewContainer = document.querySelector('.current-image-preview');
    const imagePreviewImg = document.getElementById('image-preview-img');
    const imageHint = document.getElementById('image-hint');
    if (imagePreviewImg && imagePreviewImg.getAttribute('src') === '#') {
        imagePreviewContainer.style.display = 'none';
    }

    if (imageInput) {
        imageInput.addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    imagePreviewContainer.style.display = 'block';
                    imagePreviewImg.src = e.target.result;
                    imagePreviewImg.style.display = 'block';
                    if (imageHint) {
                         imageHint.textContent = 'New image selected. This will replace the current image.';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (imagePreviewImg.src === '#') {
                     imagePreviewContainer.style.display = 'none';
                }
                if (imageHint) {
                    imageHint.textContent = 'Leave blank to keep current image.';
                }
            }
        });
    }
});