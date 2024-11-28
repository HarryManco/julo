<?php
session_start();
include 'db_connect.php'; // Include database connection file

// Fetch transactions with LEFT JOIN to handle walk-ins without users
$transactions_query = "SELECT t.id AS transaction_id, t.amount, t.payment_method, t.transaction_type, t.created_at, u.username AS customer_name 
                       FROM cafe_transactions t
                       LEFT JOIN users u ON t.user_id = u.id
                       ORDER BY t.created_at DESC";
$result = $conn->query($transactions_query);
$transactions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}
?>
<?php include 'sidebar.php'; ?> <!-- Include sidebar for admin -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafe Transaction History</title>
    <link rel="stylesheet" href="css/cafe_transactions.css">
</head>
<body>

<div class="container">
    <h2>Cafe Transaction History</h2>

    <!-- Filter Section -->
    <div class="filter-container">
        <label for="transactionTypeFilter">Transaction Type:</label>
        <select id="transactionTypeFilter" onchange="filterTransactions()">
            <option value="All">All</option>
            <option value="Online Order">Online Order</option>
            <option value="Walk-In Order">Walk-In Order</option>
        </select>

        <label for="paymentTypeFilter">Payment Method:</label>
        <select id="paymentTypeFilter" onchange="filterTransactions()">
            <option value="All">All</option>
            <option value="PayPal">PayPal</option>
            <option value="Cash">Cash</option>
        </select>
    </div>

    <!-- Transactions Table -->
    <table class="transaction-table">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Customer</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Transaction Type</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody id="transactionTableBody">
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= $transaction['transaction_id'] ?></td>
                    <td><?= $transaction['customer_name'] ?? 'Guest' ?></td>
                    <td><?= number_format($transaction['amount'], 2) ?></td>
                    <td><?= $transaction['payment_method'] ?></td>
                    <td><?= $transaction['transaction_type'] ?></td>
                    <td><?= date("Y-m-d H:i:s", strtotime($transaction['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    // Filter transactions based on user selection
    function filterTransactions() {
        const transactionType = document.getElementById('transactionTypeFilter').value;
        const paymentType = document.getElementById('paymentTypeFilter').value;

        let rows = document.querySelectorAll('#transactionTableBody tr');

        rows.forEach(row => {
            let typeCell = row.cells[4].textContent;
            let paymentCell = row.cells[3].textContent;

            let showRow = true;

            if (transactionType !== 'All' && typeCell !== transactionType) {
                showRow = false;
            }

            if (paymentType !== 'All' && paymentCell !== paymentType) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });
    }
</script>

</body>
</html>
