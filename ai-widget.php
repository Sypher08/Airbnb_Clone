<style>
.ai-chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: 'Montserrat', sans-serif;
}

.ai-chat-button {
    width: 60px;
    height: 60px;
    background: #ff385c;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: transform 0.2s;
    color: white;
    font-size: 28px;
}

.ai-chat-button:hover {
    transform: scale(1.05);
}

.ai-chat-window {
    position: absolute;
    bottom: 80px;
    right: 0;
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 8px 28px rgba(0,0,0,0.28);
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #ebebeb;
}

.ai-chat-window.open {
    display: flex;
}

.ai-chat-header {
    background: #ff385c;
    color: white;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.ai-chat-header h3 {
    font-size: 16px;
    font-weight: 600;
    margin: 0;
}

.ai-chat-close {
    cursor: pointer;
    font-size: 20px;
    background: none;
    border: none;
    color: white;
}

.ai-chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f7f7f7;
}

.ai-message {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
}

.user-message {
    align-items: flex-end;
}

.user-message .message-bubble {
    background: #ff385c;
    color: white;
    border-radius: 18px 18px 4px 18px;
    padding: 10px 14px;
    max-width: 80%;
    word-wrap: break-word;
}

.ai-message .message-bubble {
    background: white;
    color: #222;
    border-radius: 18px 18px 18px 4px;
    padding: 10px 14px;
    max-width: 80%;
    word-wrap: break-word;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message-time {
    font-size: 10px;
    color: #717171;
    margin-top: 4px;
    margin-left: 8px;
    margin-right: 8px;
}

.ai-chat-input {
    padding: 16px;
    border-top: 1px solid #ebebeb;
    display: flex;
    gap: 8px;
    background: white;
}

.ai-chat-input input {
    flex: 1;
    padding: 10px 12px;
    border: 1px solid #ebebeb;
    border-radius: 24px;
    font-family: inherit;
    font-size: 14px;
}

.ai-chat-input input:focus {
    outline: none;
    border-color: #ff385c;
}

.ai-chat-input button {
    background: #ff385c;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 24px;
    cursor: pointer;
    font-weight: 600;
}

.ai-typing {
    color: #717171;
    font-style: italic;
    padding: 8px 12px;
}

@media (max-width: 768px) {
    .ai-chat-window {
        width: calc(100vw - 40px);
        right: 0;
        bottom: 80px;
    }
}
</style>

<div class="ai-chat-widget">
    <div class="ai-chat-button" onclick="toggleAIChat()">
        🤖
    </div>
    <div class="ai-chat-window" id="aiChatWindow">
        <div class="ai-chat-header">
            <h3>🤖 AI Travel Assistant</h3>
            <button class="ai-chat-close" onclick="toggleAIChat()">✕</button>
        </div>
        <div class="ai-chat-messages" id="aiChatMessages">
            <div class="ai-message">
                <div class="message-bubble">
                    👋 Hi! I'm your AI travel assistant. Ask me about properties, bookings, locations, or anything about your stay!
                </div>
                <div class="message-time">Just now</div>
            </div>
        </div>
        <div class="ai-chat-input">
            <input type="text" id="aiChatInput" placeholder="Ask me anything..." onkeypress="if(event.key==='Enter') sendAIMessage()">
            <button onclick="sendAIMessage()">Send</button>
        </div>
    </div>
</div>

<script>
let isWaitingForResponse = false;

function toggleAIChat() {
    const window = document.getElementById('aiChatWindow');
    window.classList.toggle('open');
    if (window.classList.contains('open')) {
        document.getElementById('aiChatInput').focus();
    }
}

function addMessage(message, isUser) {
    const messagesDiv = document.getElementById('aiChatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = isUser ? 'user-message ai-message' : 'ai-message';
    messageDiv.innerHTML = `
        <div class="message-bubble">${escapeHtml(message)}</div>
        <div class="message-time">${new Date().toLocaleTimeString()}</div>
    `;
    messagesDiv.appendChild(messageDiv);
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML.replace(/\n/g, '<br>');
}

async function sendAIMessage() {
    if (isWaitingForResponse) return;
    
    const input = document.getElementById('aiChatInput');
    const message = input.value.trim();
    if (!message) return;
    
    addMessage(message, true);
    input.value = '';
    isWaitingForResponse = true;
    
    // Add typing indicator
    const typingDiv = document.createElement('div');
    typingDiv.className = 'ai-message';
    typingDiv.id = 'typingIndicator';
    typingDiv.innerHTML = '<div class="ai-typing">🤖 Typing...</div>';
    document.getElementById('aiChatMessages').appendChild(typingDiv);
    document.getElementById('aiChatMessages').scrollTop = document.getElementById('aiChatMessages').scrollHeight;
    
    try {
        const response = await fetch('/ai_chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message })
        });
        const data = await response.json();
        
        // Remove typing indicator
        document.getElementById('typingIndicator')?.remove();
        
        if (data.response) {
            addMessage(data.response, false);
        } else if (data.error) {
            addMessage('Sorry, I encountered an error. Please try again.', false);
        }
    } catch (error) {
        document.getElementById('typingIndicator')?.remove();
        addMessage('Network error. Please check your connection.', false);
    }
    
    isWaitingForResponse = false;
}
</script>
