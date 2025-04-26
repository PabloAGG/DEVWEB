window.onload = function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    const success = urlParams.get('success');

    if(success === 'user_updated') {
        alert('Usuario actualizado correctamente.');
    }
};