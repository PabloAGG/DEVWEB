document.addEventListener('DOMContentLoaded', () => {
    const ordenSelect = document.getElementById('OrdenPublicaciones');
    const contenedorPublicaciones = document.getElementById('contenedorPublicaciones');

    ordenSelect.addEventListener('change', async function() {
        const ordenSeleccionado = this.value;

        try {
            const response = await fetch('../Back/obtener_publicaciones_ordenadas.php?orden=' + ordenSeleccionado, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.html !== undefined) {
                contenedorPublicaciones.innerHTML = data.html;
            
                attachLikeButtonListeners();
            } else if (data.error) {
                console.error('Error al obtener publicaciones:', data.error);
                contenedorPublicaciones.innerHTML = '<p class="error-message">Error al cargar las publicaciones.</p>';
            }

        } catch (error) {
            console.error('Error al realizar la petición:', error);
            contenedorPublicaciones.innerHTML = '<p class="error-message">Error de conexión.</p>';
        }
    });

    function attachLikeButtonListeners() {
        const likeButtons = document.querySelectorAll('.like-btn');
        likeButtons.forEach(button => {
            button.addEventListener('click', async function() {
                const publiId = this.dataset.idpubli;
                const likeCountSpan = this.parentElement.querySelector('.like-count');

                try {
                    const response = await fetch('../Back/administrarLikes.php?id=' + publiId, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        const likeTextSpan = this.querySelector('.like-text');
                        if (data.action === 'like') {
                            this.classList.add('liked');
                            likeTextSpan.textContent = 'Te gusta';
                            likeCountSpan.textContent = parseInt(likeCountSpan.textContent.trim()) + 1;
                        } else if (data.action === 'unlike') {
                            this.classList.remove('liked');
                            likeTextSpan.textContent = 'Me gusta';
                            likeCountSpan.textContent = parseInt(likeCountSpan.textContent.trim()) - 1;
                        }
                    } else {
                        console.error('Error al actualizar el like:', data.message);
                    }

                } catch (error) {
                    console.error('Error al enviar la petición de like:', error);
                }
            });
        });
    }
});