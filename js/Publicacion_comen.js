async function guardarComentario(event) {
    event.preventDefault();
    ocultarMensaje('mensaje-comentario');
    const form = document.getElementById('form-comentario');
    const comentarioTextarea = form.querySelector('textarea[name="comentario"]');
    const publiIdInput = form.querySelector('input[name="publi_id_comentario"]');

    const comentario = comentarioTextarea.value.trim();

    if (!comentario) {
         mostrarMensaje('mensaje-comentario', 'El comentario no puede estar vacío.', false);
        return;
    }
     if (!publiIdInput || !publiIdInput.value) {
         mostrarMensaje('mensaje-comentario', 'Error: ID de publicacion no encontrado.', false);
         return;
    }

    const publiId = publiIdInput.value;

    try {
        const response = await fetch('../BACK/guardar_comentario.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                'publi_id': publiId, // Nombres coinciden con PHP
                'comentario': comentario
            })
        });

         if (!response.ok) {
           throw new Error(`Error HTTP: ${response.status}`);
        }

        const data = await response.json();

        mostrarMensaje('mensaje-comentario', data.message, data.success);

        if (data.success) {
            // Añadir dinámicamente el nuevo comentario a la lista
            const listaComentarios = document.getElementById('lista-comentarios');
            const primerComentario = listaComentarios.querySelector('.comentario-item, p'); // Busca item existente o el mensaje "sé el primero"

            const nuevoComentarioDiv = document.createElement('div');
            nuevoComentarioDiv.className = 'comentario-item';
            // Usar el nombre de usuario de la sesión actual
            const nombreUsuarioActual = "<?= $user_name ?>"; // Obtener nombre desde PHP
            nuevoComentarioDiv.innerHTML = `
                <strong>${nombreUsuarioActual}</strong>
                <span> (Ahora):</span>
                <p>${comentario.replace(/\n/g, '<br>')}</p> `;

            // Si había mensaje "sé el primero", quitarlo
            if (primerComentario && primerComentario.tagName === 'P') {
                listaComentarios.innerHTML = ''; // Limpiar lista
            }

            // Añadir al principio de la lista
            listaComentarios.insertBefore(nuevoComentarioDiv, listaComentarios.firstChild);

            // Limpiar el textarea
            comentarioTextarea.value = '';
        }

    } catch (error) {
        console.error('Error al guardar comentario:', error);
        mostrarMensaje('mensaje-comentario', 'Ocurrió un error al publicar el comentario.', false);
    }
}

function mostrarMensaje(elementoId, mensaje, esExito = true) {
    const el = document.getElementById(elementoId);
    if (!el) return;
    el.textContent = mensaje;
    el.className = 'mensaje-ajax ' + (esExito ? 'success' : 'error');
    el.style.display = 'block';
    // Opcional: Ocultar después de unos segundos
    // setTimeout(() => { el.style.display = 'none'; }, 5000);
}

function ocultarMensaje(elementoId) {
     const el = document.getElementById(elementoId);
    if (el) el.style.display = 'none';
}