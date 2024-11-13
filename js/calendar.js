document.addEventListener("DOMContentLoaded", function() {
    const currentDate = new Date();
    let selectedDate = new Date(currentDate);

    const monthDisplay = document.getElementById("month-year");
    const datesContainer = document.getElementById("dates");
    const makeReservationBtn = document.getElementById("makeReservationBtn");

    function updateMonthDisplay() {
        const monthNames = ["January", "February", "March", "April", "May", "June", 
                            "July", "August", "September", "October", "November", "December"];
        monthDisplay.textContent = `${monthNames[selectedDate.getMonth()]} ${selectedDate.getFullYear()}`;
    }

    function renderCalendar() {
        updateMonthDisplay();
        datesContainer.innerHTML = "";

        const firstDayIndex = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1).getDay();
        const lastDay = new Date(selectedDate.getFullYear(), selectedDate.getMonth() + 1, 0).getDate();

        for (let i = 0; i < firstDayIndex; i++) {
            const emptyCell = document.createElement("div");
            emptyCell.classList.add("date-cell", "disabled");
            datesContainer.appendChild(emptyCell);
        }

        for (let day = 1; day <= lastDay; day++) {
            const dateElement = document.createElement("div");
            dateElement.textContent = day;
            dateElement.classList.add("date-cell");

            const cellDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), day);

            if (cellDate < currentDate.setHours(0, 0, 0, 0)) {
                dateElement.classList.add("disabled");
                dateElement.style.pointerEvents = "none";
            } else {
                dateElement.addEventListener("click", () => {
                    const previouslySelected = document.querySelector(".date-cell.selected");
                    if (previouslySelected) {
                        previouslySelected.classList.remove("selected");
                    }

                    dateElement.classList.add("selected");
                    selectedDate = cellDate;

                    console.log(`Selected date: ${formatDate(selectedDate)}`); // Debugging line
                    // Fetch availability for each slot on the selected date
                    fetchAvailabilityForSlots(formatDate(selectedDate));

                    // Show the "Make Reservation" button
                    makeReservationBtn.style.display = "block";
                });
            }

            datesContainer.appendChild(dateElement);
        }
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function fetchAvailabilityForSlots(date) {
        const slots = [1, 2, 3];
        const serviceDuration = 30; // Duration in minutes, adjust as needed

        slots.forEach(slot => {
            fetch(`get_available_times.php?date=${date}&slot=${slot}&duration=${serviceDuration}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Network response was not ok (${response.status})`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log(`Slot ${slot} availability for ${date}:`, data); // Debugging line
                    const slotTimesContainer = document.getElementById(`slot${slot}Times`);
                    if (data.length > 0) {
                        slotTimesContainer.innerHTML = data.map(time => `<div>${time}</div>`).join("");
                    } else {
                        slotTimesContainer.innerHTML = "No available times";
                    }
                })
                .catch(error => console.error("Error fetching available times:", error));
        });

        document.getElementById("availabilityTable").style.display = "block";
    }

    function makeReservation() {
        if (selectedDate) {
            const formattedDate = formatDate(selectedDate);
            window.location.href = `reservation.php?date=${formattedDate}`;
        }
    }

    document.getElementById("makeReservationBtn").addEventListener("click", makeReservation);

    document.getElementById("prevMonth").addEventListener("click", () => {
        selectedDate.setMonth(selectedDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById("nextMonth").addEventListener("click", () => {
        selectedDate.setMonth(selectedDate.getMonth() + 1);
        renderCalendar();
    });

    renderCalendar();
});
