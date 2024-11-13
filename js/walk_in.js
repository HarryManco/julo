document.getElementById("vehicle_type").addEventListener("change", updatePriceAndDuration);
document.getElementById("service_type").addEventListener("change", updatePriceAndDuration);
document.getElementById("walkInForm").addEventListener("submit", handleFormSubmit);

function updatePriceAndDuration() {
    const vehicleSelect = document.getElementById("vehicle_type");
    const serviceSelect = document.getElementById("service_type");
    const priceField = document.getElementById("price");
    const durationField = document.getElementById("duration");

    const vehicleId = vehicleSelect.value;
    const serviceId = serviceSelect.value;

    console.log("Vehicle ID:", vehicleId, "Service ID:", serviceId);

    // Check if both vehicle and service are selected
    if (vehicleId && serviceId) {
        // Optional: show loading indicators
        priceField.value = "Loading...";
        durationField.value = "Loading...";

        fetch(`get_service_price.php?vehicle_id=${vehicleId}&service_id=${serviceId}`)
            .then(response => {
                console.log("Fetch response status:", response.status); // Log response status
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                console.log("Data received:", data); // Log data for debugging

                if (data && data.price && data.duration) {
                    // Populate the price and duration fields
                    priceField.value = parseFloat(data.price).toFixed(2); // Format price
                    durationField.value = parseInt(data.duration); // Set duration in minutes
                } else {
                    // Handle missing fields in the response
                    priceField.value = "0.00";
                    durationField.value = "0";
                    console.error("Invalid data structure:", data);
                }
            })
            .catch(error => {
                // Display fetch errors
                console.error("Error fetching service price and duration:", error);
                priceField.value = "0.00";
                durationField.value = "0";
                alert("Failed to retrieve service details. Please check your connection or try again.");
            });
    } else {
        // Reset fields if either vehicle or service is not selected
        priceField.value = "0.00";
        durationField.value = "0";
    }
}

function handleFormSubmit(event) {
    event.preventDefault(); // Prevent form from submitting normally

    // Collect form data
    const formData = new FormData(document.getElementById("walkInForm"));

    // Send form data to process_walk_in.php using fetch
    fetch("process_walk_in.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Display message based on response
        const notification = document.getElementById("notification");
        if (data.status === "success") {
            notification.classList.add("success");
            notification.classList.remove("error");
            notification.textContent = data.message;
            notification.style.display = "block";

            // Optionally: Clear the form after successful submission
            document.getElementById("walkInForm").reset();
        } else {
            notification.classList.add("error");
            notification.classList.remove("success");
            notification.textContent = data.message;
            notification.style.display = "block";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        const notification = document.getElementById("notification");
        notification.classList.add("error");
        notification.classList.remove("success");
        notification.textContent = "An error occurred. Please try again.";
        notification.style.display = "block";
    });
}
