document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('order-modal');
    const modalContent = document.getElementById('order-details');
    const closeModal = document.querySelector('.close-btn');

    // Open modal and fetch order details
    document.querySelectorAll('.view-details-btn').forEach(button => {
        button.addEventListener('click', function () {
            const orderId = this.getAttribute('data-order-id');
            modal.style.display = 'flex';

            // Fetch order details via AJAX
            fetch(`fetch_order_details.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(data => {
                    modalContent.innerHTML = data;
                })
                .catch(error => {
                    modalContent.innerHTML = `<p>Error loading order details.</p>`;
                });
        });
    });

    // Close modal
    closeModal.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});
