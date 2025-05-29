document.addEventListener("DOMContentLoaded", function () {
    // Ocultar al inicio
    $('#lista-notificaciones').hide();
    $('#contador-notificaciones').hide(); // Ocultar contador si está en 0

    // Cargar notificaciones y contador al inicio
    cargarNotificaciones();
    actualizarContadorNotificaciones();

    // Actualizar contador periódicamente
    setInterval(actualizarContadorNotificaciones, 15000); // Cada 15 segundos

    // Toggle de la lista de notificaciones al hacer clic en el icono
    $('#btn-notificaciones').on('click', function (e) {
        e.stopPropagation(); // Evita que el clic se propague y cierre la lista inmediatamente
        $('#lista-notificaciones').toggle();
        if ($('#lista-notificaciones').is(':visible')) {
            cargarNotificaciones(); // Recarga la lista para reflejar posibles cambios de estado
        }
    });

    // Cierra la lista de notificaciones si se hace clic fuera de ella
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.notificaciones').length) {
            $('#lista-notificaciones').hide();
        }
    });

    // Manejar clic en "Marcar como leída" (delegación de eventos)
    $('#lista-notificaciones').on('click', '.marcar-leida', async function (e) {
        e.preventDefault(); // Previene el comportamiento por defecto del enlace
        e.stopPropagation(); // Evita que el clic se propague
        const idNotificacion = $(this).data('idnotificacion');
        const notificacionItemDiv = $(this).closest('.notificacion-item, .notificacion-clickable-follow');

        // Asegúrate de que csrf_token está disponible globalmente en JS o es manejado de otra forma segura.
        // El tag PHP <?php echo $_SESSION["csrf_token"]; ?> no se ejecutará en un archivo .js estático.
        // Debería ser reemplazado por una variable JS global si se setea en el HTML/PHP,
        // o el backend debe manejar la validación CSRF de otra manera (ej. tokens en cookies).
        const csrfToken = window.csrf_token || ''; // Ejemplo: obtener de una variable global

        try {
            const response = await fetch('../Back/marcar_notificacion_leida.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    'idNotificacion': idNotificacion,
                    'csrf_token': csrfToken
                })
            });
            const data = await response.json();

            if (data.success) {
                notificacionItemDiv.removeClass('notificacion-no-leida').addClass('notificacion-leida');
                $(this).remove(); // Eliminar el enlace "Marcar como leída"
                actualizarContadorNotificaciones();
            } else {
                console.error('Error al marcar notificación como leída:', data.message);
            }
        } catch (error) {
            console.error('Error en la petición AJAX para marcar como leída:', error);
        }
    });

    // Manejar clic en notificaciones de tipo 'follow' para redirigir y marcar como leída
    $('#lista-notificaciones').on('click', '.notificacion-clickable-follow', async function(e) {
        // Evita que el clic en el enlace "Marcar como leída" dentro de este item active esta lógica también.
        if ($(e.target).hasClass('marcar-leida') || $(e.target).closest('.marcar-leida').length) {
            return;
        }
        e.stopPropagation();

        const idNotificacion = $(this).data('idnotificacion');
        const href = $(this).data('href');
        const notificacionItemDiv = $(this); // El div en sí

        // Asegúrate de que csrf_token está disponible. (Ver comentario anterior)
        const csrfToken = window.csrf_token || '';

        if (notificacionItemDiv.hasClass('notificacion-no-leida') && idNotificacion) {
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
                    notificacionItemDiv.removeClass('notificacion-no-leida').addClass('notificacion-leida');
                    notificacionItemDiv.find('.marcar-leida').remove();
                    actualizarContadorNotificaciones();
                } else {
                    console.error('Error al marcar notificación como leída (clic en follow):', data.message);
                }
            } catch (error) {
                console.error('Error en la petición AJAX para marcar como leída (clic en follow):', error);
            } finally {
                // Navegar después de intentar marcar como leída, incluso si falló el marcado.
                if (href && href !== '#') {
                    window.location.href = href;
                }
            }
        } else {
            // Si ya está leída o no hay ID de notificación, simplemente navega si hay un href válido.
            if (href && href !== '#') {
                window.location.href = href;
            }
        }
    });

}); // Fin de DOMContentLoaded


function cargarNotificaciones() {
    if ("Notification" in window && Notification.permission === "default") {
        Notification.requestPermission();
    }

    $.ajax({
        url: '../Back/obtener_notificaciones.php',
        type: 'GET',
        dataType: 'json',
        success: function (notificaciones) {
            const listaHtml = $('#lista-notificaciones');
            listaHtml.empty();

            if (notificaciones.length === 0) {
                listaHtml.append('<p class="pnotis">No tienes notificaciones nuevas.</p>');
            } else {
                notificaciones.forEach(notificacion => {
                    const itemClass = notificacion.leida == '1' ? 'notificacion-item notificacion-leida' : 'notificacion-item notificacion-no-leida';
                    const idNotif = notificacion.idNotificacion;
                    const idUsuarioEmisor = notificacion.idUsuarioEmite;
                    const idPublicacion = notificacion.idPublicacion;

                    let iconoHtml = '';
                    // IMPORTANTE: Para el problema de "nombre de usuario duplicado" en notificaciones de 'follow':
                    // Asegúrate de que el campo 'mensaje' que proviene de '../Back/obtener_notificaciones.php'
                    // para las notificaciones de tipo 'follow' contenga el nombre de usuario UNA SOLA VEZ.
                    // Ejemplo CORRETO: "UsuarioX ha comenzado a seguirte."
                    // Ejemplo INCORRECTO: "UsuarioX UsuarioX ha comenzado a seguirte."
                    // El JavaScript frontend simplemente mostrará el 'mensaje' tal como lo recibe del backend.
                    let mensajePrincipal = htmlspecialchars(notificacion.mensaje); // Este mensaje viene del backend
                    let enlacePrincipalDestino = '#'; // Usado por tipos no 'follow' para su <a> tag o por 'follow' para data-href

                    switch (notificacion.tipo) {
                        case 'like':
                            iconoHtml = '<i class="fa-solid fa-thumbs-up fa-fw"></i> ';
                            if (idPublicacion) {
                                enlacePrincipalDestino = `publicacion.php?id=${idPublicacion}&notif_id=${idNotif}`;
                            }
                            break;
                        case 'comentario':
                            iconoHtml = '<i class="fa-solid fa-comment fa-fw"></i> ';
                            if (idPublicacion) {
                                enlacePrincipalDestino = `publicacion.php?id=${idPublicacion}&notif_id=${idNotif}`;
                            }
                            break;
                        case 'follow':
                            iconoHtml = '<i class="fa-solid fa-user-plus fa-fw"></i> ';
                            if (idUsuarioEmisor) {
                                // Este enlace se usará en data-href para el div clickeable
                                enlacePrincipalDestino = `PerfilExt.php?id=${idUsuarioEmisor}&notif_id=${idNotif}`;
                            }
                            break;
                        default:
                            iconoHtml = '<i class="fa-solid fa-bell fa-fw"></i> ';
                            break;
                    }

                    if ("Notification" in window && Notification.permission === "granted" && notificacion.leida == '0' && document.hidden) {
                        new Notification("DEVWEB", {
                            body: notificacion.mensaje, // Usar el mensaje original para la notificación de escritorio
                            icon: "../front/LOGOWEB.jpg" 
                        });
                    }
                    
                    const fechaFormateada = htmlspecialchars(notificacion.fechaCreacion); // Asumiendo que viene formateada o es aceptable así
                    const linkLeidaHtml = notificacion.leida == '0' ? `<a href="#" class="marcar-leida" data-idnotificacion="${idNotif}">Marcar como leída</a>` : '';
                    
                    // Construye el contenido textual de la notificación
                    const contenidoTextoNotificacion = `${iconoHtml} ${mensajePrincipal} <small>(${fechaFormateada})</small>`;
                    let itemHtml;

                    if (notificacion.tipo === 'follow' && idUsuarioEmisor && enlacePrincipalDestino !== '#') {
                        // Para 'follow', el div completo es clickeable
                        itemHtml = `
                            <div class="${itemClass} notificacion-clickable-follow" 
                                 data-idnotificacion="${idNotif}" 
                                 data-href="${enlacePrincipalDestino}">
                                <span class="notificacion-content-text">${contenidoTextoNotificacion}</span>
                                ${linkLeidaHtml}
                            </div>`;
                    } else {
                        // Para otros tipos, envuelve el contenido en un <a> si hay un enlace de destino
                        let contenidoConEnlaceHtml;
                        if (enlacePrincipalDestino !== '#') {
                            contenidoConEnlaceHtml = `<a href="${enlacePrincipalDestino}" class="notificacion-link-contenido">${contenidoTextoNotificacion}</a>`;
                        } else {
                            // Si no hay enlace, solo el texto
                            contenidoConEnlaceHtml = `<span class="notificacion-content-text">${contenidoTextoNotificacion}</span>`;
                        }
                        itemHtml = `
                            <div class="${itemClass}" data-idnotificacion="${idNotif}">
                                ${contenidoConEnlaceHtml}
                                ${linkLeidaHtml}
                            </div>`;
                    }
                    listaHtml.append(itemHtml);
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al cargar notificaciones:", status, error, xhr.responseText);
            $('#lista-notificaciones').html('<p class="pnotis">Error al cargar las notificaciones.</p>');
        }
    });
}

function actualizarContadorNotificaciones() {
    $.ajax({
        url: '../Back/obtener_cantidad_no_leidas.php',
        type: 'GET',
        success: function (cantidad) {
            const contador = $('#contador-notificaciones');
            const numCantidad = parseInt(cantidad);

            contador.text(numCantidad);

            if (numCantidad > 0) {
                contador.show();
            } else {
                contador.hide();
            }
        },
        error: function () {
            console.error("Error al obtener contador de notificaciones.");
        }
    });
}

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
    return str.replace(/[&<>"']/g, function (m) { return map[m]; });
}