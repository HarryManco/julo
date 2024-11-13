document.addEventListener("DOMContentLoaded", function () {
    function closeNotification() {
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }

    setTimeout(closeNotification, 5000);

    document.getElementById("vehicle_type").addEventListener("change", updatePriceAndDuration);
    document.getElementById("service_type").addEventListener("change", updatePriceAndDuration);
    document.getElementById("slot").addEventListener("change", fetchAvailableTimes);
    document.getElementById("reservation_date").addEventListener("change", fetchAvailableTimes);

    function updatePriceAndDuration() {
        const vehicleSelect = document.getElementById("vehicle_type");
        const serviceSelect = document.getElementById("service_type");
        const priceField = document.getElementById("price");
        const paidFeeField = document.getElementById("paid_fee");
        const remainingFeeField = document.getElementById("remaining_fee");
    
        const vehicleId = vehicleSelect.value;
        const serviceId = serviceSelect.value;
    
        if (vehicleId && serviceId) {
            fetch(`get_service_price.php?vehicle_id=${vehicleId}&service_id=${serviceId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.price && data.duration) {
                        const price = parseFloat(data.price);
                        const paidFee = parseFloat(paidFeeField.value);
    
                        priceField.value = price.toFixed(2);
                        remainingFeeField.value = (price - paidFee).toFixed(2);
    
                        serviceSelect.setAttribute("data-duration", data.duration);

                        // Fetch available times after updating the duration
                        fetchAvailableTimes();
                    } else {
                        priceField.value = "N/A";
                        remainingFeeField.value = "N/A";
                    }
                })
                .catch(error => console.error("Error fetching service price:", error));
        } else {
            priceField.value = "";
            remainingFeeField.value = "";
        }
    }

    function fetchAvailableTimes() {
        const selectedDate = document.getElementById("reservation_date").value;
        const slot = document.getElementById("slot").value;
        const serviceDuration = document.getElementById("service_type").getAttribute("data-duration");
        const timeSelect = document.getElementById("reservation_time");
    
        if (selectedDate && slot && serviceDuration) {
            fetch(`get_available_times.php?date=${selectedDate}&slot=${slot}&duration=${serviceDuration}`)
                .then(response => response.json())
                .then(data => {
                    // Clear existing options
                    timeSelect.innerHTML = "<option value=''>--Select Time--</option>";

                    if (data.length > 0) {
                        // Populate the time select with available times
                        data.forEach(time => {
                            const option = document.createElement("option");
                            option.value = time;
                            option.textContent = time;
                            timeSelect.appendChild(option);
                        });

                        // Automatically select the first available time
                        timeSelect.value = data[0];
                        calculateEndTime(); // Update the end time based on the first available time
                    } else {
                        const option = document.createElement("option");
                        option.value = "";
                        option.textContent = "No available times";
                        timeSelect.appendChild(option);
                    }
                })
                .catch(error => console.error("Error fetching available times:", error));
        } else {
            // Clear time options if no valid date, slot, or service selected
            timeSelect.innerHTML = "<option value=''>--Select Time--</option>";
        }
    }

    function format12HourTime(hours, minutes) {
        const period = hours >= 12 ? "PM" : "AM";
        hours = hours % 12 || 12; // Convert to 12-hour format
        minutes = minutes.toString().padStart(2, '0');
        return `${hours}:${minutes} ${period}`;
    }

    function calculateEndTime() {
        const startTime = document.getElementById("reservation_time").value;
        const endTimeField = document.getElementById("end_time");
        const serviceDuration = parseInt(document.getElementById("service_type").getAttribute("data-duration"), 10);
    
        if (startTime && serviceDuration) {
            const [startHours, startMinutes] = startTime.split(":").map(Number);
            const endDate = new Date();
            endDate.setHours(startHours, startMinutes + serviceDuration, 0);
    
            const endHours = endDate.getHours().toString().padStart(2, '0');
            const endMinutes = endDate.getMinutes().toString().padStart(2, '0');
            endTimeField.value = `${endHours}:${endMinutes}`;
        } else {
            endTimeField.value = "N/A";
        }
    }
});
