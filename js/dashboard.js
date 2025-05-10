document.addEventListener("DOMContentLoaded", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
  const succes=urlParams.get('succes');
    if (error === 'formato_invalido') {
        alert('formato de contenido invalido');
    }else if(error === 'fallo_crear'){
      alert('Error al crear la publicacion, intentalo mas tarde');
    }
  if(succes === 'publicacion_creada'){
    alert('Publicacion creada correctamente');
  }
  


    const form = document.querySelector("form"); // asegúrate de poner el <form> real
    const fileInput = document.getElementById("ffoto");


    form.addEventListener("submit", function (e) {
        const title = form.titleP;
        const desc = form.descP;
        const categoria = form.select;

        if (!title.value.trim()) {
            title.setCustomValidity("Por favor, ingresa un título."); 
            e.preventDefault();
            return;
        }

        if (!desc.value.trim()) {
           desc.setCustomValidity("La descripción no puede estar vacía.");
    
            e.preventDefault();
            return;
        }

        if (!categoria.value) {
           categoria. setCustomValidity("Debes seleccionar una categoría.");
            e.preventDefault();
            return;
        }

        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const validTypes = ["image/jpeg", "image/png", "video/mp4"];
            const maxSize = 10 * 1024 * 1024; // 10 MB

            if (!validTypes.includes(file.type)) {
               fileInput. setCustomValidity("Solo se permiten imágenes .jpg/.png o videos .mp4");
                e.preventDefault();
                return;
            }

            if (file.size > maxSize) {
                fileInput.setCustomValidity("El archivo no puede superar los 10 MB.");
                e.preventDefault();
                return;
            }
        }
    });
});
// // // 
function cargarNotificaciones() {
    $.ajax({
        url: '../Back/obtener_notificaciones.php', // Ruta al nuevo archivo PHP
        type: 'GET',
        success: function(data) {
            $('#lista-notificaciones').html(data); // Reemplaza el contenido con las notificaciones actualizadas
            // Recalcular el contador de notificaciones no leídas
            actualizarContadorNotificaciones();
            $('#lista-notificaciones').fadeIn(); // Mostrar las notificaciones
            setTimeout(function() {
                $('#lista-notificaciones').fadeOut(); // Ocultar las notificaciones después de 5 segundos
            }, 5000);
        },
        error: function() {
            $('#lista-notificaciones').html('<p>Error al cargar las notificaciones.</p>');
        }
    });
}

function actualizarContadorNotificaciones() {
    $.ajax({
        url: '../Back/obtener_cantidad_no_leidas.php', // Nuevo archivo para obtener solo la cantidad
        type: 'GET',
        success: function(cantidad) {
            $('#contador-notificaciones').text(cantidad);
        },
        error: function() {
            $('#contador-notificaciones').text('!'); // Indica error
        }
    });
}
function toggleForm(){
    const form=document.getElementById("EspPub");
    form.style.display = (form.style.display === "flex") ? "none" : "flex";
  }