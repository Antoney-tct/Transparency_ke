document.addEventListener('DOMContentLoaded', function() {
    // Check if Chart.js is loaded
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not loaded. Please check the script inclusion.');
        return;
    }
    
    // Check if chart elements exist
    const categoryCtx = document.getElementById('categoryChart');
    const statusCtx = document.getElementById('statusChart');
    
    if (!categoryCtx || !statusCtx) {
        console.error('Chart canvas elements not found in the DOM');
        return;
    }
    
    try {
        // Initialize category chart
        const categoryChart = new Chart(categoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Infrastructure', 'Education', 'Healthcare', 'Environment', 'Transportation', 'Security', 'Other'],
                datasets: [{
                    data: [125, 98, 87, 76, 65, 54, 74],
                    backgroundColor: [
                        '#3498db', '#e74c3c', '#2ecc71', '#f1c40f', '#e67e22', '#9b59b6', '#95a5a6'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 15
                        }
                    }
                },
                cutout: '70%'
            }
        });
        
        // Initialize status chart
        const statusChart = new Chart(statusCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Pending', 'Under Review', 'In Progress', 'Implemented', 'Declined'],
                datasets: [{
                    label: 'Number of Feedback',
                    data: [42, 156, 87, 87, 207],
                    backgroundColor: [
                        '#f39c12', '#3498db', '#2ecc71', '#9b59b6', '#e74c3c'
                    ],
                    borderWidth: 0,
                    borderRadius: 5,
                    maxBarThickness: 25
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: false
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
});
