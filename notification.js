document.addEventListener('DOMContentLoaded', function() {
    // Get notification elements
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    const markAllReadBtn = document.querySelector('.mark-all-read');
    
    // Toggle notification dropdown when clicking the bell icon
    if (notificationButton && notificationDropdown) {
        notificationButton.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent event from bubbling up
            
            // Toggle dropdown visibility
            if (notificationDropdown.style.display === 'block') {
                notificationDropdown.style.display = 'none';
            } else {
                notificationDropdown.style.display = 'block';
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationButton.contains(event.target) && !notificationDropdown.contains(event.target)) {
                notificationDropdown.style.display = 'none';
            }
        });
    }
    
    // Mark all notifications as read
    if (markAllReadBtn && notificationBadge) {
        markAllReadBtn.addEventListener('click', function() {
            // Remove unread class from all notification items
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            unreadItems.forEach(item => {
                item.classList.remove('unread');
            });
            
            // Update badge count to zero
            notificationBadge.textContent = '0';
        });
    }
    
    // Individual notification item click handler
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function(event) {
            // If it's an unread notification, mark it as read
            if (this.classList.contains('unread')) {
                this.classList.remove('unread');
                
                // Update badge count
                const currentCount = parseInt(notificationBadge.textContent);
                if (currentCount > 0) {
                    notificationBadge.textContent = currentCount - 1;
                }
            }
            
            // Don't close the dropdown immediately when clicking on a notification
            event.stopPropagation();
            
           
    });
});
