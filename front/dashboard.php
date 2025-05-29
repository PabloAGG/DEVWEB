<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
  header('Location: InicioSesion.php'); // Redirigir al inicio de sesión si no está autenticado
 exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM datos_sesion v WHERE v.idUsuario = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
 $user_name = $row['nomUs'];
 $full_name = $row['nombre'];
 $user_email = $row['correo'];
 $profile_image = $row['imagen'];
 $user_role = $row['usAdmin'];
 $birth_date = $row['nacimiento'];
} else {
 echo "Error: No se encontró el usuario.";
 exit();
}

if (isset($_GET['leer_notificacion']) && is_numeric($_GET['leer_notificacion'])) {
 $idNotificacion = $_GET['leer_notificacion'];
 marcarNotificacionLeida($conn, $idNotificacion);
 header("Location: dashboard.php");
 exit();
}
function marcarNotificacionLeida($conn, $idNotificacion) {
 $query = "UPDATE Notificaciones SET leida = 1 WHERE idNotificacion = ?";
 $stmt = mysqli_prepare($conn, $query);
 mysqli_stmt_bind_param($stmt, "i", $idNotificacion);
 mysqli_stmt_execute($stmt);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Pagina principal</title>
<link rel="stylesheet" href="../css/estiloslog.css">
<link rel="stylesheet" href="../css/Dashboard.css">
<link rel="stylesheet" href="https://unpkg.com/intro.js/minified/introjs.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
<style>
    /* Estilos para el header adaptable */
    @media (max-width: 780px) {
        header .barrPrin {
            display: none; /* Oculta los botones de navegación principales del header */
        }
        /* Asegúrate de que nav.nav-mobile sea visible y esté bien posicionado en móviles.
           Puede que ya tengas estilos para esto en Dashboard.css o estiloslog.css.
           Si nav.nav-mobile es una barra inferior, usualmente tendría position: fixed; bottom: 0; */
        .nav-mobile {
            display: flex; /* O block, dependiendo de tu diseño. Asumiendo flex para centrar iconos. */
            justify-content: space-around;
            align-items: center;
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #333; /* Ejemplo de color de fondo */
            padding: 10px 0;
            z-index: 1000; /* Para asegurar que esté por encima de otro contenido */
        }
        .nav-mobile button {
            color: white; /* Ejemplo de color de iconos/texto */
            background: none;
            border: none;
            font-size: 1.5rem; /* Tamaño de iconos */
        }
        main {
             padding-bottom: 70px; /* Añade padding al main para que el nav-mobile no tape contenido */
        }
    }
    @media (min-width: 781px) {
        .nav-mobile {
            display: none; /* Oculta nav.nav-mobile en pantallas grandes */
        }
    }
</style>
</head>
<body class="cuerpo">

<header>
 <div class="logo" data-step="1" data-intro="¡Bienvenido! Este es el logo y tu enlace rápido al inicio.">
    <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px" alt="Logo DEVWEB"></a>
 </div>
 <div class="barrPrin" data-step="2" data-intro="Usa estos botones para navegar a las secciones principales: Inicio, Perfil y Categorías. También puedes cerrar tu sesión aquí.">
<button onclick="location.href='dashboard.php'">Inicio</button>
<button onclick="location.href='Perfil.php'">Perfil</button>
<button onclick="location.href='BusqAv.php'">Categorias</button>
<button onclick="location.href='../Back/LogOut.php'">Cerrar sesion</button>
</div>
 <div class="search-container" data-step="3" data-intro="Encuentra rápidamente lo que buscas utilizando la barra de búsqueda.">
<input type="text" class="search-bar" placeholder="Buscar...">
 <button class="search-button"><i class="fa-solid fa-magnifying-glass"></i></button>
 </div>
<div class="notificaciones" data-step="4" data-intro="Aquí verás tus notificaciones importantes. ¡No te pierdas nada!">
    <button id="btn-notificaciones" title="Notificaciones">
        <i class="fa-solid fa-bell"></i>
        <span id="contador-notificaciones" class="contador-notificaciones">0</span>
    </button>
    <div id="lista-notificaciones" class="lista-notificaciones">
        <p>Cargando notificaciones...</p>
    </div>
</div>

<div class="chat-modal-trigger" data-step="5" data-intro="Inicia conversaciones con otros usuarios desde aquí.">
    <button id="openChatButton" title="Abrir Chat">
        <i class="fas fa-comments"></i>
    </button>
    <div id="chatFollowersModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-button" onclick="document.getElementById('chatFollowersModal').style.display='none'">&times;</span>
            <h2>Iniciar un Chat</h2>
            <div class="chat-contacts-section">
                <h3>Siguiendo</h3>
                <div id="chatFollowedList" class="chat-user-list"></div>
            </div>
            <div class="chat-contacts-section">
                <h3>Otras Conversaciones</h3>
                <div id="chatOthersList" class="chat-user-list"></div>
            </div>
        </div>
    </div>
</div>

<div class="identificador" data-step="6" data-intro="Accede a tu perfil haciendo clic en tu nombre de usuario.">
    <button onclick="location.href='Perfil.php'"><?php echo htmlspecialchars($user_name); ?></button>
</div>
</header>



<main>


<nav class="nav-mobile" data-step="10" data-intro="En dispositivos móviles, usa esta barra para una navegación rápida.">
<button onclick="location.href='dashboard.php'"><i class="fas fa-home"></i></button>
<button onclick="location.href='Perfil.php'"><i class="fa-solid fa-user"></i></button>
<button onclick="location.href='BusqAv.php'"><i class="fa-solid fa-folder-open"></i></button>
<button onclick="location.href='../Back/LogOut.php'"><i class="fa-solid fa-right-from-bracket"></i></button>
</nav>


    <div class="EspPub" id="EspPub" data-step="7" data-intro="¡Comparte tus ideas! Desde aquí puedes crear una nueva publicación.">
<form class="espPubform" id="espPubform" action="../BACK/Publicar.php" method="post" enctype="multipart/form-data">
<h2>Crea una Publicacion</h2><br>
<div>
<label for="tituloP">Titulo</label><br>
 <input type="text" name="titleP" id="titleP" class="input" placeholder="Titulo">
<label for="select">Categoria</label>
<select name="select" id="select">
<option value="" disabled selected hidden></option>
<?php
$query_categorias = "SELECT * FROM Categorias"; // Renombrada la variable para evitar conflicto
$stmt_categorias = mysqli_prepare($conn, $query_categorias);
mysqli_stmt_execute($stmt_categorias);
$result_categorias = mysqli_stmt_get_result($stmt_categorias);
while ($row_cat = mysqli_fetch_assoc($result_categorias)) { // Renombrada la variable de fila
    echo '<option value="' . htmlspecialchars($row_cat['nombre']) . '">' . htmlspecialchars($row_cat['nombre']) . '</option>';
}
mysqli_stmt_close($stmt_categorias); // Cerrar este statement
?>
</select>
</div>


<label for="descP">Descripcion</label><br>
<textarea name="descP" id="descP" class="input" aria-label="With textarea"></textarea>


<label for="fpubfot">Agrega una foto</label>
 <input type="file" name="fpubfot" id="ffoto"><br>


 <div id="botonesPubli">
<button class="btnPub" type="submit" ><i class="fa-solid fa-pen-to-square"></i>Publicar</button></div>
</form>
</div>
<select name="OrdenPublicaciones" id="OrdenPublicaciones" data-step="8" data-intro="Organiza cómo ves las publicaciones: por seguidos, las más recientes, comentadas o gustadas.">
 <option value="">Ordenar por:</option>
 <option value="seguidos">Siguiendo</option>
 <option value="ultimas">Últimas Publicaciones</option>
<option value="comentadas">Más Comentadas</option>
 <option value="gustadas">Más Gustadas</option>
</select>

<div class="contenedor_Publicaciones" id="contenedorPublicaciones" data-step="9" data-intro="Aquí aparecerán todas las publicaciones. ¡Explora el contenido!">

</div>


       
<div id="chatWindowModal" class="chat-modal" style="display:none;">
    <div class="chat-modal-content-conversation">
        <span class="chat-close-button" onclick="closeChatWindow()">&times;</span>
        <h3 id="chatWithUserName">Chat</h3>
        <div id="chatMessagesArea" class="chat-messages-area">
            </div>
        <div class="chat-input-area">
            <input type="text" id="chatMessageInput" placeholder="Escribe un mensaje...">
            <button id="chatSendMessageButton"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
</div>

</main>
<script>
    const user_id_ACTUAL = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"  crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tourMostrado = localStorage.getItem('dashboardTourMostrado');
    // const esPantallaAncha = window.innerWidth > 780; // Esta variable no se usa directamente en la lógica del tour aquí

    if (!tourMostrado) {
        const intro = introJs();
        
        // 1. Construir el array de pasos iniciales de forma segura
        const stepNumbers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        let initialSteps = [];

        stepNumbers.forEach(num => {
            const element = document.querySelector(`[data-step="${num}"]`);
            if (element) {
                const introText = element.getAttribute('data-intro');
                if (introText) { // Asegurarse de que el atributo data-intro exista y tenga valor
                    initialSteps.push({
                        element: element,
                        intro: introText
                    });
                } else {
                    console.warn(`Intro.js: El elemento con data-step="${num}" no tiene el atributo data-intro.`);
                }
            } else {
                console.warn(`Intro.js: No se encontró el elemento con data-step="${num}".`);
            }
        });

        // Si no se pudieron construir pasos válidos, no continuar.
        if (initialSteps.length === 0) {
            console.warn("Intro.js: No se configuraron pasos válidos para el tour. El tour no se iniciará.");
            return;
        }

        // 2. Configurar Intro.js con los pasos iniciales (validados)
        intro.setOptions({
            steps: initialSteps,
            nextLabel: 'Siguiente &rarr;',
            prevLabel: '&larr; Anterior',
            doneLabel: '¡Entendido!',
        });
        
        // 3. Filtrar los pasos para incluir solo los elementos visibles (pasosActivos)
        let pasosActivos = [];
        // Asegurarse de que intro.options.steps exista antes de iterar
        if (intro.options && Array.isArray(intro.options.steps)) {
            intro.options.steps.forEach(function(paso) {
                const elemento = paso.element; // paso.element ya es un elemento DOM aquí
                // Verificar si el elemento es visible
                if (elemento && (elemento.offsetWidth > 0 || elemento.offsetHeight > 0 || elemento.getClientRects().length > 0)) {
                    pasosActivos.push(paso);
                }
            });
        } else {
            // Fallback o log si intro.options.steps no es lo esperado (debería estarlo después del setOptions)
            console.warn("Intro.js: No se pudieron obtener los pasos de intro.options para filtrar. Verificando visibilidad de initialSteps.");
            initialSteps.forEach(function(paso) {
                 const elemento = paso.element;
                 if (elemento && (elemento.offsetWidth > 0 || elemento.offsetHeight > 0 || elemento.getClientRects().length > 0)) {
                    pasosActivos.push(paso);
                }
            });
        }
        
        // Si hay pasos activos (visibles), configurar Intro.js con ellos e iniciar.
        if (pasosActivos.length > 0) {
            intro.setOptions({ steps: pasosActivos }); // Actualizar Intro.js para usar solo los pasos activos/visibles
            
            intro.oncomplete(function() {
                localStorage.setItem('dashboardTourMostrado', 'true');
            });

            intro.onexit(function() {
                // Opcional: Marcar como mostrado también si el usuario sale del tour prematuramente.
                // localStorage.setItem('dashboardTourMostrado', 'true'); 
                console.log("Tour de Intro.js cerrado por el usuario.");
            });

            intro.start();
        } else {
            console.warn("Intro.js: No hay pasos activos (visibles) para mostrar. El tour no se iniciará.");
        }

    } else {
        console.log("Intro.js: El tour del dashboard ya ha sido mostrado.");
    }
});
</script>
<script src="../js/chat.js"></script> 
<script src="../js/mensajesNotis.js"></script>
<script src="../js/search.js"></script>
<script src="../js/script.js"></script>
<script src="../js/dashboard.js"></script>
<script src="../js/publicaciones_ordenadas.js"></script>

</body>
</html>