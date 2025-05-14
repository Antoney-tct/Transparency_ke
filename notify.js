// Notification system
document.getElementById('notificationButton').addEventListener('click', function() {
    const dropdown = document.getElementById('notificationDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    
    // Mark notifications as read when dropdown is opened
    if (dropdown.style.display === 'block') {
        markNotificationsAsRead();
    }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const notificationContainer = document.getElementById('notificationButton');
    const dropdown = document.getElementById('notificationDropdown');
    
    if (!notificationContainer.contains(event.target) {
        dropdown.style.display = 'none';
    }
});

function markNotificationsAsRead() {
    fetch('mark_notifications_read.php')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('notificationBadge').style.display = 'none';
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.classList.remove('unread');
                });
            }
        });
}