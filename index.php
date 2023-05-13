<!DOCTYPE html>
<html>
<head>
  <title>ChatGPT - 在线聊天</title>
  <style>
    body {
      background-color: #f2f2f2;
      font-family: Arial, sans-serif;
    }

    #chat-container {
      max-width: 500px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    #chat-messages {
      margin-bottom: 20px;
    }

    .user-message {
      background-color: #f2f2f2;
      color: #333;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 4px;
    }

    .bot-message {
      background-color: #f5f5f5;
      color: #666;
      padding: 10px;
      margin-bottom: 10px;
      border-radius: 4px;
    }

    .input-container {
      display: flex;
    }

    .input-container input[type="text"] {
      flex-grow: 1;
      padding: 10px;
      border-radius: 4px 0 0 4px;
      border: none;
    }

    .input-container button {
      background-color: #555;
      color: #fff;
      border: none;
      border-radius: 0 4px 4px 0;
      padding: 10px 20px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div id="chat-container">
    <div id="chat-messages"></div>
    <div class="input-container">
      <input type="text" id="user-input" placeholder="输入消息..." />
      <button id="send-button">发送</button>
    </div>
  </div>

  <script>
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    // 发送用户消息
    function sendMessage() {
      const userMessage = userInput.value.trim();
      if (userMessage === '') return;

      const userMessageElement = document.createElement('div');
      userMessageElement.classList.add('user-message');
      userMessageElement.textContent = userMessage;
      chatMessages.appendChild(userMessageElement);
      userInput.value = '';

      // 调用API获取回复
      fetch('send_message.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ message: userMessage })
      })
      .then(response => response.json())
      .then(data => {
        const botMessageElement = document.createElement('div');
        botMessageElement.classList.add('bot-message');
        botMessageElement.textContent = data.reply;
        chatMessages.appendChild(botMessageElement);
        chatMessages.scrollTop = chatMessages.scrollHeight; // 滚动到底部
      })
      .catch(error => console.error(error));
    }

    // 监听发送
    sendButton.addEventListener('click', sendMessage);

    // 监听回车键，触发发送消息函数
    userInput.addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        sendMessage();
      }
    });
  </script>
</body>
</html>
