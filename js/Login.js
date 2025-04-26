window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    if (error === 'password') {
        alert('La contraseña es incorrecta.');
    } else if (error === 'user_not_found') {
        alert('El nombre de usuario o correo electrónico no existe.');
    }

    if(success === 'user_created') {
        alert('Usuario creado correctamente. Por favor, inicia sesión.');
    }
};