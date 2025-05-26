  document.addEventListener('DOMContentLoaded', function() {
    const followButton = document.getElementById('btnAccionSeguir');
        const followerCountSpan = document.getElementById('follower-count');
        // Los elementos del botón se obtienen dentro del event listener porque solo existen si el botón existe
        
        if (followButton) {
            followButton.addEventListener('click', function() {
                const perfilId = this.dataset.perfilId;
                let accion = this.dataset.accion; // 'seguir' or 'dejar_de_seguir'
                const textoBtnSpan = document.getElementById('textoBtnAccionSeguir'); // Span for button text
                const iconBtnElement = document.getElementById('iconBtnAccionSeguir'); // Icon element

                this.disabled = true; // Disable button to prevent multiple clicks

                const formData = new FormData();
                formData.append('perfil_id_accion', perfilId);
                formData.append('accion_seguir', accion);
                formData.append('ajax', 'true');

                fetch('PerfilExt.php', { // Fetch to the same file
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (followerCountSpan) {
                            followerCountSpan.textContent = data.numSeguidores;
                        }

                        if (data.esSeguidor) {
                            this.dataset.accion = 'dejar_de_seguir';
                            if (textoBtnSpan) textoBtnSpan.textContent = ' Dejar de seguir';
                            this.classList.remove('seguir');
                            this.classList.add('seguido');
                            if (iconBtnElement) {
                                iconBtnElement.classList.remove('fa-user-plus');
                                iconBtnElement.classList.add('fa-user-minus');
                            }
                        } else {
                            this.dataset.accion = 'seguir';
                            if (textoBtnSpan) textoBtnSpan.textContent = ' Seguir';
                            this.classList.remove('seguido');
                            this.classList.add('seguir');
                            if (iconBtnElement) {
                                iconBtnElement.classList.remove('fa-user-minus');
                                iconBtnElement.classList.add('fa-user-plus');
                            }
                        }
                        // console.log(data.message); // Optional: log success message
                    } else {
                        console.error('Error en la acción:', data.message || 'Ocurrió un error.');
                        // alert(data.message || 'No se pudo completar la acción.'); // Opcional: mostrar alerta al usuario
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    // alert('Error de conexión. Inténtalo de nuevo.'); // Opcional: mostrar alerta al usuario
                })
                .finally(() => {
                    this.disabled = false; // Re-enable button
                });
             });
        }
   });