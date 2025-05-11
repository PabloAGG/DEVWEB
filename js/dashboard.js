document.addEventListener("DOMContentLoaded", function () {
    // --- Manejo de mensajes de URL (existente) ---
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const succes = urlParams.get('succes'); // Corregido 'succes' a 'success' si es el nombre correcto esperado

    if (error === 'formato_invalido') {
        alert('Formato de contenido inválido.'); // Mensaje más claro
    } else if (error === 'fallo_crear') {
        alert('Error al crear la publicación, inténtalo más tarde.'); // Mensaje más claro
    }
    if (succes === 'publicacion_creada') { // Usar 'success' consistente
        alert('Publicación creada correctamente.'); // Mensaje más claro
    }

    // --- Validación del formulario de publicación (existente) ---
    // Se asume que hay un formulario con id="formPublicacion" o similar,
    // o que 'form' siempre selecciona el formulario correcto en la página.
    // Si hay múltiples formularios, considera usar un selector más específico (ej. '#id-de-tu-formulario-publicacion').
     const form = document.querySelector("form"); // asegúrate de poner el <form> real
     const fileInput = document.getElementById("ffoto");

     if (form) { // Añadir verificación para asegurarse de que el formulario existe
          form.addEventListener("submit", function (e) {
               const title = form.titleP;
               const desc = form.descP;
               const categoria = form.select;

               if (!title.value.trim()) {
                   title.setCustomValidity("Por favor, ingresa un título.");
                   e.preventDefault();
                   return;
               } else {
                   title.setCustomValidity(""); // Limpiar error si es válido
               }

               if (!desc.value.trim()) {
                  desc.setCustomValidity("La descripción no puede estar vacía.");
                   e.preventDefault();
                   return;
               } else {
                   desc.setCustomValidity(""); // Limpiar error si es válido
               }

               if (!categoria.value) {
                  categoria.setCustomValidity("Debes seleccionar una categoría.");
                   e.preventDefault();
                   return;
               } else {
                   categoria.setCustomValidity(""); // Limpiar error si es válido
               }

               // Validación de archivo (si existe)
               if (fileInput && fileInput.files.length > 0) { // Verificar si fileInput existe
                   const file = fileInput.files[0];
                   const validTypes = ["image/jpeg", "image/png", "video/mp4"];
                   // Corregido tamaño máximo a 10 MB, estaba 64MB por error
                   const maxSize = 10 * 1024 * 1024; // 10 MB

                   if (!validTypes.includes(file.type)) {
                      fileInput.setCustomValidity("Solo se permiten imágenes .jpg/.png o videos .mp4");
                       e.preventDefault();
                       return;
                   }

                   if (file.size > maxSize) {
                       fileInput.setCustomValidity("El archivo no puede superar los 10 MB.");
                       e.preventDefault();
                       return;
                   }
                    // Limpiar custom validity si el archivo es válido
                   fileInput.setCustomValidity("");
               } else if (fileInput) {
                    // Si el campo de archivo existe pero no hay archivo seleccionado, limpiar cualquier error previo
                   fileInput.setCustomValidity("");
               }
           });
     } else {
         console.error("Formulario de publicación no encontrado.");
     }


    // --- Funcionalidad de Notificaciones (Modificada) ---
    // Asegúrate de que jQuery esté cargado antes de este script.
    // El HTML para #btn-notificaciones, #contador-notificaciones y #lista-notificaciones
    // debe existir en tu dashboard.php (ver recomendaciones de HTML previas).

    // Ocultar al inicio
    $('#lista-notificaciones').hide();
    $('#contador-notificaciones').hide(); // Ocultar contador si está en 0

    // Cargar notificaciones y contador al inicio
    cargarNotificaciones();
    actualizarContadorNotificaciones();

    // Opcional: Polling para notificaciones nuevas (ajusta el intervalo)
    // Verificar contador cada 15 segundos
    setInterval(actualizarContadorNotificaciones, 15000);
     // Si quieres recargar la lista completa periódicamente, úsalo con precaución
     // setInterval(cargarNotificaciones, 30000); // Ejemplo: recargar lista cada 30 segundos

    // Toggle de la lista de notificaciones al hacer clic en el icono
    $('#btn-notificaciones').on('click', function(e) {
        e.stopPropagation(); // Evita que el clic se propague y cierre la lista inmediatamente
        $('#lista-notificaciones').toggle();
         // Opcional: Si la lista se abre, marca todas las visibles como leídas en la UI
        // Esto no las marca en la DB, solo cambia la apariencia hasta la próxima carga
        // $('#lista-notificaciones .notificacion-no-leida').removeClass('notificacion-no-leida').addClass('notificacion-leida');
    });

    // Cierra la lista de notificaciones si se hace clic fuera de ella
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.notificaciones').length) {
            $('#lista-notificaciones').hide();
        }
    });

    // Manejar clic en "Marcar como leída" (delegación de eventos)
    // Usamos .on() para manejar clicks en elementos que se añaden dinámicamente
    $('#lista-notificaciones').on('click', '.marcar-leida', async function(e) {
        e.preventDefault(); // Previene el comportamiento por defecto del enlace
        e.stopPropagation(); // Evita que el clic se propague al contenedor del item
        const idNotificacion = $(this).data('idnotificacion'); // Obtener el ID del data-attribute
        const notificacionItem = $(this).closest('.notificacion-item');

        try {
            const response = await fetch('../Back/marcar_notificacion_leida.php', {
                method: 'POST', // o 'GET', según cómo implementes marcar_notificacion_leida.php
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded' // Si envías por POST
                },
                body: new URLSearchParams({ // Si envías por POST
                    'idNotificacion': idNotificacion
                })
            });

            const data = await response.json();

            if (data.success) {
                // Actualizar visualmente la notificación en la lista
                notificacionItem.removeClass('notificacion-no-leida').addClass('notificacion-leida');
                $(this).remove(); // Eliminar el enlace "Marcar como leída"

                // Actualizar el contador de notificaciones no leídas
                actualizarContadorNotificaciones();
            } else {
                console.error('Error al marcar notificación como leída:', data.message);
                alert('Error al marcar notificación como leída: ' + data.message); // Feedback al usuario
            }
        } catch (error) {
            console.error('Error en la petición AJAX para marcar como leída:', error);
            alert('Error de comunicación con el servidor al marcar como leída.');
        }
    });

     // Delegación de eventos para hacer clic en cualquier parte del item de notificación
     // excepto en el enlace "Marcar como leída"
     $('#lista-notificaciones').on('click', '.notificacion-item', function(e) {
         // Verifica si el clic no fue en el enlace "Marcar como leída"
         if (!$(e.target).hasClass('marcar-leida')) {
             const publiId = $(this).data('idpublicacion'); // Obtener el ID de la publicación asociado
             const idNotificacion = $(this).data('idnotificacion'); // Obtener el ID de la notificación

             if (publiId) {
                  // Opcional: Marcar como leída ANTES de redirigir (vía AJAX)
                  // Solo si la notificación no está ya marcada como leída
                  if (!$(this).hasClass('notificacion-leida')) {
                      marcarNotificacionLeidaAjax(idNotificacion); // Llama a la función AJAX
                  }
                 // Redirigir a la publicación relevante
                 window.location.href = 'publicacion.php?id=' + publiId;
             }
         }
     });


}); // Fin de DOMContentLoaded

// --- Funciones de Notificaciones (Modificadas) ---

// Función para cargar la lista de notificaciones (ahora manejando JSON y construyendo mensaje)
function cargarNotificaciones() {
    $.ajax({
        url: '../Back/obtener_notificaciones.php',
        type: 'GET',
        dataType: 'json', // Esperamos que el servidor devuelva JSON
        success: function(notificaciones) {
            const listaHtml = $('#lista-notificaciones');
            listaHtml.empty(); // Limpiar lista actual

            if (notificaciones.length === 0) {
                listaHtml.append('<p>No tienes notificaciones.</p>');
            } else {
                notificaciones.forEach(notificacion => {
                    // Construir el HTML para cada notificación
                    const itemClass = notificacion.leida ? 'notificacion-item notificacion-leida' : 'notificacion-item notificacion-no-leida';
                    // Incluir data-attributes para ID de notificación y publicación
                    const itemDataAttributes = `data-idnotificacion="${notificacion.idNotificacion}" data-idpublicacion="${notificacion.idPublicacion}"`;
                    const linkLeida = notificacion.leida ? '' : `<a href="#" class="marcar-leida" data-idnotificacion="${notificacion.idNotificacion}">Marcar como leída</a>`;
                    // El enlace principal ahora está implícito en el click del div '.notificacion-item'

                    // *** MODIFICACION CLAVE AQUI ***
                    // Construir el texto del mensaje basado en el tipo de notificación
                    let mensajeTexto = '';
                    // Puedes ajustar las frases según tus necesidades
                    switch (notificacion.tipo) {
                        case 'like':
                            mensajeTexto = ` le dio me gusta a tu publicación.`;
                            break;
                        case 'comentario':
                            mensajeTexto = ` comentó en tu publicación.`;
                            break;
                        // Agrega casos para otros tipos de notificación si los tienes
                        // case 'compartir':
                        //     mensajeTexto = ` compartió tu publicación.`;
                        //     break;
                        default:
                            // En caso de tipo desconocido, puedes usar el mensaje original (con precaución)
                             // Si usas este, puede reaparecer "El usuario" si el mensaje original es "Nombre El usuario..."
                             mensajeTexto = `: ${notificacion.mensaje}`;
                            // Una alternativa más segura si el tipo es desconocido es un mensaje genérico:
                            // mensajeTexto = ` tienes una nueva notificación.`;
                            break;
                    }
                    // *** FIN MODIFICACION CLAVE ***

                    const itemHtml = `
                        <div class="${itemClass}" ${itemDataAttributes}>
                            <p><strong>${htmlspecialchars(notificacion.usuarioEmiteNombre)}</strong>${htmlspecialchars(mensajeTexto)}</p>
                            <small>${htmlspecialchars(notificacion.fechaCreacion)}</small>
                            ${linkLeida}
                        </div>
                    `; // Ya no usamos notificacion.mensaje.replace(...)

                    listaHtml.append(itemHtml);
                });
            }
        },
        error: function(xhr, status, error) {
             // Manejo de errores más detallado
             console.error("Error al cargar notificaciones:", status, error, xhr.responseText);
            $('#lista-notificaciones').html('<p>Error al cargar las notificaciones.</p>');
        }
    });
}

// Función para actualizar solo el contador de notificaciones no leídas
function actualizarContadorNotificaciones() {
    $.ajax({
        url: '../Back/obtener_cantidad_no_leidas.php',
        type: 'GET',
        success: function(cantidad) {
            const contador = $('#contador-notificaciones');
            const numCantidad = parseInt(cantidad); // Convertir a número

            contador.text(numCantidad);

            if (numCantidad > 0) {
                contador.show(); // Mostrar si hay notificaciones no leídas
                 // Opcional: Si hay notificaciones no leídas, precarga la lista (si no la cargas ya periódicamente)
                 // cargarNotificaciones();
            } else {
                contador.hide(); // Ocultar si no hay
            }
        },
        error: function() {
             console.error("Error al obtener contador de notificaciones.");
            $('#contador-notificaciones').text('!'); // Indica error
            $('#contador-notificaciones').show(); // Mostrar indicador de error
        }
    });
}

// Función AJAX separada para marcar como leída (usada al hacer clic en el item completo)
// Esto evita la redirección inmediata si el usuario solo quería marcar como leído y seguir en el dashboard
async function marcarNotificacionLeidaAjax(idNotificacion) {
     try {
           const response = await fetch('../Back/marcar_notificacion_leida.php', {
               method: 'POST', // o 'GET'
               headers: {
                   'Content-Type': 'application/x-www-form-urlencoded'
               },
               body: new URLSearchParams({
                   'idNotificacion': idNotificacion
               })
           });
           const data = await response.json();
           if (data.success) {
               console.log(`Notificación ${idNotificacion} marcada como leída.`);
                actualizarContadorNotificaciones(); // Actualizar contador
                // Encuentra el item en la lista visible y actualiza su clase
                $(`#lista-notificaciones div[data-idnotificacion="${idNotificacion}"]`)
                    .removeClass('notificacion-no-leida')
                    .addClass('notificacion-leida')
                    .find('.marcar-leida').remove(); // Eliminar el enlace "Marcar como leída"
           } else {
               console.error('Error al marcar notificación como leída (AJAX):', data.message);
           }
       } catch (error) {
           console.error('Error en la petición AJAX para marcar como leída:', error);
       }
}


// Función para escapar HTML (útil para datos recibidos por AJAX)
function htmlspecialchars(str) {
    if (typeof str !== 'string') {
        return str; // Retorna el valor si no es string (ej. null, number)
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


// --- Funcionalidad para mostrar/ocultar formulario de publicación (existente) ---
// Asumiendo que tienes un botón que llama a esta función
function toggleForm() {
    const form = document.getElementById("EspPub");
    if (form) {
        form.style.display = (form.style.display === "flex") ? "none" : "flex";
    } else {
        console.error("Elemento con id 'EspPub' no encontrado.");
    }
}
