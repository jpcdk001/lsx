<!DOCTYPE html>
<html>
<head>
  <title>ChatGPT - ��������</title>
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
      <input type="text" id="user-input" placeholder="������Ϣ..." />
      <button id="send-button">����</button>
    </div>
  </div>

  <script>
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    // �����û���Ϣ
    function sendMessage() {
      const userMessage = userInput.value.trim();
      if (userMessage === '') return;

      const userMessageElement = document.createElement('div');
      userMessageElement.classList.add('user-message');
      userMessageElement.textContent = userMessage;
      chatMessages.appendChild(userMessageElement);
      userInput.value = '';

      // ����API��ȡ�ظ�
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
        chatMessages.scrollTop = chatMessages.scrollHeight; // �������ײ�
      })
      .catch(error => console.error(error));
    }

    // ��������
    sendButton.addEventListener('click', sendMessage);

    // �����س���������������Ϣ����
    userInput.addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        sendMessage();
      }
    });
  </script>
</body>
</html>
