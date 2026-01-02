   document.addEventListener('DOMContentLoaded', () => {
   const chatIcon = document.querySelector('.chat-floating-icon');
      const chatPopup = document.getElementById('chat-popup');
      const closeChatBtn = document.getElementById('close-chat');
      const inputField = document.querySelector('.chat-footer input');
      const sendBtn = document.querySelector('.send-btn');
      const chatBody = document.querySelector('.chat-body');
      const botReplies = [
        "Sure! Let me help you with that.",
        "Can you give me more details?",
        "That's interesting!",
        "I see. Please wait a moment...",
        "Thanks for your message!"
      ];
      function appendUserMessage(message) {
        const userMsg = document.createElement('div');
        userMsg.className = 'message-container user-side';
        userMsg.innerHTML = `
            <div class="message user">${message}</div>
            <img src="https://i.pravatar.cc/150?img=12" class="message-avatar" alt="User Avatar">
        `;
        chatBody.appendChild(userMsg);
        chatBody.scrollTop = chatBody.scrollHeight;
      }

      // Function to append bot message
      function appendBotMessage(message) {
        const botMsg = document.createElement('div');
        botMsg.className = 'message-container ';
        botMsg.innerHTML = `
            <img src="https://askexperts.guptatechweb.com/dist/assets/img/chat-avtar.png" class="message-avatar" alt="Bot Avatar">
            <div class="message bot">${message}</div>
        `;
        chatBody.appendChild(botMsg);
        chatBody.scrollTop = chatBody.scrollHeight;
      }

      // Function to handle user message + bot response
      function sendMessage() {
        const userInput = inputField.value.trim();
        if (userInput !== '') {
          appendUserMessage(userInput);
          inputField.value = '';

          // Simulate bot response after delay
          setTimeout(() => {
            const randomReply = botReplies[Math.floor(Math.random() * botReplies.length)];
            appendBotMessage(randomReply);
          }, 800); // 0.8 second delay
        }
      }


      chatIcon.addEventListener('click', () => {
        const isOpening = !chatPopup.classList.contains('show');
        chatPopup.classList.toggle('show');

        if (isOpening) {

          chatBody.innerHTML = '';

          setTimeout(() => {
            appendBotMessage("Hi, Good morning!");
          }, 300);

          setTimeout(() => {
            appendBotMessage("How may I help you?");
          }, 1000);
        }
      });

      // Close button
      closeChatBtn.addEventListener('click', () => {
        chatPopup.classList.remove('show');
        chatBody.innerHTML = '';
      });

      sendBtn.addEventListener('click', sendMessage);

      inputField.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          sendMessage();
        }
      });
    });