document.addEventListener("DOMContentLoaded", function () {
    const timePeriodSelect = document.getElementById("timePeriod");
    const salesChartCanvas = document.getElementById("salesChart").getContext("2d");

    let salesChart;

    // Fetch sales data and update chart
    function fetchSalesData(period) {
        fetch(`fetch_sales_data.php?period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }

                const labels = data.map(item => item.period);
                const sales = data.map(item => item.total_sales);

                updateChart(labels, sales, period);
            })
            .catch(error => console.error("Error fetching sales data:", error));
    }

    // Initialize or update the chart
    function updateChart(labels, sales, period) {
        if (salesChart) {
            salesChart.destroy();
        }

        salesChart = new Chart(salesChartCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: `Sales (${period.charAt(0).toUpperCase() + period.slice(1)})`,
                    data: sales,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Event listener for dropdown change
    timePeriodSelect.addEventListener("change", function () {
        fetchSalesData(this.value);
    });

    // Initial chart load
    fetchSalesData(timePeriodSelect.value);
});
