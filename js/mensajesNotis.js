document.addEventListener('DOMContentLoaded', function() {
        if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }
    const openChatButton = document.getElementById('openChatButton'); //
    let chatUnreadBadge = null;

    // Crear el elemento del badge dinámicamente y añadirlo al botón de chat
    if (openChatButton) {
        chatUnreadBadge = document.createElement('span');
        chatUnreadBadge.id = 'chatUnreadBadge';
        chatUnreadBadge.className = 'chat-unread-badge';
        openChatButton.appendChild(chatUnreadBadge);
    }

    function fetchUnreadMessagesCount() {
        // Solo ejecutar si el botón y el badge existen
        if (!openChatButton || !chatUnreadBadge) {
     
            return;
        }
   
        if (typeof user_id_ACTUAL === 'undefined' ||user_id_ACTUAL === null) {

            if(chatUnreadBadge) chatUnreadBadge.style.display = 'none'; 
            return;
        }

        fetch('../Back/chat_get_unread_count.php') // Ajusta la ruta si es necesario
            .then(response => {
                if (!response.ok) {
                    throw new Error('Respuesta de red no fue OK para conteo de no leídos: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                if (data.error && data.error !== 'Usuario no autenticado') { // Ignorar error de no autenticado si ya lo manejamos
                    console.warn('Error al obtener conteo de no leídos:', data.error);
                    if(chatUnreadBadge) chatUnreadBadge.style.display = 'none';
                    return;
                }

                if (data.unread_count > 0) {
                    chatUnreadBadge.textContent = data.unread_count;
                    chatUnreadBadge.style.display = 'block'; // Mostrar el badge
                      if ("Notification" in window && Notification.permission === "granted" && document.hidden) {
                        new Notification("Nueva notificación", {
                            body: `Tienes ${data.unread_count} mensajes no leídos.`,
                            icon: "../front/LOGOWEB.jpg" // Puedes personalizar este icono
                        });
                    }
                } else {
                    chatUnreadBadge.style.display = 'none'; // Ocultar el badge si no hay mensajes no leídos
                }
            })
            .catch(error => {
                console.error('Error al solicitar conteo de mensajes no leídos:', error);
                if(chatUnreadBadge) chatUnreadBadge.style.display = 'none'; // Ocultar badge en caso de error de fetch
            });
    }

    // Llamar a la función al cargar la página
    fetchUnreadMessagesCount();

 
    setInterval(fetchUnreadMessagesCount, 30000); 

    // Opcional: Si quieres que el contador se actualice inmediatamente
    // después de cerrar una ventana de chat (donde se leyeron mensajes),
    // podrías llamar a fetchUnreadMessagesCount() desde la función closeChatWindow() en chat.js.
    // Sin embargo, el polling ya se encargará de esto eventualmente.
    // La lógica actual es que cuando abres un chat con alguien, los mensajes se marcan como leídos
    // por `chat_get_messages.php`, y la siguiente encuesta de `chat_get_unread_count.php` reflejará esto.
});