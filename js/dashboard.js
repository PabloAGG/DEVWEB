document.addEventListener("DOMContentLoaded", function () {
    // --- Manejo de mensajes de URL (existente) ---
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const succes = urlParams.get('succes'); // Considera cambiar 'succes' a 'success' en el backend para consistencia

    if (error === 'formato_invalido') {
        alert('Formato de contenido inválido.');
    } else if (error === 'fallo_crear') {
        alert('Error al crear la publicación, inténtalo más tarde.');
    }
    if (succes === 'publicacion_creada') {
        alert('Publicación creada correctamente.');
    }

    // --- Validación del formulario de publicación (existente) ---
    // Asume que tu formulario tiene name="titleP", name="descP", name="select" para los campos
    const formPublicacion = document.querySelector("form#formPublicacion"); // Sé más específico con el selector del formulario
    const fileInput = document.getElementById("ffoto");

    if (formPublicacion) {
        formPublicacion.addEventListener("submit", function (e) {
            const title = formPublicacion.titleP; // Accede a los campos por su 'name'
            const desc = formPublicacion.descP;
            const categoria = formPublicacion.select; // Asumiendo que el select tiene name="select"

            let isValid = true;

            if (!title || !title.value.trim()) {
                alert("Por favor, ingresa un título."); // O usa setCustomValidity y reportValidity
                // title?.setCustomValidity("Por favor, ingresa un título.");
                // title?.reportValidity();
                isValid = false;
            } else {
                // title?.setCustomValidity("");
            }

            if (!desc || !desc.value.trim()) {
                alert("La descripción не может быть пустой.");
                // desc?.setCustomValidity("La descripción no puede estar vacía.");
                // desc?.reportValidity();
                isValid = false;
            } else {
                // desc?.setCustomValidity("");
            }

            if (!categoria || !categoria.value) {
                alert("Debes seleccionar una categoría.");
                // categoria?.setCustomValidity("Debes seleccionar una categoría.");
                // categoria?.reportValidity();
                isValid = false;
            } else {
                // categoria?.setCustomValidity("");
            }

            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const validTypes = ["image/jpeg", "image/png", "video/mp4"];
                const maxSize = 10 * 1024 * 1024; // 10 MB

                if (!validTypes.includes(file.type)) {
                    alert("Solo se permiten imágenes .jpg/.png o videos .mp4");
                    // fileInput.setCustomValidity("Solo se permiten imágenes .jpg/.png o videos .mp4");
                    // fileInput.reportValidity();
                    isValid = false;
                } else {
                    // fileInput.setCustomValidity("");
                }

                if (file.size > maxSize) {
                    alert("El archivo no puede superar los 10 MB.");
                    // fileInput.setCustomValidity("El archivo no puede superar los 10 MB.");
                    // fileInput.reportValidity();
                    isValid = false;
                } else {
                    // fileInput.setCustomValidity("");
                }
            }
            
            if (!isValid) {
                e.preventDefault(); // Detener el envío del formulario si hay errores
            }
        });
    } else {
        console.warn("Formulario de publicación ('form#formPublicacion') no encontrado. La validación no se activará.");
    }


    // --- Funcionalidad de Notificaciones ---
    $('#lista-notificaciones').hide();
    $('#contador-notificaciones').hide();

    cargarNotificaciones();
    actualizarContadorNotificaciones();

    setInterval(actualizarContadorNotificaciones, 15000);

    $('#btn-notificaciones').on('click', function(e) {
        e.stopPropagation();
        $('#lista-notificaciones').toggle();
        // Si la lista es visible y hay notificaciones, es un buen momento para recargarlas
        // o al menos actualizar sus estados si es necesario.
        if ($('#lista-notificaciones').is(':visible')) {
            cargarNotificaciones();
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.notificaciones').length) {
            $('#lista-notificaciones').hide();
        }
    });

    $('#lista-notificaciones').on('click', '.marcar-leida', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        const idNotificacion = $(this).data('idnotificacion');
        const notificacionItemDiv = $(this).closest('.notificacion-item'); // Div padre del item
        const csrfToken = window.csrf_token || ''; // Obtener de variable global

        try {
            const response = await fetch('../Back/marcar_notificacion_leida.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 
                    'idNotificacion': idNotificacion,
                    'csrf_token': csrfToken // Enviar CSRF token
                })
            });
            const data = await response.json();
            if (data.success) {
                notificacionItemDiv.removeClass('notificacion-no-leida').addClass('notificacion-leida');
                $(this).remove(); // Eliminar el enlace "Marcar como leída"
                actualizarContadorNotificaciones();
            } else {
                console.error('Error al marcar notificación como leída:', data.message);
                alert('Error al marcar notificación como leída: ' + (data.message || 'Error desconocido'));
            }
        } catch (error) {
            console.error('Error en la petición AJAX para marcar como leída:', error);
            alert('Error de comunicación con el servidor al marcar como leída.');
        }
    });

    // NUEVO MANEJADOR DE CLIC PARA ITEMS DE NOTIFICACIÓN (redirige y puede marcar como leída)
    $('#lista-notificaciones').on('click', '.notificacion-item', async function(e) {
        // No hacer nada si se hizo clic en el enlace "Marcar como leída"
        if ($(e.target).hasClass('marcar-leida') || $(e.target).closest('.marcar-leida').length) {
            return;
        }
        e.stopPropagation();

        const idNotificacion = $(this).data('idnotificacion');
        const redirectUrl = $(this).data('redirect-url'); // URL a la que redirigir
        const isUnread = $(this).hasClass('notificacion-no-leida');
        const csrfToken = window.csrf_token || '';

        if (isUnread && idNotificacion) {
            try {
                const response = await fetch('../Back/marcar_notificacion_leida.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ 
                        'idNotificacion': idNotificacion,
                        'csrf_token': csrfToken 
                    })
                });
                const data = await response.json();
                if (data.success) {
                    $(this).removeClass('notificacion-no-leida').addClass('notificacion-leida');
                    $(this).find('.marcar-leida').remove();
                    actualizarContadorNotificaciones();
                } else {
                    console.error('Error al marcar notificación como leída al hacer clic en item:', data.message);
                }
            } catch (error) {
                console.error('Error en AJAX al marcar como leída (clic en item):', error);
            } finally {
                if (redirectUrl && redirectUrl !== '#') {
                    window.location.href = redirectUrl;
                }
            }
        } else {
            // Si ya está leída o no hay ID, simplemente redirigir si hay URL
            if (redirectUrl && redirectUrl !== '#') {
                window.location.href = redirectUrl;
            }
        }
    });
}); // Fin de DOMContentLoaded


function cargarNotificaciones() {
    $.ajax({
        url: '../Back/obtener_notificaciones.php',
        type: 'GET',
        dataType: 'json',
        success: function(notificaciones) {
            const listaHtml = $('#lista-notificaciones');
            listaHtml.empty();

            if (notificaciones.length === 0) {
                listaHtml.append('<p class="pnotis">No tienes notificaciones nuevas.</p>');
            } else {
                notificaciones.forEach(notificacion => {
                    const itemClass = notificacion.leida == '1' ? 'notificacion-item notificacion-leida' : 'notificacion-item notificacion-no-leida';
                    const idNotif = notificacion.idNotificacion;
                    const idUsuarioEmite = notificacion.idUsuarioEmite; // Para enlace de 'follow'
                    const idPublicacion = notificacion.idPublicacion; // Para enlace de 'like'/'comentario'
                    
                    let iconoHtml = '';
                    let mensajeMostrado = ''; // El mensaje que se va a mostrar
                    let redirectUrl = '#'; // URL a la que el item completo redirigirá

                    switch (notificacion.tipo) {
                        case 'like':
                            iconoHtml = '<i class="fa-solid fa-thumbs-up fa-fw"></i> ';
                            // Para 'like', el mensaje es "Usuario X le dio me gusta..."
                            mensajeMostrado = htmlspecialchars(notificacion.usuarioEmiteNombre) + " le dio me gusta a tu publicación.";
                            if (idPublicacion) {
                                redirectUrl = `publicacion.php?id=${idPublicacion}&notif_id=${idNotif}`;
                            }
                            break;
                        case 'comentario':
                            iconoHtml = '<i class="fa-solid fa-comment fa-fw"></i> ';
                            // Para 'comentario', el mensaje es "Usuario X comentó..."
                            mensajeMostrado = htmlspecialchars(notificacion.usuarioEmiteNombre) + " comentó en tu publicación.";
                            if (idPublicacion) {
                                redirectUrl = `publicacion.php?id=${idPublicacion}&notif_id=${idNotif}`;
                            }
                            break;
                        case 'follow':
                            iconoHtml = '<i class="fa-solid fa-user-plus fa-fw"></i> ';
                            // Para 'follow', el mensaje ya viene completo desde el backend como "UsuarioX ha comenzado a seguirte."
                            // Tu script de INSERT ya lo guarda bien.
                            mensajeMostrado = htmlspecialchars(notificacion.mensaje);
                            if (idUsuarioEmite) {
                                redirectUrl = `PerfilExt.php?id=${idUsuarioEmite}&notif_id=${idNotif}`;
                            }
                            break;
                        default:
                            iconoHtml = '<i class="fa-solid fa-bell fa-fw"></i> ';
                            // Para tipos desconocidos, mostrar el mensaje del backend directamente.
                            // Esto también evita la duplicación si 'mensaje' ya es completo.
                            mensajeMostrado = htmlspecialchars(notificacion.mensaje);
                            // Considerar si estos tipos deben tener un redirectUrl o no.
                            break;
                    }

                    // Notificación de escritorio (si el permiso está concedido y la pestaña no está activa)
                    if ("Notification" in window && Notification.permission === "granted" && notificacion.leida == '0' && document.hidden) {
                        new Notification("DEVWEB", {
                            body: notificacion.mensaje, // Usar el mensaje original para la notificación de escritorio
                            icon: "../front/LOGOWEB.jpg" // Asegúrate que esta ruta es correcta
                        });
                    }

                    const fechaFormateada = htmlspecialchars(notificacion.fechaCreacion);
                    const linkLeidaHtml = notificacion.leida == '1' ? '' : `<a href="#" class="marcar-leida" data-idnotificacion="${idNotif}">Marcar como leída</a>`;
                    
                    // Construir el contenido del item
                    // Añadimos data-redirect-url al div principal
                    const itemHtml = `
                        <div class="${itemClass}" data-idnotificacion="${idNotif}" data-redirect-url="${redirectUrl}">
                            ${iconoHtml}
                            <span class="notificacion-mensaje-principal">${mensajeMostrado}</span>
                            <small class="notificacion-fecha">(${fechaFormateada})</small>
                            ${linkLeidaHtml}
                        </div>`;
                    
                    listaHtml.append(itemHtml);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar notificaciones:", status, error, xhr.responseText);
            $('#lista-notificaciones').html('<p class="pnotis">Error al cargar las notificaciones.</p>');
        }
    });
}

function actualizarContadorNotificaciones() {
    $.ajax({
        url: '../Back/obtener_cantidad_no_leidas.php',
        type: 'GET',
        success: function(cantidad) {
            const contador = $('#contador-notificaciones');
            const numCantidad = parseInt(cantidad);
            contador.text(numCantidad);
            if (numCantidad > 0) {
                contador.show();
            } else {
                contador.hide();
            }
        },
        error: function() {
            console.error("Error al obtener contador de notificaciones.");
            // $('#contador-notificaciones').text('!').show(); // Opcional: Indicar error
        }
    });
}

// La función marcarNotificacionLeidaAjax ya no es necesaria aquí si el manejador de .notificacion-item la cubre.
// La eliminamos para evitar duplicidad, ya que el manejador de .marcar-leida y .notificacion-item hacen el fetch.

function htmlspecialchars(str) {
    if (typeof str !== 'string') {
        return str === null || typeof str === 'undefined' ? '' : String(str);
    }
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return str.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function toggleForm() {
    const form = document.getElementById("EspPub");
    if (form) {
        form.style.display = (form.style.display === "flex") ? "none" : "flex";
    } else {
        console.error("Elemento con id 'EspPub' no encontrado.");
    }
}