<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar</title>
    <link rel="stylesheet" href="css/calendar.css">
</head>
<body>
<?php include 'header.php'; ?>
    <div class="container">
        <div class="calendar-container">
            <h2>Calendar</h2>
            <div class="month">
                <button id="prevMonth" class="nav-btn">&#8249;</button>
                <h3 id="month-year"></h3>
                <button id="nextMonth" class="nav-btn">&#8250;</button>
            </div>
            <div class="days">
                <div>Sunday</div>
                <div>Monday</div>
                <div>Tuesday</div>
                <div>Wednesday</div>
                <div>Thursday</div>
                <div>Friday</div>
                <div>Saturday</div>
            </div>
            <div class="dates" id="dates"></div>
        </div>

        <!-- Slot Availability Table -->
        <div class="availability-table" id="availabilityTable" style="display: none;">
            <table>
                <tr>
                    <th>SLOT 1</th>
                    <th>SLOT 2</th>
                    <th>SLOT 3</th>
                </tr>
                <tr>
                    <td>
                        <h4>Time Availability:</h4>
                        <div id="slot1Times"></div> <!-- Dynamic content for Slot 1 times -->
                    </td>
                    <td>
                        <h4>Time Availability:</h4>
                        <div id="slot2Times"></div> <!-- Dynamic content for Slot 2 times -->
                    </td>
                    <td>
                        <h4>Time Availability:</h4>
                        <div id="slot3Times"></div> <!-- Dynamic content for Slot 3 times -->
                    </td>
                </tr>
            </table>
        </div>

        <!-- "Make Reservation" Button, shown only after a date is selected -->
        <button id="makeReservationBtn" class="reserve-btn" style="display: none;" onclick="makeReservation()">Make Reservation</button>
    </div>

    <script src="js/calendar.js"></script>
</body>
</html>
