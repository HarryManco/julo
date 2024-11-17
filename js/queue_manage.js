document.addEventListener("DOMContentLoaded", function () {
    const queueTableBody = document.getElementById("queueTable");

    // Periodically refresh queue table
    setInterval(refreshQueueTable, 10000); // Refresh every 10 seconds

    function refreshQueueTable() {
        fetch("manage_queue.php")
            .then((response) => response.json())
            .then((queues) => {
                queueTableBody.innerHTML = ""; // Clear table body

                queues.forEach((row) => {
                    const tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${row.queue_id}</td>
                        <td>${row.customer_type}</td>
                        <td>${row.queue_status}</td>
                        <td>${row.assigned_slot || "N/A"}</td>
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
            })
            .catch((error) => console.error("Error refreshing queue table:", error));
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
                },
                body: `queue_id=${queueId}&queue_status=${status}`,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        alert(data.message);
                        refreshQueueTable(); // Refresh the table after update
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch((error) => console.error("Error updating queue:", error));
        }
    });

    // Initial load
    refreshQueueTable();
});
