<?php
session_start();
require '../Back/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: InicioSesion.php');
    exit();
}

$user_sesion_id = $_SESSION['user_id'];
$user_sesion_name = $_SESSION['user_name'] ?? 'Usuario'; // Para la barra superior

// ---- INICIO: Manejo de acción Dejar de seguir con AJAX ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'true' && isset($_POST['accion_dejar_de_seguir'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Error desconocido.'];

    if (!isset($_POST['perfil_id_accion'])) {
        $response['message'] = 'ID de perfil no proporcionado.';
        echo json_encode($response);
        exit();
    }

    $idPerfilADejarDeSeguir = (int)$_POST['perfil_id_accion'];

    if ($idPerfilADejarDeSeguir <= 0) {
        $response['message'] = 'ID de perfil inválido.';
        echo json_encode($response);
        exit();
    }

    // El usuario de la sesión es el que deja de seguir (idSeguidor)
    // El perfil_id_accion es el usuario que era seguido (idSeguido)
    $stmtDelete = mysqli_prepare($conn, "DELETE FROM Seguidores WHERE idSeguido = ? AND idSeguidor = ?");
    if ($stmtDelete) {
        mysqli_stmt_bind_param($stmtDelete, 'ii', $idPerfilADejarDeSeguir, $user_sesion_id);
        if (mysqli_stmt_execute($stmtDelete)) {
            if (mysqli_stmt_affected_rows($stmtDelete) > 0) {
                $response['success'] = true;
                $response['message'] = 'Has dejado de seguir a este usuario.';
            } else {
                $response['message'] = 'No estabas siguiendo a este usuario o ya lo dejaste de seguir.';
                // Considerar success true si el estado final es "no siguiendo"
                 $response['success'] = true; // O false, dependiendo de cómo quieras manejarlo
            }
        } else {
            $response['message'] = 'Error al ejecutar la acción: ' . mysqli_stmt_error($stmtDelete);
        }
        mysqli_stmt_close($stmtDelete);
    } else {
        $response['message'] = 'Error al preparar la consulta para dejar de seguir.';
    }

    echo json_encode($response);
    exit();
}
// ---- FIN: Manejo de acción Dejar de seguir con AJAX ----


$show_mode = isset($_GET['show']) ? $_GET['show'] : 'follows'; // Por defecto muestra a quién sigue
$page_title = "Usuarios";
$list_users = [];

if ($show_mode === 'follows') {
    $page_title = "Personas que sigues";
    // Obtener usuarios a los que SIGUE el usuario actual (user_sesion_id es el idSeguidor)
    $sql_list = "SELECT u.idUsuario, u.nomUs, u.nombre, u.imagen, u.tipo_Img 
                 FROM Usuarios u 
                 JOIN Seguidores s ON u.idUsuario = s.idSeguido 
                 WHERE s.idSeguidor = ?";
    $stmt_list = mysqli_prepare($conn, $sql_list);
    mysqli_stmt_bind_param($stmt_list, 'i', $user_sesion_id);
} elseif ($show_mode === 'followers') {
    $page_title = "Personas que te siguen";
    // Obtener usuarios que SIGUEN AL usuario actual (user_sesion_id es el idSeguido)
    $sql_list = "SELECT u.idUsuario, u.nomUs, u.nombre, u.imagen, u.tipo_Img 
                 FROM Usuarios u 
                 JOIN Seguidores s ON u.idUsuario = s.idSeguidor 
                 WHERE s.idSeguido = ?";
    $stmt_list = mysqli_prepare($conn, $sql_list);
    mysqli_stmt_bind_param($stmt_list, 'i', $user_sesion_id);
} else {
    // Modo no válido, redirigir o mostrar error
    header('Location: Perfil.php');
    exit();
}

if (isset($stmt_list)) {
    mysqli_stmt_execute($stmt_list);
    $result_list = mysqli_stmt_get_result($stmt_list);
    while ($row_user = mysqli_fetch_assoc($result_list)) {
        $list_users[] = $row_user;
    }
    mysqli_stmt_close($stmt_list);
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="../css/estiloslog.css"> <link rel="stylesheet" href="../css/Perfil.css">
    <link rel="stylesheet" href="../css/Dashboard.css">
    <link rel="stylesheet" href="../css/seguidos.css">
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


       <button id="openChatButton" title="Abrir Chat">
    <i class="fas fa-comments"></i> </button>

    <div id="chatFollowersModal" class="chat-modal" style="display:none;">
    <div class="chat-modal-content">
        <span class="chat-close-button" onclick="document.getElementById('chatFollowersModal').style.display='none'">&times;</span>
        <h2>Iniciar chat con:</h2>
        <div id="chatFollowersList" class="chat-user-list">
            </div>
    </div>
</div>


 <div class="identificador">
 <button onclick="location.href='Perfil.php'"><?php echo htmlspecialchars($user_sesion_name); ?></button>
 </div>
</header>

    <main>
        <div class="user-list-container">
            <h1><?php echo htmlspecialchars($page_title); ?></h1>
            <?php if (!empty($list_users)): ?>
                <ul class="user-list">
                    <?php foreach ($list_users as $user): ?>
                        <li class="user-list-item" id="user-item-<?php echo htmlspecialchars($user['idUsuario']); ?>">
                            <?php
                            $imagenSrc = '../assets/image_default.png'; // Imagen por defecto
                            if (!empty($user['imagen']) && !empty($user['tipo_Img'])) {
                                $mime = htmlspecialchars($user['tipo_Img']);
                                $base64 = base64_encode($user['imagen']);
                                $imagenSrc = 'data:' . $mime . ';base64,' . $base64;
                            }
                            ?>
                            <img src="<?php echo $imagenSrc; ?>" alt="Foto de perfil de <?php echo htmlspecialchars($user['nomUs']); ?>" class="profile-pic-small">
                            <div class="user-info">
                                <a href="PerfilExt.php?id=<?php echo htmlspecialchars($user['idUsuario']); ?>">
                                    <?php echo htmlspecialchars($user['nombre']); ?>
                                </a>
                                <div class="username">@<?php echo htmlspecialchars($user['nomUs']); ?></div>
                            </div>
                            <?php if ($show_mode === 'follows'): // Mostrar botón "Dejar de seguir" solo en la lista de "siguiendo" ?>
                                <button class="btn-unfollow" data-userid="<?php echo htmlspecialchars($user['idUsuario']); ?>">
                                    <i class="fa-solid fa-user-minus"></i> Dejar de seguir
                                </button>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="no-users">
                    <?php 
                    if ($show_mode === 'follows') {
                        echo "No sigues a nadie todavía.";
                    } else {
                        echo "Nadie te sigue todavía.";
                    }
                    ?>
                </p>
            <?php endif; ?>
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
    <script src="../js/script.js"></script>
    <script src="../js/search.js"></script>
   <script  src="../js/seguidos.js"></script>
</body>
</html>
<?php
mysqli_close($conn);
?>
