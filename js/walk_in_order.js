// Initialize the walk-in order functionality
document.addEventListener('DOMContentLoaded', () => {
    const orderItems = document.getElementById('order-items');
    const orderTotal = document.getElementById('order-total');
    const addToOrderButtons = document.querySelectorAll('.add-to-order');
    const form = document.getElementById('order-form');

    // Object to store the current order state
    let order = {};

    // Attach event listeners
    attachAddToOrderListeners(addToOrderButtons, order, updateOrderDisplay);

    // Form submission handler
    handleFormSubmission(form, order);

    // Automatically hide success message after 5 seconds
    hideNotification('success-message', 5000);
});

/**
 * Attach event listeners to "Add to Order" buttons
 */
function attachAddToOrderListeners(buttons, order, updateCallback) {
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const price = parseFloat(button.dataset.price);

            if (!order[id]) {
                order[id] = { name, price, quantity: 1 };
            } else {
                order[id].quantity++;
            }

            updateCallback(order);
        });
    });
}

/**
 * Update the order display in the DOM
 */
function updateOrderDisplay(order) {
    const orderItems = document.getElementById('order-items');
    const orderTotal = document.getElementById('order-total');

    orderItems.innerHTML = ''; // Clear existing items in the table
    let total = 0;

    for (const id in order) {
        const item = order[id];
        total += item.price * item.quantity;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.name}</td>
            <td>
                <button class="decrease" data-id="${id}">-</button>
                <span>${item.quantity}</span>
                <button class="increase" data-id="${id}">+</button>
            </td>
            <td>P${(item.price * item.quantity).toFixed(2)}</td>
        `;
        orderItems.appendChild(row);
    }

    orderTotal.textContent = `P${total.toFixed(2)}`;

    attachQuantityHandlers(order, updateOrderDisplay);
}

/**
 * Attach event listeners to "Increase" and "Decrease" quantity buttons
 */
function attachQuantityHandlers(order, updateCallback) {
    document.querySelectorAll('.increase').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            if (order[id]) {
                order[id].quantity++;
                updateCallback(order);
            }
        });
    });

    document.querySelectorAll('.decrease').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            if (order[id]) {
                if (order[id].quantity > 1) {
                    order[id].quantity--;
                } else {
                    delete order[id]; // Remove the item if quantity reaches 0
                }
                updateCallback(order);
            }
        });
    });
}

/**
 * Handle form submission and attach order data as hidden inputs
 */
function handleFormSubmission(form, order) {
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        Object.keys(order).forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `order_items[${id}]`;
            input.value = order[id].quantity;
            form.appendChild(input);
        });

        form.submit(); // Submit the form
    });
}

/**
 * Automatically hide notifications after a set time
 */
function hideNotification(elementId, timeout) {
    const notification = document.getElementById(elementId);
    if (notification) {
        setTimeout(() => {
            notification.style.display = 'none';
        }, timeout);
    }
}
