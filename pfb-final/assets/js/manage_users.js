document.addEventListener('DOMContentLoaded', function() {
    const deleteForms = document.querySelectorAll('.delete-form');

    deleteForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();
            const userIdInput = form.querySelector('input[name="user_id"]');
            const userId = userIdInput ? userIdInput.value : 'N/A';
            const confirmation = confirm(`Are you sure you want to delete User ID #${userId}? This action will permanently remove the user and their associated data (transactions, cart, etc.).`);

            if (confirmation) {
                form.submit();
            }
        });
    });
});