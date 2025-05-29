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
    $profile_image = $row['imagen'];
   $user_role = $row['usAdmin']; 
    $birth_date = $row['nacimiento'];
 } else {
    echo "Error: No se encontró el usuario.";
    exit();
 }
 $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : ''; // Cambiar '0' por '' (cadena vacía)
 $categoria_seleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : '';
 
 // Construir la consulta SQL dinámicamente
 $query = "SELECT p.*, m.contenido, m.tipo_Img, m.video,m.video_path, u.nomUs AS autor, u.imagen AS imgPerfil, u.tipo_Img AS tipo_ImgUser,
           FormatearFecha(p.fechaC) AS fecha_formateada,
           (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios,
           (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli AND idUsuario = ?) AS hasLiked,
           p.nLikes
           FROM Publicaciones p
           JOIN Multimedia m ON m.idPubli = p.idPubli
           JOIN Usuarios u ON u.idUsuario = p.idUsuario
           WHERE p.estado = 1";
 
 $params = array("i", $user_id); // Inicializar con el tipo 'i' para user_id
 $param_values = array(&$user_id);
 
 if (!empty($busqueda)) {
     $query .= " AND (p.titulo LIKE ? OR p.descripcion LIKE ? OR u.nomUs LIKE ?)";
     $params[0] .= "sss"; // Añadir tipos para los parámetros de búsqueda (strings)
     $busqueda_param = '%' . $busqueda . '%';
     $param_values[] = &$busqueda_param;
     $param_values[] = &$busqueda_param;
     $param_values[] = &$busqueda_param;
 }
 
 if (!empty($categoria_seleccionada)) {
     $query .= " AND p.categoria = ?";
     $params[0] .= "s"; // Añadir tipo para el parámetro de categoría (string)
     $param_values[] = &$categoria_seleccionada;
 }
 
 $query .= " ORDER BY p.fechaC DESC";
 
 $stmt = mysqli_prepare($conn, $query);
 
 // Usar la función array_merge para combinar los tipos y valores de los parámetros
 $bind_params = array_merge(array($params[0]), $param_values);
 // Llama a la función mysqli_stmt_bind_param directamente
 call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt), $bind_params));
 
 mysqli_stmt_execute($stmt);
 $resultBusq = mysqli_stmt_get_result($stmt);
 
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
<link rel="stylesheet" href="../css/BusqAv.css">
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



<label for="categoria-select">Filtrar por Categoría:</label>
<select name="categoria" id="categoria-select">
    <option value="">Todas las Categorías</option>
    <?php
    $query_categorias = "SELECT * FROM Categorias";
    $stmt_categorias = mysqli_prepare($conn, $query_categorias);
    mysqli_stmt_execute($stmt_categorias);
    $result_categorias = mysqli_stmt_get_result($stmt_categorias);
    while ($row_categoria = mysqli_fetch_assoc($result_categorias)) {
        $selected = ($categoria_seleccionada == $row_categoria['nombre']) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($row_categoria['nombre']) . '" ' . $selected . '>' . htmlspecialchars($row_categoria['nombre']) . '</option>';
    }
    ?>
</select>
<br>

<div class="contenedor_publicaciones">
  <h3>Resultados de busqueda "<?php echo $busqueda?>"</h3>
<?php
while ($row = mysqli_fetch_assoc($resultBusq)) { 
    $mime = $row['tipo_Img'] ?? 'image/png'; 
    $isVideo = $row['video']; 
    $video = $row['video_path'] ?? null; // Ruta del video si es un video
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']); 
    $hasLiked = $row['hasLiked'] > 0; 
    $numLikes = $row['nLikes']; 
    $fechaFormateada = $row['fecha_formateada']; 
    $numComentarios = $row['comentarios']; 
?> 
<div class="card-container"> 
    <div class="card"> 
        <div class="card-header"> 
            <div class="userPres"> 
        <?php if ($row['imgPerfil']!==null) { 

$mimeusuario = $row['tipo_ImgUser'] ?? 'image/png'; 
$base64 = base64_encode($row['imgPerfil']); 

echo '<img class="img-cirUs" src="data:' . $mimeusuario . ';base64,' . $base64 . '">'; 

} else {?> 

<img id="imgPerfil" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs"> 
<?php  }   ?> 
            <span class="autor"><a href="PerfilExt.php?id=<?php echo $row['idUsuario']?>"><?php echo htmlspecialchars($row['autor']); ?></a></span></div> 
            <span class="fecha"><?php echo htmlspecialchars($fechaFormateada); ?></span> 
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
        <button class="btn like-btn <?php echo $hasLiked ? 'liked' : ''; ?>" data-idpubli="<?php echo $row['idPubli']; ?>"> 
    <i class="fa-solid fa-thumbs-up"></i> 
    <span class="like-text"><?php echo $hasLiked ? 'Te gusta' : 'Me gusta'; ?></span> 
</button> 
<span class="like-count"> 
         <?php echo $numLikes; ?> 
    </span> 
            <button class="btn comment" onclick="window.location.href='publicacion.php?id=<?php echo $row['idPubli']; ?>'"><i class="fa-solid fa-comment"></i> Comentar</button> 
            <span class="Coment-count"> 
         <?php echo $numComentarios; ?> 
    </span> 
            <button class="btn share"><i class="fa-solid fa-share"></i> Compartir</button> 
        </div> 
    </div> 
</div> 
<?php } ?> 

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
 <script src="../js/notis.js"></script>
<script src="../js/BusqAv.js"></script>
<script src="../js/search.js"></script>
        <script src="../js/script.js"></script>
</body>
</html>