
function initFeedbackCharts() {
    const ctx = document.getElementById('feedbackTrendChart').getContext('2d');
    const timeFilter = document.getElementById('timeFilter');
    let chart;

    function loadChartData(timeRange) {
        fetch(`get_analytics_data.php?range=${timeRange}`)
            .then(response => response.json())
            .then(data => {
                if (chart) chart.destroy();
                
                chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Feedback Received',
                            data: data.counts,
                            backgroundColor: 'rgba(11, 157, 63, 0.2)',
                            borderColor: 'rgba(11, 157, 63, 1)',
                            borderWidth: 2,
                            tension: 0.3
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            });
    }

    timeFilter.addEventListener('change', () => loadChartData(timeFilter.value));
    loadChartData(timeFilter.value); // Initial load
}