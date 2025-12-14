document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const vendorIdInput = form.querySelector('input[name="vendor_id"]');
            const vendorId = vendorIdInput ? vendorIdInput.value : 'N/A';
            const confirmation = confirm(`Are you sure you want to delete Vendor ID #${vendorId}? This action cannot be undone.`);

            if (confirmation) {
                form.submit();
            }
        });
    });
});