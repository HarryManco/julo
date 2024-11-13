$(document).ready(function() {
    // Handle form submission via AJAX
    $("#car-size-form").on("submit", function(event) {
        event.preventDefault(); // Prevent the default form submission

        const carModel = $("#car_model").val();
        const carSize = $("#car_size").val();

        $.ajax({
            type: "POST",
            url: "manage_carsize.php",
            data: {
                car_model: carModel,
                car_size: carSize,
                ajax: true // Flag to indicate this is an AJAX request
            },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    // Display success message
                    $("#message").addClass("success").text(response.message).show();

                    // Append the new car model and size to the table
                    const newRow = `
                        <tr>
                            <td>${response.data.car_model}</td>
                            <td>${response.data.car_size.charAt(0).toUpperCase() + response.data.car_size.slice(1)}</td>
                            <td>${response.data.created_at}</td>
                            <td>${response.data.updated_at}</td>
                        </tr>
                    `;
                    $("#car-size-table tbody").append(newRow);

                    // Clear form inputs
                    $("#car_model").val("");
                    $("#car_size").val("small");
                } else {
                    // Display error message
                    $("#message").addClass("error").text(response.message).show();
                }

                // Hide the message after a few seconds
                setTimeout(function() {
                    $("#message").fadeOut();
                }, 5000);
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", error);
            }
        });
    });
});
