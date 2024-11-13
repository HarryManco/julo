function closeNotification() {
    const notification = document.querySelector('.notification');
    if (notification) {
        notification.style.display = 'none';
    }
}

// Automatically hide the notification after 5 seconds
document.addEventListener("DOMContentLoaded", function() {
    setTimeout(closeNotification, 5000); // 5000 milliseconds = 5 seconds
});

$(document).ready(function() {
    $('#servicePriceForm').on('submit', function(e) {
        e.preventDefault(); // Prevent form submission

        $.ajax({
            type: 'POST',
            url: 'add_service_price.php', // Point to the PHP file
            data: $(this).serialize(),
            success: function(response) {
                const result = JSON.parse(response);

                if (result.status === 'success') {
                    // Append the new row to the table
                    $('#servicePriceTableBody').append(`
                        <tr id="priceRow-${result.data.id}">
                            <td>${result.data.service_name}</td>
                            <td>${result.data.car_model}</td>
                            <td>P${parseFloat(result.data.price).toFixed(2)}</td>
                            <td>${result.data.duration}</td>
                            <td>
                                <a href="edit_service_price.php?id=${result.data.id}">Edit</a> |
                                <a href="delete_service_price.php?id=${result.data.id}" onclick="return confirm('Are you sure you want to delete this service price?')">Delete</a>
                            </td>
                        </tr>
                    `);

                    // Clear form fields
                    $('#service_id').val('0');
                    $('#car_model').val('');
                    $('#price').val('');
                    $('#duration').val('');
                } else {
                    alert(result.message);
                }
            },
            error: function() {
                alert('Failed to add service price.');
            }
        });
    });
});
