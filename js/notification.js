document.addEventListener("DOMContentLoaded", function () {
    const notificationBell = document.getElementById("notificationBell");
    const notificationDropdown = document.getElementById("notificationDropdown");
    const notificationCount = document.getElementById("notificationCount");
    const notificationBody = document.getElementById("notificationBody");

    // Toggle the notification dropdown
    notificationBell.addEventListener("click", () => {
        notificationDropdown.style.display = notificationDropdown.style.display === "block" ? "none" : "block";

        // Mark notifications as read when opened
        if (notificationDropdown.style.display === "block") {
            markNotificationsAsRead();
        }
    });

    // Fetch notifications
    function fetchNotifications() {
        fetch("fetch_notifications.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.length > 0) {
                    notificationCount.style.display = "inline";
                    notificationCount.textContent = data.length;

                    notificationBody.innerHTML = ""; // Clear existing notifications
                    data.forEach((notification) => {
                        const notificationItem = document.createElement("p");
                        notificationItem.textContent = notification.message;
                        notificationBody.appendChild(notificationItem);
                    });
                } else {
                    notificationCount.style.display = "none";
                    notificationBody.innerHTML = "<p>No new notifications</p>";
                }
            })
            .catch((error) => console.error("Error fetching notifications:", error));
    }

    // Mark notifications as read
    function markNotificationsAsRead() {
        fetch("mark_notifications_read.php", { method: "POST" })
            .then(() => {
                notificationCount.textContent = "0";
                notificationCount.style.display = "none";
            });
    }

    // Fetch notifications on page load
    fetchNotifications();
});
