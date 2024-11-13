// carwash_transactions.js

function filterTransactions() {
    const transactionType = document.getElementById("transactionTypeFilter").value;
    const paymentType = document.getElementById("paymentTypeFilter").value;
    const rows = document.querySelectorAll("#transactionTableBody tr");

    rows.forEach(row => {
        const rowTransactionType = row.cells[4].textContent;
        const rowPaymentType = row.cells[5].textContent;

        if ((transactionType === "All" || rowTransactionType === transactionType) &&
            (paymentType === "All" || rowPaymentType === paymentType)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
