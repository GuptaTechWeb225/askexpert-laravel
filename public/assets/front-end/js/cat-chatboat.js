document.addEventListener('DOMContentLoaded', () => {
            const menuIcon = document.querySelector('.ec-menu-icon');
            const navLinks = document.querySelector('.ec-nav-links');
            const chatInput = document.querySelector('#chat-input');
            const sendBtn = document.querySelector('#send-btn');
            const expertChatArea = document.querySelector('#ec-expert-chat-area');
            const fullScreenChat = document.querySelector('#ec-full-screen-chat');
            const fullScreenChatArea = document.querySelector('#ec-full-screen-chat-area');
            const fullScreenChatInput = document.querySelector('#ec-full-screen-chat-input');
            const fullScreenSendBtn = document.querySelector('#ec-full-screen-send-btn');
            const closeChatBtn = document.querySelector('#ec-close-chat-btn');

            // Toggle mobile menu
            if (menuIcon && navLinks) {
                menuIcon.addEventListener('click', () => {
                    navLinks.classList.toggle('ec-active');
                });
            }

            // Dropdown functionality
            const dropdowns = document.querySelectorAll('.ec-dropdown');

            dropdowns.forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const content = dropdown.querySelector('.ec-dropdown-content');

                button.addEventListener('click', (event) => {
                    event.stopPropagation();

                    // Close all other dropdowns
                    dropdowns.forEach(other => {
                        if (other !== dropdown) {
                            other.querySelector('.ec-dropdown-content').style.display = 'none';
                        }
                    });

                    // Toggle this dropdown
                    content.style.display = content.style.display === 'flex' ? 'none' : 'flex';
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', (event) => {
                dropdowns.forEach(dropdown => {
                    const content = dropdown.querySelector('.ec-dropdown-content');
                    if (!dropdown.contains(event.target)) {
                        content.style.display = 'none';
                    }
                });
            });

            // Chatbot functionality
            const sendMessage = (inputElement, chatArea) => {
                const messageText = inputElement.value.trim();
                if (!messageText) return;

                const userMessage = document.createElement('div');
                userMessage.classList.add('ec-message-container', 'ec-user-side');
                userMessage.innerHTML = `
                    <div class="ec-message ec-user">
                        <p>${messageText}</p>
                    </div>
                    <img src="/dist/assets/img/expert/expert-2.png" alt="User" class="ec-message-avatar">
                `;
                expertChatArea.appendChild(userMessage);
                fullScreenChatArea.appendChild(userMessage.cloneNode(true));

                setTimeout(() => {
                    const botMessage = document.createElement('div');
                    botMessage.classList.add('ec-message-container', 'ec-bot-side');
                    botMessage.innerHTML = `
                        <img src="/dist/assets/img/chat-avtar.png" alt="Pearl Chatbot" class="ec-message-avatar">
                        <div class="ec-message ec-bot">
                            <p>Thank you for your question! Can you provide more details about your pet's symptoms?</p>
                        </div>
                    `;
                    expertChatArea.appendChild(botMessage);
                    fullScreenChatArea.appendChild(botMessage.cloneNode(true));
                    expertChatArea.scrollTop = expertChatArea.scrollHeight;
                    fullScreenChatArea.scrollTop = fullScreenChatArea.scrollHeight;
                }, 1000);

                inputElement.value = '';
                expertChatArea.scrollTop = expertChatArea.scrollHeight;
                fullScreenChatArea.scrollTop = fullScreenChatArea.scrollHeight;
            };

            // Open full-screen chat on mobile input click
            if (chatInput) {
                chatInput.addEventListener('click', () => {
                    if (window.innerWidth <= 768) {
                        fullScreenChat.style.display = 'flex';
                        fullScreenChatArea.innerHTML = expertChatArea.innerHTML;
                        fullScreenChatInput.focus();
                    }
                });
            }

            // Send message from main chat
            if (sendBtn && chatInput && expertChatArea) {
                sendBtn.addEventListener('click', () => {
                    if (window.innerWidth > 768) {
                        sendMessage(chatInput, expertChatArea);
                    }
                });
                chatInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter' && window.innerWidth > 768) {
                        sendMessage(chatInput, expertChatArea);
                    }
                });
            }

            // Send message from full-screen chat
            if (fullScreenSendBtn && fullScreenChatInput && fullScreenChatArea) {
                fullScreenSendBtn.addEventListener('click', () => sendMessage(fullScreenChatInput, fullScreenChatArea));
                fullScreenChatInput.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') sendMessage(fullScreenChatInput, fullScreenChatArea);
                });
            }

            // Close full-screen chat
            if (closeChatBtn) {
                closeChatBtn.addEventListener('click', () => {
                    fullScreenChat.style.display = 'none';
                });
            }
        });