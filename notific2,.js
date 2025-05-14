// Notification Dropdown Toggle
document.addEventListener('DOMContentLoaded', function() {
   


document.querySelector('.notification-btn').addEventListener('click', function(e) {
    e.stopPropagation();
    const dropdown = document.querySelector('.notification-dropdown');
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function() {
    const dropdown = document.querySelector('.notification-dropdown');
    if (dropdown) {
        dropdown.style.display = 'none';
    }
});
});