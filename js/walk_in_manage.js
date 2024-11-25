document.addEventListener("DOMContentLoaded", function () {
    const updateButtons = document.querySelectorAll(".update-button");

    updateButtons.forEach(button => {
        button.addEventListener("click", function () {
            const form = button.closest(".update-form");
            const formData = new FormData(form);

            const walkInStatus = formData.get("walk_in_status");
            const paymentStatus = formData.get("payment_status");

            // Prevent updating to "Completed" if payment status is "Unpaid"
            if (walkInStatus === "Completed" && paymentStatus === "Unpaid") {
                const notification = document.getElementById("notification");
                notification.textContent = "Cannot mark as Completed when Payment Status is Unpaid.";
                notification.className = "notification error";
                notification.style.display = "block";
                return; // Stop further execution
            }

            fetch("process_update_walk_in.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    const notification = document.getElementById("notification");

                    if (data.success) {
                        notification.textContent = data.message;
                        notification.className = "notification success";
                    } else {
                        notification.textContent = data.message || "An error occurred.";
                        notification.className = "notification error";
                    }

                    notification.style.display = "block";

                    // Refresh the page or reload data here if necessary
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                })
                .catch(error => {
                    console.error("Error:", error);
                    const notification = document.getElementById("notification");
                    notification.textContent = "An error occurred. Please try again.";
                    notification.className = "notification error";
                    notification.style.display = "block";
                });
        });
    });
});
