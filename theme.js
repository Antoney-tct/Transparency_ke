document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    
    // Check for saved theme preference or use device preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    // Set initial theme
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.setAttribute('data-theme', 'dark');
        themeIcon.classList.replace('fa-moon', 'fa-sun');
    }
    
    // Toggle theme function
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        
        // Update DOM and localStorage
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        
        // Update icon
        if (newTheme === 'dark') {
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        } else {
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        }
        
        // Update charts if they exist
        updateChartsTheme(newTheme);
    }
    
    // Add click event to theme toggle button
    themeToggle.addEventListener('click', toggleTheme);
    
    // Function to update chart themes
    function updateChartsTheme(theme) {
        // Get all chart instances
        if (window.Chart && Chart.instances) {
            Object.values(Chart.instances).forEach(chart => {
                // Update chart colors based on theme
                if (theme === 'dark') {
                    // Set dark mode colors for chart elements
                    chart.options.scales.x.grid.color = 'rgba(255, 255, 255, 0.1)';
                    chart.options.scales.y.grid.color = 'rgba(255, 255, 255, 0.1)';
                    chart.options.scales.x.ticks.color = '#bbbbbb';
                    chart.options.scales.y.ticks.color = '#bbbbbb';
                    chart.options.plugins.legend.labels.color = '#f0f0f0';
                } else {
                    // Set light mode colors for chart elements
                    chart.options.scales.x.grid.color = 'rgba(0, 0, 0, 0.1)';
                    chart.options.scales.y.grid.color = 'rgba(0, 0, 0, 0.1)';
                    chart.options.scales.x.ticks.color = '#666666';
                    chart.options.scales.y.ticks.color = '#666666';
                    chart.options.plugins.legend.labels.color = '#333333';
                }
                chart.update();
            });
        }
    }
    
    // Initialize charts with correct theme
    const currentTheme = document.documentElement.getAttribute('data-theme');
    if (currentTheme === 'dark') {
        // Set initial dark theme for charts when they load
        const checkChartsLoaded = setInterval(() => {
            if (window.Chart && Chart.instances && Object.keys(Chart.instances).length > 0) {
                updateChartsTheme('dark');
                clearInterval(checkChartsLoaded);
            }
        }, 100);
    }
});
