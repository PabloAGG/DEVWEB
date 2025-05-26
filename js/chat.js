document.addEventListener('DOMContentLoaded', function() {
    const openChatButton = document.getElementById('openChatButton');
    const chatFollowersModal = document.getElementById('chatFollowersModal');
    const chatFollowersList = document.getElementById('chatFollowersList');
    
    const chatWindowModal = document.getElementById('chatWindowModal');
    const chatWithUserName = document.getElementById('chatWithUserName');
    const chatMessagesArea = document.getElementById('chatMessagesArea');
    const chatMessageInput = document.getElementById('chatMessageInput');
    const chatSendMessageButton = document.getElementById('chatSendMessageButton');

    let currentConversationId = null;
    let currentChattingWithUserId = null;
    let idUsuarioActual = user_id_ACTUAL; // Necesitarás obtener el ID del usuario actual, quizás desde una variable global PHP o un data-attribute
    let messagePollingInterval = null;


    // const userInfoElement = document.getElementById('user-info'); 
    // if (userInfoElement && userInfoElement.dataset.userid) {
    //     idUsuarioActual = parseInt(userInfoElement.dataset.userid);
    // } else {
    //     // Intenta obtenerlo de una variable global si la configuras en tu PHP
    //     if (typeof ID_USUARIO_ACTUAL !== 'undefined') {
    //         idUsuarioActual = ID_USUARIO_ACTUAL;
    //     } else {
    //         console.error("No se pudo obtener el ID del usuario actual.");
    //         // Podrías deshabilitar el botón de chat si no hay ID
    //         if(openChatButton) openChatButton.disabled = true;
    //         return; // Salir si no hay ID de usuario
    //     }
    // }


    if (openChatButton) {
        openChatButton.addEventListener('click', () => {
            chatFollowersModal.style.display = 'block';
            loadFollowers();
        });
    }

    function loadFollowers() {
    fetch('../Back/chat_get_seguidos.php') // Ruta a tu script modificado
        .then(response => {
            if (!response.ok) {
                throw new Error('Respuesta de red no fue OK para la lista de seguidores: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const chatFollowersList = document.getElementById('chatFollowersList');
            chatFollowersList.innerHTML = ''; // Limpiar lista anterior

            if (data.error) {
                chatFollowersList.innerHTML = `<p>${data.error}</p>`;
                return;
            }
            if (!Array.isArray(data) || data.length === 0) {
                chatFollowersList.innerHTML = '<p>No sigues a nadie todavía.</p>';
                return;
            }

            data.forEach(user => {
                const userItem = document.createElement('div');
                userItem.classList.add('user-item'); // Clase para estilos del item de usuario

                // Contenedor para el nombre y el posible punto de notificación
                const userInfoContainer = document.createElement('span');
                
                const userNameSpan = document.createElement('span');
                userNameSpan.textContent = user.nomUs; // Ya no es necesario escapeHTML si el backend asegura UTF-8 y el contenido es texto plano
                userInfoContainer.appendChild(userNameSpan);

                // Añadir indicador si hay mensajes no leídos de este usuario
                if (user.unread_from_user_count && user.unread_from_user_count > 0) {
                    const unreadDot = document.createElement('span');
                    unreadDot.className = 'follower-unread-dot'; // Nueva clase CSS para el punto
                    unreadDot.title = `${user.unread_from_user_count} mensajes no leídos de ${user.nomUs}`;
                    unreadDot.innerText=`${user.unread_from_user_count}`;
                    userInfoContainer.appendChild(unreadDot);
                }
                
                userItem.appendChild(userInfoContainer);

                userItem.addEventListener('click', () => {
                    startChatWith(user);
                    // Opcional: Ocultar el punto inmediatamente al hacer clic,
                    // aunque se actualizará correctamente la próxima vez que se abra la lista.
                    const dot = userItem.querySelector('.follower-unread-dot');
                    if (dot) {
                        dot.style.display = 'none';
                    }
                });
                chatFollowersList.appendChild(userItem);
            });
        })
        .catch(error => {
            console.error('Error cargando seguidores con detalle de no leídos:', error);
            const chatFollowersList = document.getElementById('chatFollowersList');
            if (chatFollowersList) { // Asegurarse de que el elemento existe
                 chatFollowersList.innerHTML = '<p>Error al cargar la lista de seguidos.</p>';
            }
        });
}

    function startChatWith(user) {
        currentChattingWithUserId = user.idUsuario;
        chatFollowersModal.style.display = 'none'; // Cerrar modal de seguidores
        
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
            chatWithUserName.textContent = `${user.nomUs}`;
            chatWindowModal.style.display = 'block';
            loadMessages();
            startMessagePolling(); // Iniciar polling para nuevos mensajes
        })
        .catch(error => console.error('Error iniciando conversación:', error));
    }

    function loadMessages() {
        if (!currentConversationId) return;

        fetch(`../Back/chat_get_messages.php?id_conversacion=${currentConversationId}`)
            .then(response => response.json())
            .then(data => {
                chatMessagesArea.innerHTML = ''; // Limpiar mensajes anteriores
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
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('chat-message');
        // Necesitas saber quién es el usuario actual para aplicar la clase 'sent' o 'received'
        // Asumimos que tienes 'idUsuarioActual' disponible en este scope
        if (msg.id_emisor === idUsuarioActual) { 
            messageDiv.classList.add('sent');
        } else {
            messageDiv.classList.add('received');
        }
        
        // Formatear la fecha
        const fecha = new Date(msg.fecha_envio.replace(' ', 'T') ); // Asegurar que se interprete como UTC si viene de MySQL TIMESTAMP
        const formattedTime = fecha.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });


        messageDiv.innerHTML = `
            <span class="sender-name">${msg.nombre_emisor}</span>
            <p class="message-content">${escapeHTML(msg.contenido_mensaje)}</p>
            <span class="message-time">${formattedTime}</span>
        `;
        chatMessagesArea.appendChild(messageDiv);
    }

    function scrollToBottom() {
        chatMessagesArea.scrollTop = chatMessagesArea.scrollHeight;
    }
    
    function escapeHTML(str) {
        var p = document.createElement("p");
        p.appendChild(document.createTextNode(str));
        return p.innerHTML;
    }


    if (chatSendMessageButton) {
        chatSendMessageButton.addEventListener('click', sendMessage);
    }
    if (chatMessageInput) {
        chatMessageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    function sendMessage() {
        const messageText = chatMessageInput.value.trim();
        if (!messageText || !currentConversationId) return;

        const formData = new FormData();
        formData.append('id_conversacion', currentConversationId);
        formData.append('contenido_mensaje', messageText);

        fetch('../Back/chat_send_message.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optimistic update: add message to UI immediately
                 const sentMessage = {
                    id_emisor: idUsuarioActual, // Asume que tienes idUsuarioActual
                    nombre_emisor: 'Tú', // O el nombre del usuario actual
                    contenido_mensaje: messageText,
                    fecha_envio: new Date().toISOString().slice(0, 19).replace('T', ' ') // Simula la fecha
                };
                appendMessageToChatArea(sentMessage);
                scrollToBottom();
                chatMessageInput.value = ''; // Limpiar input
                // No es necesario llamar a loadMessages() aquí si haces polling,
                // pero si no, podrías llamarlo para asegurar sincronización.
            } else {
                alert(`Error enviando mensaje: ${data.error || 'Error desconocido'}`);
            }
        })
        .catch(error => console.error('Error enviando mensaje:', error));
    }

    // Polling para nuevos mensajes (forma simple de "tiempo real")
    function startMessagePolling() {
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
        }
        messagePollingInterval = setInterval(() => {
            if (currentConversationId && document.getElementById('chatWindowModal').style.display === 'block') {
                // Solo carga si la ventana de chat está visible y hay una conversación activa
                loadMessages();
            }
        }, 5000); // Cada 5 segundos
    }

    function stopMessagePolling() {
        if (messagePollingInterval) {
            clearInterval(messagePollingInterval);
            messagePollingInterval = null;
        }
    }
    
    // Función para cerrar la ventana de chat y detener el polling
    window.closeChatWindow = function() { // Hacerla global para el onclick del HTML
        chatWindowModal.style.display = 'none';
        stopMessagePolling();
        currentConversationId = null;
        currentChattingWithUserId = null;
        chatMessagesArea.innerHTML = ''; // Limpiar área de mensajes
    }


});