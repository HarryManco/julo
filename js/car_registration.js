$(document).ready(function () {
    $('#car_model').select2({
        placeholder: "Select a car model", // Placeholder text
        allowClear: true, // Add a clear option
        minimumResultsForSearch: 5, // Show search box after 5 options
        width: '100%' // Match the width of the container
    });
});

// Real-time plate number validation
function validatePlateNumber(input) {
    const plateFormat = /^[A-Z]{3} \d{3}$/;
    const value = input.value.toUpperCase(); // Automatically convert input to uppercase

    // Show error if the format doesn't match
    if (!plateFormat.test(value) && value.length > 0) {
        input.setCustomValidity("Plate number must be in the format 'AAA 111'");
    } else {
        input.setCustomValidity(""); // Clear the error if format is correct
    }
    input.value = value; // Update the input value in uppercase
}
