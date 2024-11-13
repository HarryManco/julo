document.addEventListener("DOMContentLoaded", function () {
    const updateButtons = document.querySelectorAll(".update-button");

    updateButtons.forEach(button => {
        button.addEventListener("click", function () {
            const form = button.closest(".update-form");
            const formData = new FormData(form);

            fetch("process_update_walk_in.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const notification = document.getElementById("notification");

                if (data.status === "success") {
                    notification.textContent = data.message;
                    notification.className = "notification success";
                } else {
                    notification.textContent = data.message || "An error occurred.";
                    notification.className = "notification error";
                }

                notification.style.display = "block";

                // Refresh the page or reload data here if necessary
                setTimeout(() => {
                    location.reload(); // Optional, reloads the page to show updated values
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
