function initializeCharts() {
    // Sector Allocation Pie Chart
    const sectorCtx = document.getElementById('sectorChart');
    if (sectorCtx) {
        new Chart(sectorCtx, {
            type: 'pie',
            data: {
                labels: ['Education', 'Health', 'Infrastructure', 'Security', 'Agriculture', 'Others'],
                datasets: [{
                    data: [25, 15, 20, 12, 10, 18],
                    backgroundColor: [
                        '#0b9d3f',
                        '#1d4ed8',
                        '#ff6b35',
                        '#6c757d',
                        '#28a745',
                        '#6610f2'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1200, // Smooth entrance
                    easing: 'easeOutQuart'
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.raw}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Expenditure Trend Bar Chart
    const expenditureCtx = document.getElementById('expenditureChart');
    if (expenditureCtx) {
        new Chart(expenditureCtx, {
            type: 'bar',
            data: {
                labels: ['2019', '2020', '2021', '2022', '2023'],
                datasets: [{
                    label: 'Budget Expenditure (KSh Billion)',
                    data: [2100, 2300, 2500, 2800, 3300],
                    backgroundColor: 'rgba(11, 157, 63, 0.7)',
                    borderColor: 'rgba(11, 157, 63, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    delay: 300 // Staggered load for better UX
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'KSh Billion'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Fiscal Year'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `KSh ${context.raw} billion`;
                            }
                        }
                    }
                }
            }
        });
    }
}
