document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const productIdInput = form.querySelector('input[name="product_id"]');
            const productId = productIdInput ? productIdInput.value : 'N/A';
            const confirmation = confirm(`Are you sure you want to delete Product ID #${productId}? This product must not be associated with any existing transactions.`);

            if (confirmation) {
                form.submit();
            }
        });
    });
});