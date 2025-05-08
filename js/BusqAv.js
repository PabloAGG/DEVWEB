const categoriaSelect = document.getElementById('categoria-select');
const searchBar = document.querySelector('.search-bar');

categoriaSelect.addEventListener('change', function() {
    let busqueda = searchBar.value;
    let categoria = this.value;
    let url = 'BusqAv.php?';

    if (busqueda) {
        url += 'busqueda=' + encodeURIComponent(busqueda) + '&';
    }
    if (categoria) {
        url += 'categoria=' + encodeURIComponent(categoria);
    } else {
        url = url.slice(0, -1); // Remove trailing '&' if no categoria
    }

    window.location.href = url;
});