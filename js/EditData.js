window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    if (error === 'username_exists') {
        alert('El nombre de usuario ya existe. Por favor, elige otro nombre de usuario.');
    } else if (error === 'email_exists') {
        alert('El correo electrónico ya está registrado. Por favor, utiliza otro correo.');
    }else if (error === 'user_not_updated') {
        alert('El usuario no se pudo actualizar. Por favor, intenta mas tarde.');
    }

 
};