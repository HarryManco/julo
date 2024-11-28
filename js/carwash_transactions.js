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