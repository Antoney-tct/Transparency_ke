document.addEventListener('DOMContentLoaded', function() {
    // Initialize the progress circle
    initProgressCircle();
    
    // Add event listeners to tab buttons
    const tabButtons = document.querySelectorAll('.project-tab');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            showTab(tabId);
        });
    });
    
    // Initialize notification dropdown
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    
    if (notificationButton && notificationDropdown) {
        notificationButton.addEventListener('click', function() {
            notificationDropdown.style.display = 
                notificationDropdown.style.display === 'block' ? 'none' : 'block';
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!notificationButton.contains(event.target) && 
                !notificationDropdown.contains(event.target)) {
                notificationDropdown.style.display = 'none';
            }
        });
    }
});

// Function to show the selected tab
function showTab(tabId) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tabs
    const tabs = document.querySelectorAll('.project-tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Show the selected tab content
    const selectedTab = document.getElementById(tabId);
    if (selectedTab) {
        selectedTab.classList.add('active');
    }
    
    // Add active class to the clicked tab
    const clickedTab = document.querySelector(`.project-tab[onclick="showTab('${tabId}')"]`);
    if (clickedTab) {
        clickedTab.classList.add('active');
    }
}

// Function to initialize the progress circle
function initProgressCircle() {
    const canvas = document.getElementById('progressCircle');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = 50;
    
    // Draw background circle
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.lineWidth = 15;
    ctx.strokeStyle = '#e0e0e0';
    ctx.stroke();
    
    // Draw progress arc (45% in this case)
    const progress = 0.45; // 45%
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, -0.5 * Math.PI, (-0.5 + 2 * progress) * Math.PI);
    ctx.lineWidth = 15;
    ctx.strokeStyle = '#0b9d3f';
    ctx.stroke();
}

// Functions for project reactions
function reactToProject(reaction) {
    const likesCount = document.getElementById('likesCount');
    const dislikesCount = document.getElementById('dislikesCount');
    
    if (reaction === 'like' && likesCount) {
        likesCount.textContent = parseInt(likesCount.textContent) + 1;
    } else if (reaction === 'dislike' && dislikesCount) {
        dislikesCount.textContent = parseInt(dislikesCount.textContent) + 1;
    }
}

function focusCommentBox() {
    const commentInput = document.getElementById('commentInput');
    if (commentInput) {
        commentInput.focus();
    }
}

function shareProject() {
    // Implement sharing functionality
    alert('Share this project: ' + window.location.href);
}

function submitComment() {
    const commentInput = document.getElementById('commentInput');
    if (commentInput && commentInput.value.trim() !== '') {
        alert('Comment submitted: ' + commentInput.value);
        commentInput.value = '';
    }
}
