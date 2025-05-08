document.addEventListener('DOMContentLoaded', () => {
    const likeButtons = document.querySelectorAll('.like-btn');

    likeButtons.forEach(button => {
        button.addEventListener('click', async function() {
            const publiId = this.dataset.idpubli;
            const likeCountSpan = this.parentElement.querySelector('.like-count'); // Seleccionar el span .like-count

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
                        likeCountSpan.textContent = parseInt(likeCountSpan.textContent.trim()) + 1; // Incrementar el contador
                    } else if (data.action === 'unlike') {
                        this.classList.remove('liked');
                        likeTextSpan.textContent = 'Me gusta';
                        likeCountSpan.textContent = parseInt(likeCountSpan.textContent.trim()) - 1; // Decrementar el contador
                    }
                } else {
                    console.error('Error al actualizar el like:', data.message);
                }

            } catch (error) {
                console.error('Error al enviar la petición de like:', error);
            }
        });
    });
});