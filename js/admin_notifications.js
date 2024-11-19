document.addEventListener("DOMContentLoaded", function () {
    const notificationsList = document.getElementById("notifications-list");
    const noNotificationsMessage = document.getElementById("no-notifications");

    // Function to fetch notifications
    function fetchNotifications() {
        fetch("fetch_notifications.php?admin=true")
            .then(response => response.json())
            .then(data => {
                if (data.notifications && data.notifications.length > 0) {
                    notificationsList.innerHTML = "";
                    noNotificationsMessage.style.display = "none";

                    data.notifications.forEach(notification => {
                        const li = document.createElement("li");
                        li.className = `notification ${notification.status === 'unread' ? 'unread' : 'read'}`;
                        li.innerHTML = `
                            <p>${notification.message}</p>
                            <span class="timestamp">${formatTime(notification.created_at)}</span>
                            ${notification.status === 'unread' ? `
                                <form method="POST" class="mark-as-read-form" action="mark_as_read.php">
                                    <input type="hidden" name="notification_id" value="${notification.id}">
                                    <button type="submit" class="mark-as-read-btn">Mark as Read</button>
                                </form>` : ''
                            }
                        `;
                        notificationsList.appendChild(li);
                    });
                } else {
                    notificationsList.innerHTML = "";
                    noNotificationsMessage.style.display = "block";
                }
            })
            .catch(error => console.error("Error fetching notifications:", error));
    }

    // Helper function to format time
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        const hours = date.getHours() % 12 || 12;
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const period = date.getHours() >= 12 ? "PM" : "AM";
        return `${hours}:${minutes} ${period}`;
    }

    // Fetch notifications every 10 seconds
    fetchNotifications();
    setInterval(fetchNotifications, 10000);

    // Event listener for marking notifications as read
    document.addEventListener("click", function (e) {
        if (e.target && e.target.classList.contains("mark-as-read-btn")) {
            e.preventDefault();
            const form = e.target.closest("form");
            const notificationId = form.querySelector("input[name='notification_id']").value;

            fetch("mark_as_read.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `notification_id=${notificationId}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        fetchNotifications(); // Refresh notifications
                    } else {
                        console.error("Error marking notification as read:", data.error);
                    }
                })
                .catch(error => console.error("Error:", error));
        }
    });
});
