
function showTimeline(timelineId) {
    // Hide all timeline contents
    document.querySelectorAll('.timeline-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.timeline-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show selected timeline and activate tab
    document.getElementById(timelineId + '-timeline').classList.add('active');
    event.currentTarget.classList.add('active');
}




// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {

    // --- Subscription Form Handling ---
    const subscriptionForm = document.querySelector('.subscription-form');

    if (subscriptionForm) { // Check if the form exists on the current page
        subscriptionForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop the form from submitting the traditional way

            const emailInput = this.querySelector('input[type="email"]');
            const emailValue = emailInput.value;

            // Check if the email input is not empty (ignoring spaces)
            if (emailValue.trim() !== '') {
                // Show the thank you message
                alert('Thank you for subscribing to government updates!');

                // Clear the form field
                this.reset();
            } else {
                //  Alert the user if the email field is empty
                alert('Please enter your email address first.');
            }
        });
    }

   
});


