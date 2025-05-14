// Project tab functionality
function showTab(tabId) {
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Deactivate all tabs
    const tabs = document.querySelectorAll('.project-tab');
    tabs.forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Activate the selected tab and its content
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`.project-tab[onclick="showTab('${tabId}')"]`).classList.add('active');
}

// Project reactions functionality
function reactToProject(reaction) {
    const likesCount = document.getElementById('likesCount');
    const dislikesCount = document.getElementById('dislikesCount');
    
    if (reaction === 'like') {
        likesCount.textContent = parseInt(likesCount.textContent) + 1;
    } else if (reaction === 'dislike') {
        dislikesCount.textContent = parseInt(dislikesCount.textContent) + 1;
    }
}

function focusCommentBox() {
    document.getElementById('commentInput').focus();
}

function shareProject() {
    // Implement sharing functionality 
    alert('Share this project with others!');
}

function submitComment() {
    const commentInput = document.getElementById('commentInput');
    const commentText = commentInput.value.trim();
    
    if (commentText) {
        
        const commentsCount = document.getElementById('commentsCount');
        commentsCount.textContent = parseInt(commentsCount.textContent) + 1;
        
        // Clear the input
        commentInput.value = '';
        
        // You could also add the new comment to the DOM here
        alert('Comment submitted successfully!');
    }
}



