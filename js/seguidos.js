document.addEventListener('DOMContentLoaded', function() {
        const unfollowButtons = document.querySelectorAll('.btn-unfollow');

        unfollowButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userIdToUnfollow = this.dataset.userid;
                const listItem = document.getElementById('user-item-' + userIdToUnfollow);

                if (!confirm('¿Estás seguro de que quieres dejar de seguir a este usuario?')) {
                    return;
                }

                this.disabled = true; // Deshabilitar botón

                const formData = new FormData();
                formData.append('perfil_id_accion', userIdToUnfollow);
                formData.append('accion_dejar_de_seguir', 'true'); // Nombre de la acción
                formData.append('ajax', 'true');

                fetch('Seguidos.php', { // Fetch al mismo archivo
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
                        if (listItem) {
                            listItem.style.transition = 'opacity 0.5s ease';
                            listItem.style.opacity = '0';
                            setTimeout(() => {
                                listItem.remove();
                                // Opcional: verificar si la lista está vacía y mostrar mensaje
                                const userList = document.querySelector('.user-list');
                                if (userList && userList.children.length === 0) {
                                    const container = document.querySelector('.user-list-container');
                                    const noUsersP = document.createElement('p');
                                    noUsersP.classList.add('no-users');
                                    noUsersP.textContent = "Ya no sigues a nadie.";
                                    container.appendChild(noUsersP);
                                }
                            }, 500); // Esperar a que termine la transición
                        }
                        // alert(data.message); // Opcional: mostrar mensaje de éxito
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo dejar de seguir al usuario.'));
                        this.disabled = false; // Rehabilitar botón si falla
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    alert('Error de conexión. Inténtalo de nuevo.');
                    this.disabled = false; // Rehabilitar botón si falla
                });
            });
        });
    });