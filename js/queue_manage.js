document.addEventListener("DOMContentLoaded", function () {
    const queueTableBody = document.getElementById("queueTable");
    const notificationElement = document.getElementById("notification");

    function showNotification(message, type = "success") {
        notificationElement.textContent = message;
        notificationElement.className = `notification ${type}`;
        notificationElement.style.display = "block";

        setTimeout(() => {
            notificationElement.style.display = "none";
        }, 5000);
    }

    function refreshQueueTable() {
        fetch("manage_queue.php", {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then((response) => response.json())
            .then((queues) => {
                queueTableBody.innerHTML = "";

                if (queues.length > 0) {
                    queues.forEach((row) => {
                        const tr = document.createElement("tr");
                        tr.innerHTML = `
                            <td>${row.queue_id}</td>
                            <td>${row.customer_name || "Unknown"}</td>
                            <td>${row.queue_status}</td>
                            <td>${row.slot || "N/A"}</td>
                            <td>
                                <select class="queue-status" data-id="${row.queue_id}">
                                    <option value="Waiting" ${row.queue_status === "Waiting" ? "selected" : ""}>Waiting</option>
                                    <option value="Serving" ${row.queue_status === "Serving" ? "selected" : ""}>Serving</option>
                                    <option value="Finished" ${row.queue_status === "Finished" ? "selected" : ""}>Finished</option>
                                </select>
                                <button class="update-btn" data-id="${row.queue_id}">Update</button>
                            </td>
                        `;
                        queueTableBody.appendChild(tr);
                    });
                } else {
                    queueTableBody.innerHTML = "<tr><td colspan='5'>No queues found for today.</td></tr>";
                }
            })
            .catch((error) => showNotification("Error fetching queue data.", "error"));
    }

    document.addEventListener("click", function (e) {
        if (e.target && e.target.classList.contains("update-btn")) {
            const queueId = e.target.getAttribute("data-id");
            const statusSelect = document.querySelector(`.queue-status[data-id="${queueId}"]`);
            const status = statusSelect.value;

            fetch("manage_queue.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: `queue_id=${queueId}&queue_status=${status}`
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        showNotification(data.message, "success");
                        refreshQueueTable();
                    } else {
                        showNotification(data.message, "error");
                    }
                })
                .catch((error) => showNotification("Error updating queue.", "error"));
        }
    });

    refreshQueueTable(); 
    setInterval(refreshQueueTable, 10000); 
});
