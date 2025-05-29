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

if ($row = mysqli_fetch_assoc($result)) { $user_name = $row['nomUs'];
 $full_name = $row['nombre'];
 $user_email = $row['correo'];
 $profile_image = $row['imagen'];
 $user_role = $row['usAdmin'];
 $birth_date = $row['nacimiento'];
} else {
 echo "Error: No se encontró el usuario.";
 exit();
}
// Esta lógica de GET para leer notificación podría manejarse mejor con AJAX
// si la notificación se marca al hacer clic en ella en la lista desplegable (como en el JS modificado)
// Si no, asegúrate de que esta función y la redirección sigan estando aquí si se usa un enlace GET directo
if (isset($_GET['leer_notificacion']) && is_numeric($_GET['leer_notificacion'])) {
 $idNotificacion = $_GET['leer_notificacion'];
 marcarNotificacionLeida($conn, $idNotificacion);
 header("Location: dashboard.php"); // Redirigir para evitar re-procesamiento
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>
<body class="cuerpo">

<header>
 <div class="logo">  <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px" alt="Logo DEVWEB"></a></div>
 <div class="barrPrin">
<button onclick="location.href='dashboard.php'">Inicio</button>
<button onclick="location.href='Perfil.php'">Perfil</button>
<button onclick="location.href='BusqAv.php'">Categorias</button>
<button onclick="location.href='../Back/LogOut.php'">Cerrar sesion</button>
</div>
 <div class="search-container">
<input type="text" class="search-bar" placeholder="Buscar...">
 <button class="search-button"><i class="fa-solid fa-magnifying-glass"></i></button>
 </div>
<div class="notificaciones">
    <button id="btn-notificaciones" title="Notificaciones">
        <i class="fa-solid fa-bell"></i>
        <span id="contador-notificaciones" class="contador-notificaciones">0</span>
    </button>
    <div id="lista-notificaciones" class="lista-notificaciones">
        <p>Cargando notificaciones...</p>
    </div>
</div>

<div class="chat-modal-trigger">
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

<div class="identificador">
    <button onclick="location.href='Perfil.php'"><?php echo htmlspecialchars($user_name); ?></button>
</div>
</header>



<main>
<nav class="nav-mobile">
<button onclick="location.href='dashboard.php'"><i class="fas fa-home"></i></button>
<button onclick="location.href='Perfil.php'"><i class="fa-solid fa-user"></i></button>
<button onclick="location.href='BusqAv.php'"><i class="fa-solid fa-folder-open"></i></button>
<button onclick="location.href='../Back/LogOut.php'"><i class="fa-solid fa-right-from-bracket"></i></button>
</nav>
    <div class="EspPub" id="EspPub" >
<form class="espPubform" action="../BACK/Publicar.php" method="post" enctype="multipart/form-data">
<h2>Crea una Publicacion</h2><br>
<div>
<label for="tituloP">Titulo</label><br>
 <input type="text" name="titleP" id="titleP" class="input" placeholder="Titulo">
<label for="select">Categoria</label>
<select name="select" id="select">
<option value="" disabled selected hidden></option>
<?php
$query="SELECT * FROM Categorias";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    echo '<option value="' . htmlspecialchars($row['nombre']) . '">' . htmlspecialchars($row['nombre']) . '</option>';
}
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
<select name="OrdenPublicaciones" id="OrdenPublicaciones">
 <option value="">Ordenar por:</option>
 <option value="seguidos">Siguiendo</option>
 <option value="ultimas">Últimas Publicaciones</option>
<option value="comentadas">Más Comentadas</option>
 <option value="gustadas">Más Gustadas</option>
</select>

<div class="contenedor_Publicaciones" id="contenedorPublicaciones">

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
<script src="../js/chat.js"></script> 
<script src="../js/mensajesNotis.js"></script>
<script src="../js/search.js"></script>
<script src="../js/script.js"></script>
<script src="../js/dashboard.js"></script>
<script src="../js/publicaciones_ordenadas.js"></script>


</body>
</html>