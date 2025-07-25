<?php include './includes/header.php'; ?>
<?php include './includes/sidebar.php'; ?>
<?php
include 'connect.php';

$userId = $_SESSION['userId'];
$query = "SELECT h.id, h.name 
          FROM hall h
          JOIN assignhall ah ON h.id = ah.hallId
          WHERE ah.userId = $userId";
$result = mysqli_query($con, $query);
$halls = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<div class="main">
    <?php include './includes/topNavBar.php'; ?>
    <div class="mt-4 mx-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 d-flex align-items-center gap-2">
                <ion-icon name="chatbubbles-outline"></ion-icon>
                <span>Chat Dashboard</span>
            </h3>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="chat-container">
                    <div class="conversation-list">
                        <div class="conversation-list-header">
                            <ion-icon name="people-outline"></ion-icon>
                            <span>Active Conversations</span>
                        </div>
                        <div class="conversation-list-content" id="conversations"></div>
                    </div>
                    
                    <div class="chat-window">
                        <div class="chat-window-wrapper">
                            <div id="selected-conversation" style="display:none;">
                                <div class="chat-header-container">
                                    <button class="mobile-back-btn" onclick="toggleChatView()">
                                        <ion-icon name="arrow-back-outline"></ion-icon>
                                    </button>
                                    <ion-icon name="person-circle-outline"></ion-icon>
                                    <div>
                                        <h5 class="mb-0">Chat with <span id="customer-name"></span></h5>
                                        <small>
                                            <ion-icon name="business-outline"></ion-icon>
                                            <span id="hall-name"></span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="chat-messages-container" id="chat-messages"></div>
                                
                                <div class="chat-input-container">
                                    <div class="chat-input">
                                        <input type="text" id="message-input" placeholder="Type your reply...">
                                        <button id="send-btn">
                                            <ion-icon name="send-outline"></ion-icon>
                                            <span>Send</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="no-conversation" class="no-conversation-container">
                                <ion-icon name="chatbox-ellipses-outline" style="font-size: 3rem; color: #ddd;"></ion-icon>
                                <h5>No conversation selected</h5>
                                <p class="text-muted">Select a conversation from the list to start chatting</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentConversation = null;
let refreshInterval;

function toggleChatView() {
    const chatWindow = document.getElementById('selected-conversation');
    const conversationList = document.querySelector('.conversation-list');
    
    if (window.innerWidth <= 768) {
        chatWindow.style.display = 'none';
        conversationList.style.display = 'flex';
        
        // Highlight the active conversation in the list
        if (currentConversation) {
            // Remove active class from all
            document.querySelectorAll('.conversation').forEach(conv => {
                conv.classList.remove('active');
            });
            
            // Add to current
            const activeConv = document.querySelector(
                `.conversation[data-userid="${currentConversation.userId}"][data-hallid="${currentConversation.hallId}"]`
            );
            if (activeConv) {
                activeConv.classList.add('active');
            }
        }
    }
}

function handleMobileView() {
    const isMobile = window.innerWidth <= 768;
    if (isMobile) {
        // Hide chat window by default on mobile
        document.getElementById('selected-conversation').style.display = 'none';
        document.getElementById('no-conversation').style.display = 'flex';
        
        // Show conversation list by default
        document.querySelector('.conversation-list').style.display = 'flex';
    } else {
        // Reset for desktop
        document.querySelector('.conversation-list').style.display = 'flex';
        document.getElementById('no-conversation').style.display = 'flex';
    }
}

// Modified selectConversation for mobile
function selectConversation(userId, hallId, userName, hallName) {
    document.querySelectorAll('.conversation').forEach(conv => {
        conv.classList.remove('active');
    });
    
    // Add active class to selected conversation
    const selectedConv = document.querySelector(
        `.conversation[data-userid="${userId}"][data-hallid="${hallId}"]`
    );
    if (selectedConv) {
        selectedConv.classList.add('active');
    }
    
    // Rest of your existing code...
    currentConversation = { userId, hallId };
    
    // Update UI
    document.getElementById('customer-name').textContent = userName;
    document.getElementById('hall-name').textContent = hallName;
    
    // Mobile-specific behavior
    if (window.innerWidth <= 768) {
        document.getElementById('selected-conversation').style.display = 'flex';
        document.getElementById('no-conversation').style.display = 'none';
        document.querySelector('.conversation-list').style.display = 'none';
    } else {
        document.getElementById('selected-conversation').style.display = 'flex';
        document.getElementById('no-conversation').style.display = 'none';
    }
    
    loadMessages().then(() => {
        const convElement = document.querySelector(`.conversation[data-userid="${userId}"][data-hallid="${hallId}"]`);
        if (convElement) {
            convElement.classList.remove('unread');
            const badge = convElement.querySelector('.unread-badge');
            if (badge) badge.remove();
        }
        loadConversations();
    });
    
    startRefreshing();
}

// Initialize on load and resize
window.addEventListener('load', () => {
    handleMobileView();
    // addMobileBackButton();
});

window.addEventListener('resize', () => {
    handleMobileView();
    // addMobileBackButton();
});

// Modified loadConversations to ensure proper unread display
function loadConversations() {
    fetch('get_conversations.php')
        .then(response => response.json())
        .then(conversations => {
            let html = '';
            conversations.forEach(conv => {
                const initials = conv.userName.split(' ').map(n => n[0]).join('');
                const hasUnread = conv.unread > 0;
                
                html += `
                <div class="conversation ${hasUnread ? 'unread' : ''}" 
                     data-userid="${conv.userId}" 
                     data-hallid="${conv.hallId}"
                     onclick="selectConversation(${conv.userId}, ${conv.hallId}, '${conv.userName}', '${conv.hallName}')">
                    <div class="conversation-avatar">${initials}</div>
                    <div class="conversation-content">
                        <strong>${conv.userName}</strong>
                        <p>${conv.lastMessage}</p>
                        <small>
                            <ion-icon name="time-outline"></ion-icon>
                            ${new Date(conv.lastTime).toLocaleString()}
                        </small>
                    </div>
                    ${hasUnread ? '<span class="unread-badge">New</span>' : ''}
                </div>`;
            });
            
            document.getElementById('conversations').innerHTML = html || `
                <div class="p-3 text-center text-muted">
                    <ion-icon name="alert-circle-outline"></ion-icon>
                    <p>No conversations yet</p>
                </div>`;
        });
}
// Load messages for selected conversation
function loadMessages() {
    if (!currentConversation) return;
    
    fetch(`get_messages.php?userId=${currentConversation.userId}&hallId=${currentConversation.hallId}`)
        .then(response => response.json())
        .then(messages => {
            let html = '';
            messages.forEach(msg => {
                const messageClass = msg.sender === 'user' ? 'user-message' : 'hall-message';
                html += `
                <div class="message ${messageClass}">
                    <p>${msg.message}</p>
                    <small>
                        <ion-icon name="time-outline"></ion-icon>
                        ${new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </small>
                </div>`;
            });
            document.getElementById('chat-messages').innerHTML = html;
            scrollToBottom();
        });
}

// Send message as hall owner
document.getElementById('send-btn').addEventListener('click', sendMessage);
document.getElementById('message-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});

function sendMessage() {
    if (!currentConversation || !document.getElementById('message-input').value.trim()) return;
    
    const message = document.getElementById('message-input').value;
    
    fetch('send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `userId=${currentConversation.userId}&hallId=${currentConversation.hallId}&message=${encodeURIComponent(message)}&sender=hall`
    })
    .then(response => response.json())
    .then(() => {
        document.getElementById('message-input').value = '';
        loadMessages();
        loadConversations();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to send message');
    });
}

// Auto-refresh messages
function startRefreshing() {
    clearInterval(refreshInterval);
    refreshInterval = setInterval(() => {
        if (currentConversation) {
            loadMessages();
            loadConversations();
        }
    }, 3000);
}

function scrollToBottom() {
    const container = document.getElementById('chat-messages');
    // Small timeout to ensure DOM is updated
    setTimeout(() => {
        container.scrollTop = container.scrollHeight;
    }, 50);
}

// Initial load
loadConversations();
// Add this to your existing JavaScript

</script>

<?php include './includes/footer.php'; ?>