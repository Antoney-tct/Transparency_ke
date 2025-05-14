// Notification dropdown functionality on the clicking the bell icon
document.addEventListener('DOMContentLoaded', function() {
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');
    
   
    notificationButton.addEventListener('click', function(e) {
        e.stopPropagation();
        if (notificationDropdown.style.display === 'block') {
            notificationDropdown.style.display = 'none';
        } else {
            notificationDropdown.style.display = 'block';
        }
    });
    
    
    document.addEventListener('click', function(e) {
        if (!notificationDropdown.contains(e.target) && e.target !== notificationButton) {
            notificationDropdown.style.display = 'none';
        }
    });
    
 
    const markAllReadButton = document.querySelector('.mark-all-read');
    markAllReadButton.addEventListener('click', function(e) {
        e.preventDefault();
        const unreadItems = document.querySelectorAll('.notification-item.unread');
        unreadItems.forEach(item => {
            item.classList.remove('unread');
        });
        notificationBadge.textContent = '0';
    });
    
   
    const notificationItems = document.querySelectorAll('.notification-item');
    notificationItems.forEach(item => {
        item.addEventListener('click', function() {
            if (this.classList.contains('unread')) {
                this.classList.remove('unread');
                const currentCount = parseInt(notificationBadge.textContent);
                if (currentCount > 0) {
                    notificationBadge.textContent = currentCount - 1;
                }
            }
        });
    });
});

