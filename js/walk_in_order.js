document.addEventListener('DOMContentLoaded', () => {
    const orderItems = document.getElementById('order-items');
    const orderTotal = document.getElementById('order-total');
    const addToOrderButtons = document.querySelectorAll('.add-to-order');

    let order = {};

    addToOrderButtons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.dataset.id;
            const name = button.dataset.name;
            const price = parseFloat(button.dataset.price);

            if (!order[id]) {
                order[id] = { name, price, quantity: 1 };
            } else {
                order[id].quantity += 1;
            }

            updateOrder();
        });
    });

    const updateOrder = () => {
        orderItems.innerHTML = '';
        let total = 0;

        for (const id in order) {
            const item = order[id];
            total += item.price * item.quantity;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${item.name}</td>
                <td>
                    <button class="decrease" data-id="${id}">▲</button>
                    ${item.quantity}
                    <button class="increase" data-id="${id}">▼</button>
                </td>
                <td>P${(item.price * item.quantity).toFixed(2)}</td>
            `;
            orderItems.appendChild(row);
        }

        orderTotal.textContent = `P${total.toFixed(2)}`;
    };
});
