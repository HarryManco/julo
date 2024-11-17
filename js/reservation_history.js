// Show receipt modal
function showReceipt(details) {
    const modal = document.getElementById('receiptModal');
    document.getElementById('receiptDetails').innerHTML = details;
    modal.style.display = 'block';
}

// Close receipt modal
function closeModal() {
    document.getElementById('receiptModal').style.display = 'none';
}

// Function to fetch updated reservation data
function fetchReservationUpdates() {
    $.ajax({
        url: 'fetch_reservation_updates.php',
        method: 'POST',
        success: function (response) {
            const reservations = JSON.parse(response);

            // Update the table dynamically
            reservations.forEach(reservation => {
                // Update Paid Fee
                document.getElementById(`paid_fee_${reservation.reservation_id}`).innerText = reservation.paid_fee;

                // Update Reservation Status
                document.getElementById(`reservation_status_${reservation.reservation_id}`).innerText = reservation.reservation_status;
            });
        },
        error: function (error) {
            console.error('Error fetching reservation updates:', error);
        }
    });
}

// Fetch updates every 10 seconds
setInterval(fetchReservationUpdates, 10000);
