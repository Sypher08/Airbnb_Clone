<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Travel Assistant - Airbnb Clone</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: white;
            border-bottom: 1px solid #ebebeb;
            padding: 16px 0;
        }
        
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ff385c;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            gap: 24px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #222;
            font-size: 14px;
        }
        
        .assistant-container {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .assistant-header {
            background: #ff385c;
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .assistant-header h1 {
            font-size: 32px;
            margin-bottom: 8px;
        }
        
        .assistant-header p {
            opacity: 0.9;
        }
        
        .chat-container {
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f7f7f7;
        }
        
        .message {
            margin-bottom: 20px;
            display: flex;
        }
        
        .user-message {
            justify-content: flex-end;
        }
        
        .ai-message {
            justify-content: flex-start;
        }
        
        .message-bubble {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 20px;
            word-wrap: break-word;
        }
        
        .user-message .message-bubble {
            background: #ff385c;
            color: white;
            border-radius: 20px 20px 4px 20px;
        }
        
        .ai-message .message-bubble {
            background: white;
            color: #222;
            border-radius: 20px 20px 20px 4px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .message-time {
            font-size: 11px;
            color: #717171;
            margin-top: 4px;
            margin-left: 12px;
        }
        
        .input-area {
            padding: 20px;
            background: white;
            border-top: 1px solid #ebebeb;
            display: flex;
            gap: 12px;
        }
        
        .input-area input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #ebebeb;
            border-radius: 30px;
            font-family: inherit;
            font-size: 14px;
        }
        
        .input-area input:focus {
            outline: none;
            border-color: #ff385c;
        }
        
        .input-area button {
            background: #ff385c;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .suggestions {
            padding: 16px 20px;
            background: white;
            border-top: 1px solid #ebebeb;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .suggestion-chip {
            padding: 8px 16px;
            background: #f7f7f7;
            border-radius: 30px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .suggestion-chip:hover {
            background: #ebebeb;
        }
        
        .typing {
            color: #717171;
            font-style: italic;
            padding: 8px 16px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">airbnb</a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="add-listing.php">Become a host</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="my-bookings.php">My trips</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="assistant-container">
        <div class="assistant-header">
            <h1>🤖 AI Travel Assistant</h1>
            <p>Ask me anything about your travel plans</p>
        </div>
        
        <div class="chat-container">
            <div class="messages-area" id="messagesArea">
                <div class="message ai-message">
                    <div>
                        <div class="message-bubble">
                            👋 Hello! I'm your AI travel assistant. I can help you find properties, check prices, suggest destinations, and answer any questions about bookings. What would you like to know?
                        </div>
                        <div class="message-time">Just now</div>
                    </div>
                </div>
            </div>
            
            <div class="suggestions">
                <div class="suggestion-chip" onclick="sendSuggestion('Show me properties in Pune')">🏠 Properties in Pune</div>
                <div class="suggestion-chip" onclick="sendSuggestion('What is the average price?')">💰 Average price</div>
                <div class="suggestion-chip" onclick="sendSuggestion('How do I book a property?')">📅 How to book</div>
                <div class="suggestion-chip" onclick="sendSuggestion('Cancellation policy')">❌ Cancellation policy</div>
            </div>
            
            <div class="input-area">
                <input type="text" id="chatInput" placeholder="Ask me anything..." onkeypress="if(event.key==='Enter') sendMessage()">
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </div>
    
    <script>
        let isWaiting = false;
        
        function addMessage(message, isUser) {
            const area = document.getElementById('messagesArea');
            const div = document.createElement('div');
            div.className = `message ${isUser ? 'user-message' : 'ai-message'}`;
            div.innerHTML = `
                <div>
                    <div class="message-bubble">${escapeHtml(message)}</div>
                    <div class="message-time">${new Date().toLocaleTimeString()}</div>
                </div>
            `;
            area.appendChild(div);
            area.scrollTop = area.scrollHeight;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML.replace(/\n/g, '<br>');
        }
        
        function sendSuggestion(text) {
            document.getElementById('chatInput').value = text;
            sendMessage();
        }
        
        async function sendMessage() {
            if (isWaiting) return;
            
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            if (!message) return;
            
            addMessage(message, true);
            input.value = '';
            isWaiting = true;
            
            // Add typing indicator
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message ai-message';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = '<div class="typing">🤖 Typing...</div>';
            document.getElementById('messagesArea').appendChild(typingDiv);
            document.getElementById('messagesArea').scrollTop = document.getElementById('messagesArea').scrollHeight;
            
            try {
                const response = await fetch('/ai_chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message })
                });
                const data = await response.json();
                
                document.getElementById('typingIndicator')?.remove();
                
                if (data.response) {
                    addMessage(data.response, false);
                } else {
                    addMessage('Sorry, I encountered an error. Please try again.', false);
                }
            } catch (error) {
                document.getElementById('typingIndicator')?.remove();
                addMessage('Network error. Please check your connection.', false);
            }
            
            isWaiting = false;
        }
    </script>
</body>
</html>
