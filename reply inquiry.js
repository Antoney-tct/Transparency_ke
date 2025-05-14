// Global variables
let messages = [];
let selectedMessageId = null;

// Display all messages in the list
function renderMessagesList() {
    const messagesListEl = document.getElementById('messagesList');
    
    // Show empty state if no messages
    if (messages.length === 0) {
        messagesListEl.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No messages in your inbox</p>
            </div>
        `;
        return;
    }
    
    // Create HTML for each message
    let messageHTML = '';
    messages.forEach(message => {
        const isUnread = message.status === 'unread' ? 'unread' : '';
        const isSelected = message.id === selectedMessageId ? 'selected' : '';
        
        messageHTML += `
            <div class="message-item ${isUnread} ${isSelected}" 
                 data-id="${message.id}" onclick="selectMessage(${message.id})">
                <div class="message-header">
                    <div class="message-sender">${message.name}</div>
                    <div class="message-date">${formatDate(message.timestamp)}</div>
                </div>
                <div class="message-subject">${message.subject}</div>
                <div class="message-preview">${message.message.substring(0, 100)}${message.message.length > 100 ? '...' : ''}</div>
            </div>
        `;
    });
    
    messagesListEl.innerHTML = messageHTML;
}

// Show message details when selected
function selectMessage(messageId) {
    selectedMessageId = messageId;
    
    // Update UI to show selected message
    const messageItems = document.querySelectorAll('.message-item');
    messageItems.forEach(item => {
        item.classList.remove('selected');
        if (item.dataset.id == messageId) {
            item.classList.remove('unread');
            item.classList.add('selected');
        }
    });
    
    // Find the message in our data
    const message = messages.find(m => m.id === messageId);
    if (!message) return;
    
    // Mark as read if it was unread
    if (message.status === 'unread') {
        message.status = 'read';
        
        // Tell the server this message was read
        fetch(`/api/admin/messages/${messageId}/read`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'}
        }).catch(error => {
            console.error('Error marking message as read:', error);
        });
    }
    
    // Show message details in the right panel
    const detailEl = document.getElementById('messageDetail');
    detailEl.querySelector('.detail-content').innerHTML = `
        <div class="detail-info">
            <div class="detail-subject">${message.subject}</div>
            <div class="detail-meta">
                <div>From: <strong>${message.name}</strong> (${message.email})</div>
                <div>${formatDate(message.timestamp)}</div>
            </div>
        </div>
        
        <div class="detail-message">
            ${message.message.replace(/\n/g, '<br>')}
        </div>
        
        <div class="reply-form">
            <h3>Reply</h3>
            <textarea id="replyMessage" placeholder="Type your reply here..."></textarea>
            <div class="reply-actions">
                <button class="btn btn-primary" onclick="sendReply(${messageId})">Send Reply</button>
                <button class="btn btn-danger" onclick="deleteMessage(${messageId})">Delete Message</button>
            </div>
        </div>
    `;
}

// Send a reply to the message
function sendReply(messageId) {
    const replyText = document.getElementById('replyMessage').value.trim();
    
    // Validate reply text
    if (!replyText) {
        alert('Please enter a reply message');
        return;
    }
    
    const message = messages.find(m => m.id === messageId);
    if (!message) return;
    
    // Send reply to server
    fetch(`/api/admin/messages/${messageId}/reply`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            replyText,
            to: message.email
        }),
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        alert('Reply sent successfully!');
        document.getElementById('replyMessage').value = '';
        
        // Update message status and refresh list
        message.status = 'replied';
        renderMessagesList();
    })
    .catch(error => {
        console.error('Error sending reply:', error);
        alert('Failed to send reply. Please try again later.');
    });
}

// Delete a message
function deleteMessage(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }
    
    // Send delete request to server
    fetch(`/api/admin/messages/${messageId}`, {
        method: 'DELETE',
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        // Remove from our local data
        messages = messages.filter(m => m.id !== messageId);
        
        // Clear selection if needed
        if (selectedMessageId === messageId) {
            selectedMessageId = null;
            document.getElementById('messageDetail').querySelector('.detail-content').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-envelope-open"></i>
                    <p>Select a message to view details</p>
                </div>
            `;
        }
        
        // Update the UI
        renderMessagesList();
    })
    .catch(error => {
        console.error('Error deleting message:', error);
        alert('Failed to delete message. Please try again later.');
    });
}

// Format date for display
function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    
    // Today: show time only
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    
    // This year: show month and day
    if (date.getFullYear() === now.getFullYear()) {
        return date.toLocaleDateString([], { month: 'short', day: 'numeric' });
    }
    
    // Otherwise: show full date
    return date.toLocaleDateString([], { year: 'numeric', month: 'short', day: 'numeric' });
}

// Get messages from server when page loads
function fetchMessages() {
    fetch('/api/admin/messages')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            messages = data;
            renderMessagesList();
        })
        .catch(error => {
            console.error('Error fetching messages:', error);
            document.getElementById('messagesList').innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Error loading messages. Please try again later.</p>
                </div>
            `;
        });
}

// Start loading messages when page is ready
document.addEventListener('DOMContentLoaded', fetchMessages);
