<?php
include 'db_connect.php'; // Include database connection file

// Fetch transactions with LEFT JOIN to handle walk-ins without users
$transactions_query = "SELECT t.transaction_id, t.amount, t.payment_method, t.transaction_type, t.payment_type, t.created_at, u.username AS customer_name 
                       FROM carwash_transactions t
                       LEFT JOIN users u ON t.customer_id = u.id
                       ORDER BY t.created_at DESC";
$result = $conn->query($transactions_query);
$transactions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
}
?>
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carwash Transactions</title>
    <link rel="stylesheet" href="css/carwash_transactions.css">
</head>
<body>

<div class="container">
    <h2>Carwash Transaction History</h2>

    <!-- Filter Section -->
    <div class="filter-container">
        <label for="transactionTypeFilter">Transaction Type:</label>
        <select id="transactionTypeFilter" onchange="filterTransactions()">
            <option value="All">All</option>
            <option value="Reservation">Reservation</option>
            <option value="Walk-in">Walk-in</option>
        </select>

        <label for="paymentTypeFilter">Payment Type:</label>
        <select id="paymentTypeFilter" onchange="filterTransactions()">
            <option value="All">All</option>
            <option value="Reservation Fee">Reservation Fee</option>
            <option value="Remaining Fee">Remaining Fee</option>
            <option value="Full Payment">Full Payment</option>
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
                <th>Payment Type</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody id="transactionTableBody">
            <?php foreach ($transactions as $transaction): ?>
                <tr>
                    <td><?= $transaction['transaction_id'] ?></td>
                    <td><?= $transaction['customer_name'] ?? 'Guest' ?></td>
                    <td><?= $transaction['amount'] ?></td>
                    <td><?= $transaction['payment_method'] ?></td>
                    <td><?= $transaction['transaction_type'] ?></td>
                    <td><?= $transaction['payment_type'] ?></td>
                    <td><?= date("Y-m-d H:i:s", strtotime($transaction['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="js/carwash_transactions.js"></script>
</body>
</html>
