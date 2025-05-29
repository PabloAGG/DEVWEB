<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
// Verificar si el usuario ha iniciado sesión       
if (!isset($_SESSION['user_id'])) {
    header('Location: InicioSesion.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

$user_id = $_SESSION['user_id'];
 $sql = "SELECT * FROM datos_sesion WHERE idUsuario = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $user_name = $row['nomUs'];
    $full_name = $row['nombre'];
    $user_email = $row['correo'];
    $user_role = $row['usAdmin']; 
    $birth_date = $row['nacimiento'];
} else {
    echo "Error: No se encontró el usuario.";
    exit();
}

 $numSeguidores=0;
 $numSeguidos=0;
$stmtContador = mysqli_prepare($conn, "SELECT COUNT(*) AS numSeguidores FROM Seguidores WHERE idSeguido = ?");
if ($stmtContador) {
    mysqli_stmt_bind_param($stmtContador, 'i', $user_id);
    mysqli_stmt_execute($stmtContador);
    $resultContador = mysqli_stmt_get_result($stmtContador);
    if ($rowContador = mysqli_fetch_assoc($resultContador)) {
        $numSeguidores = (int)$rowContador['numSeguidores'];
    }
    mysqli_stmt_close($stmtContador);
} else {
    error_log("Error al preparar statement para contar seguidores: " . mysqli_error($conn));
}
$stmtContador = mysqli_prepare($conn, "SELECT COUNT(*) AS numSeguidores FROM Seguidores WHERE idSeguidor = ?");
if ($stmtContador) {
    mysqli_stmt_bind_param($stmtContador, 'i', $user_id);
    mysqli_stmt_execute($stmtContador);
    $resultContador = mysqli_stmt_get_result($stmtContador);
    if ($rowContador = mysqli_fetch_assoc($resultContador)) {
        $numSeguidos = (int)$rowContador['numSeguidores'];
    }
    mysqli_stmt_close($stmtContador);
} else {
    error_log("Error al preparar statement para contar seguidores: " . mysqli_error($conn));
}

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
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/Perfil.css">
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




<div class="perfilUs">

<?php   
$sqlMultimedia = "SELECT * FROM Usuarios WHERE idUsuario = $user_id";
$resultadoMultimedia = $conn->query($sqlMultimedia);

if ($resultadoMultimedia && $resultadoMultimedia->num_rows > 0) {
    if ($media = $resultadoMultimedia->fetch_assoc()) {

        if($media['imagen']===null){
    
echo '<img id="imgPerfil" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs">';
 }   else{
    
        $mime = $media['tipo_Img'] ?? 'image/png';
        $base64 = base64_encode($media['imagen']);

        echo '<img class="img-cirUs" src="data:' . $mime . ';base64,' . $base64 . '">';
 }
    }
} ?> 

    <ul id="list-perfil">
        <li><strong>Nombre Usuario:</strong><?php echo $user_name?></li>
        <li><strong>Nombre:</strong> <?php echo $full_name?></li>
        <li><strong>Correo:</strong> <?php echo $user_email?></li>
        <li><strong>Edad:</strong> <?php 
        $fechaActual = new DateTime(); // Fecha actual
        $fechaNacimiento = new DateTime($birth_date); // Fecha de nacimiento
        $edad = $fechaActual->diff($fechaNacimiento)->y; // Calcular la diferencia en años
        echo $edad . " años"; ?></li>
        <li><strong>Rol:</strong> <?php echo $user_role == 1 ? 'Administrador' :'Usuario' ; ?></li>
       <li>
        <strong><a href="Seguidos.php?show=follows">Seguidos:</a></strong><span id="follow-count"><?php echo $numSeguidos; ?></span> 
        
        <strong><a href="Seguidos.php?show=followers">Seguidores:</a></strong><span id="follower-count"><?php echo $numSeguidores; ?></span>
    </li>
  
</ul>

    <div class="btns-perfil">
    <button class="btnEx" onclick="location.href='EditData.php'">Modificar datos</button></div>
<?php
if($user_role == 1 ){ ?>
  <div class="btns-perfil"> <button class="btnEx" onclick="location.href='../Back/Admin.php'">Panel</button> </div>
<?php } ?>

</div>

<div class="contenedor_Publicaciones">
<?php
$query = "SELECT p.*, m.contenido, m.tipo_Img, m.video,m.video_path, u.nomUs AS autor
          FROM Publicaciones p
          JOIN Multimedia m ON m.idPubli = p.idPubli
          JOIN Usuarios u ON u.idUsuario = p.idUsuario
          WHERE p.estado = 1 AND p.idUsuario=?
          ORDER BY p.fechaC DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $mime = $row['tipo_Img'] ?? 'image/png';
    $isVideo = $row['video'];
    $video = $row['video_path'] ?? null; // Ruta del video si es un video
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']);
?>
<div class="card-container">
    <div class="card">
        <div class="card-header">
            <span class="autor"><?php echo htmlspecialchars($row['autor']); ?></span>
            <span class="fecha"><?php echo htmlspecialchars($row['fechaC']); ?></span>
        </div>

        <div class="card-body">
            <h2><?php echo htmlspecialchars($row['titulo']); ?></h2>
            <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
            <?php if ($isVideo): ?>
                <video class="media" controls>
                    <source src="<?php echo $video; ?>" type="<?php echo $mime; ?>">
                    Tu navegador no soporta video.
                </video>
            <?php else: ?>
                <img class="media" src="<?php echo $mediaSrc; ?>" alt="Contenido multimedia">
            <?php endif; ?>
        </div>

        <div class="card-footer">
            <button class="btn drop" onclick="window.location.href='../Back/bajaPublicaciones.php?id=<?php echo $row['idPubli']?>'"><i class="fa-solid fa-trash"></i> Eliminar</button>
            
        
        </div>
    </div>
</div>
<?php } ?>

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
 <script src="../js/notis.js"></script>
<script src="../js/search.js"></script>
  <script src="../js/script.js"></script>
</body>
</html>