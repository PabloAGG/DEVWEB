document.addEventListener('DOMContentLoaded', () => {
    const searchBar = document.querySelector('.search-bar');

    searchBar.addEventListener('keypress', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault(); // Evita el comportamiento por defecto del Enter (enviar el formulario, si existe)
            const busqueda = searchBar.value;
            if (busqueda.trim() !== '') { // Verifica que no esté vacío
                window.location.href = 'BusqAv.php?busqueda=' + encodeURIComponent(busqueda);
            } else {
                // Opcional: Puedes mostrar un mensaje de error o hacer otra cosa si el input está vacío
                alert('Por favor, ingresa un término de búsqueda.');
            }
        }
    });
});