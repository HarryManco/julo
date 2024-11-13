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

                    makeReservationBtn.style.display = "block"; // Show the "Make Reservation" button
                });
            }

            datesContainer.appendChild(dateElement);
        }
    }

    function makeReservation() {
        if (selectedDate) {
            // Format date as YYYY-MM-DD without time information
            const year = selectedDate.getFullYear();
            const month = String(selectedDate.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
            const day = String(selectedDate.getDate()).padStart(2, '0');
            const formattedDate = `${year}-${month}-${day}`;
    
            // Redirect to reservation.php with the selected date
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
