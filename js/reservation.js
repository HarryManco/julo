document.addEventListener("DOMContentLoaded", function () {
    const reservationForm = document.getElementById("reservationForm");

    // Close notification after a few seconds
    function closeNotification() {
        const notification = document.querySelector('.notification');
        if (notification) {
            notification.style.display = 'none';
        }
    }

    setTimeout(closeNotification, 5000);

    // Event listeners for form fields
    document.getElementById("vehicle_type").addEventListener("change", updatePriceAndDuration);
    document.getElementById("service_type").addEventListener("change", updatePriceAndDuration);
    document.getElementById("slot").addEventListener("change", fetchAvailableTimes);
    document.getElementById("reservation_date").addEventListener("change", fetchAvailableTimes);
    document.getElementById("reservation_time").addEventListener("change", calculateEndTime);

    // Populate the time slots
    populateTimeSlots();

    function populateTimeSlots() {
        const timeSelect = document.getElementById("reservation_time");
        const startTime = 7; // 7:00 AM
        const endTime = 19; // 7:00 PM
        const interval = 30; // 30-minute intervals
        const now = new Date();
    
        // Current date and time
        const currentDate = now.toISOString().split("T")[0];
        const currentHours = now.getHours();
        const currentMinutes = now.getMinutes();
    
        // Clear existing options
        timeSelect.innerHTML = "<option value=''>--Select Time--</option>";
    
        for (let hour = startTime; hour < endTime; hour++) {
            for (let minute = 0; minute < 60; minute += interval) {
                const optionTime = new Date();
                optionTime.setHours(hour, minute, 0);
    
                const optionValue = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                const period = hour >= 12 ? "PM" : "AM";
                const displayHour = hour > 12 ? hour - 12 : hour === 0 ? 12 : hour;
                const displayMinute = minute.toString().padStart(2, '0');
                const formattedTime = `${displayHour}:${displayMinute} ${period}`;
    
                // Exclude past times if the selected date is today
                if (selectedDate === currentDate) {
                    if (hour < currentHours || (hour === currentHours && minute <= currentMinutes)) {
                        continue;
                    }
                }
    
                // Create and append valid options
                const option = new Option(formattedTime, optionValue);
                timeSelect.add(option);
            }
        }
    }    

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

                        // Save the duration to the service dropdown
                        serviceSelect.setAttribute("data-duration", data.duration);

                        // Fetch available times after updating duration
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
                        data.forEach(time => {
                            const option = new Option(time, time);
                            timeSelect.add(option);
                        });
                    } else {
                        const option = new Option("No available times", "");
                        timeSelect.add(option);
                    }
                })
                .catch(error => console.error("Error fetching available times:", error));
        } else {
            timeSelect.innerHTML = "<option value=''>--Select Time--</option>";
        }
    }
    
    function calculateEndTime() {
        const startTime = document.getElementById("reservation_time").value;
        const endTimeField = document.getElementById("end_time");
        const serviceDuration = parseInt(document.getElementById("service_type").getAttribute("data-duration"), 10);

        if (startTime && serviceDuration) {
            const [startHours, startMinutes] = startTime.split(":").map(Number);
            const endDate = new Date();
            endDate.setHours(startHours, startMinutes + serviceDuration, 0);

            const endHours = endDate.getHours();
            const endMinutes = endDate.getMinutes();
            const formattedEndTime = format12HourTime(endHours, endMinutes);

            endTimeField.value = formattedEndTime;
        } else {
            endTimeField.value = "N/A";
        }
    }

    function format12HourTime(hours, minutes) {
        const period = hours >= 12 ? "PM" : "AM";
        const formattedHours = hours % 12 || 12;
        const formattedMinutes = minutes.toString().padStart(2, '0');
        return `${formattedHours}:${formattedMinutes} ${period}`;
    }
});
