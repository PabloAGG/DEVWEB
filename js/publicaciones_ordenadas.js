document.addEventListener('DOMContentLoaded', () => {
    const ordenSelect = document.getElementById('OrdenPublicaciones');
    const contenedorPublicaciones = document.getElementById('contenedorPublicaciones');

    // --- FUNCIÓN PARA CARGAR PUBLICACIONES ---
    async function cargarPublicaciones(orden) {
        // Mostrar indicador de carga (opcional, pero recomendado)
        contenedorPublicaciones.innerHTML = '<p class="cargando-pubs">Cargando publicaciones...</p>';

        try {
            // Ajusta la URL si necesitas enviar el user_id para la opción "seguidos", etc.
            // Por ejemplo: '../Back/obtener_publicaciones_ordenadas.php?orden=' + orden + '&user_id=' + user_id_ACTUAL
            const response = await fetch('../Back/obtener_publicaciones_ordenadas.php?orden=' + orden, {
                method: 'GET', // O 'POST' si tu backend espera POST y envías datos en el body
                headers: {
                    'Content-Type': 'application/json'
                    // Si es POST y envías JSON en el body, asegúrate que el backend lo maneje.
                    // Si es GET, los parámetros van en la URL como ya lo tienes.
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.html !== undefined) {
                contenedorPublicaciones.innerHTML = data.html;
                attachLikeButtonListeners(); // Vuelve a asignar listeners a los nuevos botones de like
            } else if (data.error) {
                console.error('Error al obtener publicaciones:', data.error);
                contenedorPublicaciones.innerHTML = `<p class="error-message">Error al cargar las publicaciones: ${data.error}</p>`;
            } else {
                // Si data.html no está definido y no hay data.error, puede que el backend no esté devolviendo lo esperado.
                console.warn('Respuesta inesperada del servidor:', data);
                contenedorPublicaciones.innerHTML = '<p class="error-message">No se pudieron cargar las publicaciones (respuesta inesperada).</p>';
            }

        } catch (error) {
            console.error('Error al realizar la petición:', error);
            contenedorPublicaciones.innerHTML = '<p class="error-message">Error de conexión al cargar publicaciones.</p>';
        }
    }

    // --- EVENT LISTENER PARA EL SELECT ---
    ordenSelect.addEventListener('change', function() {
        const ordenSeleccionado = this.value;
        if (ordenSeleccionado) { // Solo carga si se selecciona un valor válido
            cargarPublicaciones(ordenSeleccionado);
        }
    });

    // --- FUNCIÓN PARA ASIGNAR LISTENERS A BOTONES DE LIKE ---
    // Esta función se debe llamar cada vez que se actualiza el contenido de contenedorPublicaciones
    function attachLikeButtonListeners() {
        const likeButtons = document.querySelectorAll('.like-btn');
        likeButtons.forEach(button => {
            // Remover listeners antiguos para evitar duplicados si esta función se llama múltiples veces
            // Esto es una forma simple, para casos más complejos considera clonar y reemplazar el nodo.
            const newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);

            newButton.addEventListener('click', async function() {
                const publiId = this.dataset.idpubli;
                // Intenta encontrar el span del contador de likes de una manera más robusta
                const cardFooter = this.closest('.card-footer'); // Asumiendo que el botón está en un card-footer
                const likeCountSpan = cardFooter ? cardFooter.querySelector('.like-count') : null;


                try {
                    const response = await fetch('../Back/administrarLikes.php?id=' + publiId, {
                        method: 'GET', // O 'POST' según tu backend
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success && likeCountSpan) { // Asegurarse que likeCountSpan existe
                        const likeTextSpan = this.querySelector('.like-text');
                        if (data.action === 'like') {
                            this.classList.add('liked');
                            if(likeTextSpan) likeTextSpan.textContent = 'Te gusta';
                            likeCountSpan.textContent = data.nLikes; // Usar el conteo del servidor
                        } else if (data.action === 'unlike') {
                            this.classList.remove('liked');
                            if(likeTextSpan) likeTextSpan.textContent = 'Me gusta';
                            likeCountSpan.textContent = data.nLikes; // Usar el conteo del servidor
                        }
                    } else {
                        console.error('Error al actualizar el like:', data.message || 'No se encontró el contador de likes.');
                    }

                } catch (error) {
                    console.error('Error al enviar la petición de like:', error);
                }
            });
        });
    }

    // --- CARGA INICIAL DE PUBLICACIONES ---
    // Cargar las "últimas publicaciones" por defecto al cargar la página.
    // Asegúrate que tu backend reconozca 'ultimas' como un orden válido.
    if (ordenSelect.value === "" || ordenSelect.value === "ultimas") { // Si no hay nada seleccionado o es 'ultimas'
        ordenSelect.value = "ultimas"; // Asegura que 'ultimas' esté seleccionado visualmente si es la carga inicial
        cargarPublicaciones('ultimas');
    } else {
        // Si ya hay un valor seleccionado (ej. por recarga de página con estado recordado), usa ese valor
        cargarPublicaciones(ordenSelect.value);
    }

});