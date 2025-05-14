document.addEventListener('DOMContentLoaded', function() {
    // Data for all years
    const progressData = {
        "2010-2015": [
            {year: 2010, info: "Vision 2030 launched. New constitution adopted."},
            {year: 2011, info: "Economic growth reached 5.1%. Infrastructure projects began."},
            {year: 2012, info: "Devolution process started. County governments established."},
            {year: 2013, info: "SGR Phase 1 construction began. Mobile money expanded."},
            {year: 2014, info: "Universal healthcare pilot launched. GDP growth at 5.7%."},
            {year: 2015, info: "SGR progress continued. Digital literacy programs started."}
        ],
        "2016-2020": [
            {year: 2016, info: "SGR Nairobi-Mombasa completed. Huduma centers expanded."},
            {year: 2017, info: "Digital literacy program launched. Manufacturing agenda started."},
            {year: 2018, info: "Big Four Agenda launched. Affordable housing projects began."},
            {year: 2019, info: "Universal health coverage rollout. 5.4% GDP growth."},
            {year: 2020, info: "COVID-19 response. Economic stimulus packages."}
        ],
        "2021-2025": [
            {year: 2021, info: "Economic recovery. Digital superhighway initiative."},
            {year: 2022, info: "New administration. Hustler Fund launched."},
            {year: 2023, info: "Affordable housing projects expanded."},
            {year: 2024, info: "Ongoing projects..."},
            {year: 2025, info: "Vision 2030 target year."}
        ]
    };

    // Get DOM elements
    const periodButtons = document.querySelectorAll('.progress-period-buttons button');
    const trackLine = document.querySelector('.progress-track-line');
    const infoDisplay = document.querySelector('.progress-info-display');

    // Function to show years for selected period
    function showPeriod(period) {
        // Clear existing markers
        trackLine.innerHTML = '';
        
        // Get years for selected period
        const years = progressData[period];
        
        // Create markers for each year
        years.forEach((item, index) => {
            // Calculate position (0% to 100%)
            const position = (index / (years.length - 1)) * 100;
            
            // Create marker element
            const marker = document.createElement('div');
            marker.className = 'progress-year-marker';
            marker.style.left = `calc(${position}% - 12px)`;
            marker.dataset.year = item.year;
            marker.dataset.info = item.info;
            
            // Create year label
            const label = document.createElement('div');
            label.className = 'progress-year-label';
            label.textContent = item.year;
            
            // Add click event to show info
            marker.addEventListener('click', function() {
                // Remove active class from all markers
                document.querySelectorAll('.progress-year-marker').forEach(m => {
                    m.classList.remove('active');
                });
                
                // Add active class to clicked marker
                this.classList.add('active');
                
                // Show info in the display
                infoDisplay.innerHTML = `<p><strong>${item.year}:</strong> ${item.info}</p>`;
            });
            
            // Add label to marker
            marker.appendChild(label);
            
            // Add marker to track line
            trackLine.appendChild(marker);
        });
    }

    // Add click events to period buttons
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            periodButtons.forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Show the selected period
            showPeriod(this.dataset.period);
        });
    });

    // Initialize with first period
    showPeriod('2010-2015');
});