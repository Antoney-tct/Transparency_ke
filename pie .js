
    
   
 
    
   
    
   
        document.addEventListener("DOMContentLoaded", function() {
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: ['Education', 'Healthcare', 'Infrastructure', 'Security', 'Agriculture','Sports'],
                    datasets: [{
                        data: [20, 20, 25, 15, 10,10],
                        backgroundColor: ['red', 'blue', 'green', 'yellow', 'purple', 'orange']
                    }]
                }
            });
    
            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: ['Education', 'Healthcare', 'Infrastructure', 'Security', 'Sports','Agriculture'],
                    datasets: [{
                        data: [30, 20, 15, 15, 10,25],
                        backgroundColor: ['teal','red', 'blue', 'green', 'yellow','purple']
                    }]
                }
            });
    
            const barCtx2 = document.getElementById('barChart2').getContext('2d');
            new Chart(barCtx2, {
                type: 'bar',
                data: {
                    labels: ['Nairobi', 'Kirinyaga', 'Machakos', 'Mombasa', 'Kisumu','Homabay','Migori','Machakos','Kisii'],
                    datasets: [{
                        data: [30000000, 20000000, 15000000, 10500000, 12000000,25000000,30000000,20000000,25000000],
                        backgroundColor: ['red', 'blue', 'green', 'yellow', 'purple','teal','brown','indigo','violet']
                    }]
                }
            });
        });
        