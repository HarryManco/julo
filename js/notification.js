document.addEventListener("DOMContentLoaded", function () {
    const notificationBell = document.getElementById("notificationBell");
    const notificationDropdown = document.getElementById("notificationDropdown");
    const notificationCount = document.getElementById("notificationCount");
    const notificationBody = document.getElementById("notificationBody");
    let unreadCleared = false; // Track if unread notifications are cleared

    // Toggle the notification dropdown
    notificationBell.addEventListener("click", () => {
        const isDropdownOpen = notificationDropdown.style.display === "block";

        if (isDropdownOpen) {
            notificationDropdown.style.display = "none";
        } else {
            notificationDropdown.style.display = "block";

            if (!unreadCleared) {
                markNotificationsAsRead();
            }
        }
    });

    // Fetch notifications
    function fetchNotifications() {
        fetch("fetch_notifications.php")
            .then((response) => response.json())
            .then((data) => {
                if (data.notifications && data.notifications.length > 0) {
                    const unreadCount = data.notifications.filter((n) => n.status === "unread").length;

                    // Update the notification count
                    if (unreadCount > 0) {
                        notificationCount.style.display = "inline";
                        notificationCount.textContent = unreadCount;
                    } else {
                        notificationCount.style.display = "none";
                    }

                    // Populate the notifications
                    notificationBody.innerHTML = ""; // Clear the dropdown
                    data.notifications.forEach((notification) => {
                        const notificationItem = document.createElement("p");
                        notificationItem.textContent = notification.message;
                        notificationItem.style.color = notification.status === "unread" ? "blue" : "black";
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
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // After marking as read, refresh the notifications
                    fetchNotifications();
                    unreadCleared = true; // Ensure unread are cleared only once
                } else {
                    console.error("Error marking notifications as read:", data.error);
                }
            });
    }

    // Fetch notifications on page load
    fetchNotifications();

    // Refresh notifications every 30 seconds
    setInterval(fetchNotifications, 30000); // Refresh every 30 seconds
});
