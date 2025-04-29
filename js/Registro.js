const pswInput=document.getElementById('psw-change');
document.addEventListener('DOMContentLoaded', () => {
    // Manejo de errores desde URL
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');

    if (error === 'user_not_created') {
        alert('El usuario no se pudo crear. Por favor, intenta nuevamente.');
    } else if (error === 'user_exists') {
        alert('El usuario ya existe. Por favor, elige otro nombre de usuario.');
    } else if (error === 'email_exists') {
        alert('El correo electrónico ya está registrado. Por favor, utiliza otro correo.');
    }

    // Elementos del formulario
    const nombreCompletoInput = document.querySelector("input[name='nombre_completo']");
    const contraseñaInput = document.querySelector("input[name='contraseña_usuario']");
    const contraseñacheck = document.querySelector("input[name='contraseña_Check']");
    const fechaNacimientoInput = document.querySelector("input[name='fecha_usuario']");
    const emailInput = document.querySelector("input[name='email_usuario']");
    const form = document.querySelector("form");

    // Validación en tiempo real
    const validarCampo = (campo, regex, mensaje) => {
        if (!regex.test(campo.value)) {
            campo.setCustomValidity(mensaje);
            campo.reportValidity(); // Muestra el mensaje inmediatamente
            return false;
        } else {
            campo.setCustomValidity("");
            return true;
        }
    };

    nombreCompletoInput.addEventListener('input', () => {
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        validarCampo(nombreCompletoInput, nombreRegex, "El nombre solo puede contener letras y espacios.");
    });

    if(pswInput.checked){
if(contraseñaInput!==null && contraseñacheck!==null){

    contraseñaInput.addEventListener('input', () => {
        const contraseñaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        validarCampo(contraseñaInput, contraseñaRegex, 
            "La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.");
    });

    contraseñacheck.addEventListener('input', () => {
        if (contraseñaInput.value !== contraseñacheck.value) {
            contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
            contraseñacheck.reportValidity();
        } else {
            contraseñacheck.setCustomValidity("");
        }
    });
}else{
    alert("Ingrese una contraseña para continuar.");
    contraseñaInput.setCustomValidity("Ingrese una contraseña para continuar.");
    return;
}
    }

    fechaNacimientoInput.addEventListener('input', () => {
        if (!fechaNacimientoInput.value) {
            fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
            fechaNacimientoInput.reportValidity();
            return;
        }

        const fechaSeleccionada = new Date(fechaNacimientoInput.value);
        const fechaActual = new Date();
        if (fechaSeleccionada > fechaActual) {
            fechaNacimientoInput.setCustomValidity("La fecha de nacimiento no puede ser en el futuro.");
            fechaNacimientoInput.reportValidity();
        } else {
            fechaNacimientoInput.setCustomValidity("");
        }
    });

    emailInput.addEventListener('input', () => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        validarCampo(emailInput, emailRegex, "Por favor, ingresa un correo electrónico válido.");
    });

    // Validación al enviar
    form.addEventListener('submit', (event) => {
        let esValido = true;

        // Forzar validación de todos los campos
        esValido = validarCampo(nombreCompletoInput, /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/, "El nombre solo puede contener letras y espacios.") && esValido;
        
        
        if (pswInput.checked){
            esValido = validarCampo(contraseñaInput, /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/, "Contraseña inválida.") && esValido;
        if (contraseñaInput.value !== contraseñacheck.value) {
            contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
            contraseñacheck.reportValidity();
            esValido = false;
        }
 }
        if (!fechaNacimientoInput.value) {
            fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
            fechaNacimientoInput.reportValidity();
            esValido = false;
        }
   

        esValido = validarCampo(emailInput, /^[^\s@]+@[^\s@]+\.[^\s@]+$/, "Correo electrónico inválido.") && esValido;

        if (!esValido) {
            event.preventDefault();
            alert("Por favor, corrige los errores en el formulario.");
        }
    });
});


function previewImage() {
    const fileInput = document.getElementById('imgRuta');
    const img = document.getElementById('imgPerfil');
    const imgContainer = document.getElementById('img-contenedor');
    if (!fileInput || !img) return;

    const file = fileInput.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            img.style.display = 'block';
            imgContainer.style.marginBottom='200px'; // Mostrar el contenedor de la imagen
        };
        reader.readAsDataURL(file);
    } else {
        img.style.display = 'none';
    }
}

function togglePasswordVisibility(){
    const pswCont=document.getElementById('psw-contenedor');
    const pswCont2=document.getElementById('psw-contenedor2');

    if(pswInput.checked){
        pswCont.style.display='block';
        pswCont2.style.display='block';
        pswCont.required = true;
        pswCont2.required = true;
    }else{
        pswCont.style.display='none';
        pswCont.required = false;
        pswCont.value = "";
        pswCont2.style.display='none';
        pswCont2.required = false;
        pswCont2.value = "";
    }
}
// window.onload = function() {
//     const urlParams = new URLSearchParams(window.location.search);
//     const error = urlParams.get('error');


//     if (error === 'user_not_created') {
//         alert('El usuario no se pudo crear. Por favor, intenta nuevamente.');
      
//     } else if (error === 'user_exists') {
//         alert('El usuario ya existe. Por favor, elige otro nombre de usuario.');
//     } else if (error === 'email_exists') {
//         alert('El correo electrónico ya está registrado. Por favor, utiliza otro correo.');
//     } 
// };
// ocument.addEventListener('DOMContentLoaded', () => {
   
//    let isValid = true;
//  const nombreCompletoInput = document.querySelector("input[name='nombre_completo']");
//     const contraseñaInput = document.querySelector("input[name='contraseña_usuario']");
//     const contraseñacheck = document.querySelector("input[name='contraseña_Check']");
//     const fechaNacimientoInput = document.querySelector("input[name='fecha_usuario']");
//     const emailInput = document.querySelector("input[name='email_usuario']");
//     const form = document.querySelector("form");

//     // Validación de Nombre Completo
//     nombreCompletoInput.addEventListener('input', () => {
//         const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
//         if (!nombreRegex.test(nombreCompletoInput.value)) {
//             nombreCompletoInput.setCustomValidity("El nombre solo puede contener letras y espacios.");
//             isValid = false;
//         } else {
//             nombreCompletoInput.setCustomValidity("");
//         }
//     });

//     // Validación de Contraseña
//     contraseñaInput.addEventListener('input', () => {
//         const contraseñaRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
//         if (!contraseñaRegex.test(contraseñaInput.value)) {
//             contraseñaInput.setCustomValidity("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.");
//        isValid = false;
//         } else {
//             contraseñaInput.setCustomValidity("");
//         }
//     });
// // Validación de Confirmación de Contraseña
//     contraseñacheck.addEventListener('input', () => {
//         if (contraseñaInput.value !== contraseñacheck.value) {
//             contraseñacheck.setCustomValidity("Las contraseñas no coinciden.");
//             isValid = false;
//         } else {
//             contraseñacheck.setCustomValidity("");
//         }
//     });

//     // Validación de Fecha de Nacimiento
//     fechaNacimientoInput.addEventListener('input', () => {
//         if (!fechaNacimientoInput.value) {
//             fechaNacimientoInput.setCustomValidity("Por favor, selecciona una fecha.");
//             isValid = false;
//             return;
//         }

//         const fechaSeleccionada = new Date(fechaNacimientoInput.value);
//         const fechaActual = new Date();
//         if (fechaSeleccionada > fechaActual) {
//             fechaNacimientoInput.setCustomValidity("La fecha de nacimiento no puede ser en el futuro.");
//             isValid = false;
//         } else {
//             fechaNacimientoInput.setCustomValidity("");
//         }
//     });

//     // Validación de Email
//     emailInput.addEventListener('input', () => {
//         const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
//         if (!emailRegex.test(emailInput.value)) {
//             emailInput.setCustomValidity("Por favor, ingresa un correo electrónico válido.");
//             isValid = false;
//         } else {
//             emailInput.setCustomValidity("");
//         }
//     });

//     // Validación al Enviar el Formulario
//     form.addEventListener('submit', (event) => {
     
//         // Prevenir el envío del formulario si hay errores
//         if (!form.checkValidity() || !isValid) {
//             event.preventDefault();
//             alert("Por favor, corrige los errores en el formulario.");
//         }
//     });
// });