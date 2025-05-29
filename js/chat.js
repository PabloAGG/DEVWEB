document.addEventListener('DOMContentLoaded', function() {
    const openChatButton = document.getElementById('openChatButton');
    const chatFollowersModal = document.getElementById('chatFollowersModal');
    
    // Referencias a las nuevas listas
    const chatFollowedList = document.getElementById('chatFollowedList'); 
    const chatOthersList = document.getElementById('chatOthersList');
    
    const chatWindowModal = document.getElementById('chatWindowModal');
    const chatWithUserName = document.getElementById('chatWithUserName');
    const chatMessagesArea = document.getElementById('chatMessagesArea');
    const chatMessageInput = document.getElementById('chatMessageInput');
    const chatSendMessageButton = document.getElementById('chatSendMessageButton');

    let currentConversationId = null;
    let currentChattingWithUserId = null;
    let idUsuarioActual = user_id_ACTUAL; // Asegúrate que esta variable global esté definida
    let messagePollingInterval = null;

    if (openChatButton) {
        openChatButton.addEventListener('click', () => {
            chatFollowersModal.style.display = 'block';
            loadChatContacts(); // Cambiamos el nombre de la función
        });
    }

    // Función para crear un item de usuario para las listas
    function createUserListItem(user) {
        const userItem = document.createElement('div');
        userItem.classList.add('user-item');

        const userInfoContainer = document.createElement('span');
        const userNameSpan = document.createElement('span');
        userNameSpan.textContent = user.nomUs;
        userInfoContainer.appendChild(userNameSpan);

        if (user.unread_from_user_count && user.unread_from_user_count > 0) {
            const unreadDot = document.createElement('span');
            unreadDot.className = 'follower-unread-dot';
            unreadDot.title = `${user.unread_from_user_count} mensajes no leídos de ${user.nomUs}`;
            unreadDot.innerText = `${user.unread_from_user_count}`;
            userInfoContainer.appendChild(unreadDot);
        }
        
        userItem.appendChild(userInfoContainer);

        userItem.addEventListener('click', () => {
            startChatWith(user);
            const dot = userItem.querySelector('.follower-unread-dot');
            if (dot) {
                dot.style.display = 'none'; // Opcional: ocultar inmediatamente
            }
        });
        return userItem;
    }

    function loadChatContacts() { // Función renombrada y modificada
    fetch('../Back/chat_get_seguidos.php') // Llama al PHP modificado
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta de red no fue OK: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Limpiar listas anteriores
            if(chatFollowedList) chatFollowedList.innerHTML = ''; 
            if(chatOthersList) chatOthersList.innerHTML = '';

            if (data.error) {
                if(chatFollowedList) chatFollowedList.innerHTML = `<p>${data.error}</p>`;
                console.error('Error del backend:', data.error);
                return;
            }

            // Poblar lista de seguidos
            if (data.followed && Array.isArray(data.followed)) {
                if (data.followed.length === 0) {
                    if(chatFollowedList) chatFollowedList.innerHTML = '<p>No sigues a nadie o no hay chats activos.</p>';
                } else {
                    data.followed.forEach(user => {
                        if(chatFollowedList) chatFollowedList.appendChild(createUserListItem(user));
                    });
                }
            } else {
                 if(chatFollowedList) chatFollowedList.innerHTML = '<p>No se pudo cargar la lista de seguidos.</p>';
            }

            // Poblar lista de otras conversaciones
            if (data.others && Array.isArray(data.others)) {
                if (data.others.length === 0) {
                    if(chatOthersList) chatOthersList.innerHTML = '<p>No hay otras conversaciones activas.</p>';
                } else {
                    data.others.forEach(user => {
                        if(chatOthersList) chatOthersList.appendChild(createUserListItem(user));
                    });
                }
            } else {
                 if(chatOthersList) chatOthersList.innerHTML = '<p>No se pudo cargar la lista de otras conversaciones.</p>';
            }
        })
        .catch(error => {
            console.error('Error cargando listas de chat:', error);
            if(chatFollowedList) chatFollowedList.innerHTML = '<p>Error al cargar la lista de chat.</p>';
            if(chatOthersList) chatOthersList.innerHTML = ''; // Limpiar si da error también
        });
    }

    function startChatWith(user) {
        currentChattingWithUserId = user.idUsuario;
        if(chatFollowersModal) chatFollowersModal.style.display = 'none'; 
        
        const formData = new FormData();
        formData.append('id_otro_usuario', user.idUsuario);

        fetch('../Back/chat_get_or_create_conversation.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(`Error: ${data.error}`);
                return;
            }
            currentConversationId = data.id_conversacion;
            if(chatWithUserName) chatWithUserName.textContent = `${user.nomUs}`;
            if(chatWindowModal) chatWindowModal.style.display = 'block';
            loadMessages();
            startMessagePolling(); 
        })
        .catch(error => console.error('Error iniciando conversación:', error));
    }

    function loadMessages() {
        if (!currentConversationId || !chatMessagesArea) return;

        fetch(`../Back/chat_get_messages.php?id_conversacion=${currentConversationId}`)
            .then(response => response.json())
            .then(data => {
                chatMessagesArea.innerHTML = ''; 
                if (data.error) {
                    chatMessagesArea.innerHTML = `<p>${data.error}</p>`;
                    return;
                }
                data.forEach(msg => {
                    appendMessageToChatArea(msg);
                });
                scrollToBottom();
            })
            .catch(error => console.error('Error cargando mensajes:', error));
    }
    
    function appendMessageToChatArea(msg) {
        if(!chatMessagesArea) return;
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('chat-message');
        
        if (msg.id_emisor === idUsuarioActual) { 
            messageDiv.classList.add('sent');
        } else {
            messageDiv.classList.add('received');
        }
        
        // El backend ya debería enviar 'fecha_envio' en un formato que new Date() pueda parsear.
        // Si es un string 'YYYY-MM-DD HH:MM:SS' de MySQL, reemplazar espacio con 'T' para compatibilidad.
        const fecha = new Date(msg.fecha_envio.replace(' ', 'T')); 
        const formattedTime = fecha.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

        messageDiv.innerHTML = `
            <span class="sender-name">${escapeHTML(msg.nombre_emisor)}</span>
            <p class="message-content">${escapeHTML(msg.contenido_mensaje)}</p>
            <span class="message-time">${formattedTime}</span>
        `;
        chatMessagesArea.appendChild(messageDiv);
    }

    function scrollToBottom() {
        if(chatMessagesArea) chatMessagesArea.scrollTop = chatMessagesArea.scrollHeight;
    }
    
    function escapeHTML(str) {
        if (typeof str !== 'string') return '';
        var p = document.createElement("p");
        p.appendChild(document.createTextNode(str));
        return p.innerHTML;
    }

    if (chatSendMessageButton) {
        chatSendMessageButton.addEventListener('click', sendMessage);
    }
    if (chatMessageInput) {
        chatMessageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) { // Enviar con Enter, Shift+Enter para nueva línea
                e.preventDefault(); // Prevenir nueva línea si solo es Enter
                sendMessage();
            }
        });
    }

    function sendMessage() {
        if(!chatMessageInput || !currentConversationId) return;
        const messageText = chatMessageInput.value.trim();
        if (!messageText) return;

        const formData = new FormData();
        formData.append('id_conversacion', currentConversationId);
        formData.append('contenido_mensaje', messageText);
        // Asegúrate que el backend chat_send_message.php puede obtener $_SESSION['user_id'] como id_emisor

        fetch('../Back/chat_send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.message_info) { // Asumir que el backend devuelve info del mensaje enviado
                appendMessageToChatArea(data.message_info); // Usar info del backend
                scrollToBottom();
                chatMessageInput.value = ''; 
            } else {
                alert(`Error enviando mensaje: ${data.error || 'Error desconocido'}`);
            }
        })
        .catch(error => console.error('Error enviando mensaje:', error));
    }

    function startMessagePolling() {
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
        }
        messagePollingInterval = setInterval(() => {
            if (currentConversationId && chatWindowModal && chatWindowModal.style.display === 'block') {
                loadMessages();
            }
        }, 5000); 
    }

    function stopMessagePolling() {
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
            messagePollingInterval = null;
        }
    }
    
    // Añadir el span para cerrar el modal de seguidores si no existe
    const seguidoresModalCloseButton = document.querySelector('#chatFollowersModal .close-button');
    if (seguidoresModalCloseButton) {
        seguidoresModalCloseButton.onclick = function() {
            if(chatFollowersModal) chatFollowersModal.style.display = "none";
        }
    }
    
    // Cerrar ventana de chat individual
    window.closeChatWindow = function() { 
        if(chatWindowModal) chatWindowModal.style.display = 'none';
        stopMessagePolling();
        currentConversationId = null;
        currentChattingWithUserId = null;
        if(chatMessagesArea) chatMessagesArea.innerHTML = ''; 
        // Tal vez recargar la lista de contactos para actualizar contadores de no leídos.
        // loadChatContacts(); 
    }
});