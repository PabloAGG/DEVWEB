window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    if (error === 'password') {
        alert('La contraseña es incorrecta.');
    } else if (error === 'user_not_found') {
        alert('El nombre de usuario o correo electrónico no existe.');
    }else if(error === 'user_not_deleted'){
        alert('El usuario no ha podido ser eliminado. Por favor, contacta al administrador.');}


    if(success === 'user_created') {
        alert('Usuario creado correctamente. Por favor, inicia sesión.');
    }else if(success === 'user_deleted'){
        alert('El usuario ha sido eliminado. Por favor, contacta al administrador.');}

    window.addEventListener('load', () => {
        document.getElementById('usuario').value = '';
        document.getElementById('password').value = '';
        
      });
};