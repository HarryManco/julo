document.addEventListener("DOMContentLoaded", () => {
    const quantityInputs = document.querySelectorAll(".quantity-input");
    const totalPriceElement = document.getElementById("totalPrice");
    const checkoutButton = document.getElementById("checkoutButton");
    const paypalContainer = document.getElementById("paypal-button-container");

    quantityInputs.forEach(input => {
        input.addEventListener("input", function () {
            const price = parseFloat(this.getAttribute("data-price"));
            const quantity = parseInt(this.value) || 1; // Ensure quantity is at least 1
            const row = this.closest("tr");
            const itemTotalElement = row.querySelector(".item-total");
            const itemId = row.getAttribute("data-id");

            // Update item total
            const itemTotal = price * quantity;
            itemTotalElement.textContent = `PHP ${itemTotal.toFixed(2)}`;

            // Update overall total
            let total = 0;
            document.querySelectorAll(".item-total").forEach(el => {
                total += parseFloat(el.textContent.replace("PHP ", ""));
            });
            totalPriceElement.textContent = `PHP ${total.toFixed(2)}`;

            // Send AJAX request to update quantity in the database
            updateCartQuantity(itemId, quantity);
        });
    });

    function updateCartQuantity(itemId, quantity) {
        fetch("update_cart_quantity.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({ id: itemId, quantity }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                console.log("Quantity updated successfully.");
            } else {
                displayNotification(data.message || "Error updating cart.", "error");
            }
        })
        .catch(error => {
            console.error("Error updating cart:", error);
            displayNotification("An error occurred while updating the cart. Please try again.", "error");
        });
    }

    function displayNotification(message, type) {
        const notification = document.createElement("div");
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.insertBefore(notification, document.body.firstChild);
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Event listener for custom Checkout button
    checkoutButton.addEventListener("click", () => {
        // Show PayPal button container and render the PayPal button on first checkout click
        paypalContainer.style.display = "block";
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: parseFloat(totalPriceElement.textContent.replace("PHP ", "")).toFixed(2)
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    alert('Payment completed by ' + details.payer.name.given_name);
                    window.location.href = "order_success.php";  // Adjust as needed for the success page
                });
            }
        }).render('#paypal-button-container'); // Render PayPal button in the hidden container
    });
});
