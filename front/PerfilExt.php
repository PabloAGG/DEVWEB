<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: InicioSesion.php');
    exit();
}

$user_sesion_id=$_SESSION['user_id'];
$user_sesion = $_SESSION['user_name'];
$perfil_id = $_GET['id'] ?? null; // Obtener el ID del perfil a mostrar}


if($perfil_id == $user_sesion_id){
     header('Location: Perfil.php');
     exit();
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Error desconocido.', 'esSeguidor' => false, 'numSeguidores' => 0];

    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Usuario no autenticado.';
        echo json_encode($response);
        exit();
    }

    $idUsuarioSesion = $_SESSION['user_id'];
    $idPerfilVisitado = isset($_POST['perfil_id_accion']) ? (int)$_POST['perfil_id_accion'] : 0;
    $accion = isset($_POST['accion_seguir']) ? $_POST['accion_seguir'] : '';

    if ($idPerfilVisitado <= 0) {
        $response['message'] = 'ID de perfil inválido.';
        echo json_encode($response);
        exit();
    }

    if ($idUsuarioSesion == $idPerfilVisitado) {
        $response['message'] = 'No puedes seguirte a ti mismo.';
        // También obtener el estado actual para la respuesta aunque la acción no se realice
        $stmtCheckFollow = mysqli_prepare($conn, "SELECT idSeguidores FROM Seguidores WHERE idSeguido = ? AND idSeguidor = ?");
        mysqli_stmt_bind_param($stmtCheckFollow, 'ii', $idPerfilVisitado, $idUsuarioSesion);
        mysqli_stmt_execute($stmtCheckFollow);
        mysqli_stmt_store_result($stmtCheckFollow);
        $response['esSeguidor'] = mysqli_stmt_num_rows($stmtCheckFollow) > 0;
        mysqli_stmt_close($stmtCheckFollow);

        $stmtCount = mysqli_prepare($conn, "SELECT COUNT(*) AS numSeguidores FROM Seguidores WHERE idSeguido = ?");
        mysqli_stmt_bind_param($stmtCount, 'i', $idPerfilVisitado);
        mysqli_stmt_execute($stmtCount);
        $resultCount = mysqli_stmt_get_result($stmtCount);
        if ($rowCount = mysqli_fetch_assoc($resultCount)) {
            $response['numSeguidores'] = (int)$rowCount['numSeguidores'];
        }
        mysqli_stmt_close($stmtCount);
        echo json_encode($response);
        exit();
    }

    $dbActionSuccess = false;

    if ($accion === 'seguir') {
        $stmtCheck = mysqli_prepare($conn, "SELECT idSeguidores FROM Seguidores WHERE idSeguido = ? AND idSeguidor = ?");
        mysqli_stmt_bind_param($stmtCheck, 'ii', $idPerfilVisitado, $idUsuarioSesion);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);
        if (mysqli_stmt_num_rows($stmtCheck) == 0) {
            mysqli_stmt_close($stmtCheck);
            $stmtInsertSeguidor = mysqli_prepare($conn, "INSERT INTO Seguidores (idSeguido, idSeguidor) VALUES (?, ?)");
            if ($stmtInsertSeguidor) {
                mysqli_stmt_bind_param($stmtInsertSeguidor, 'ii', $idPerfilVisitado, $idUsuarioSesion);
                if (mysqli_stmt_execute($stmtInsertSeguidor)) {
                    $dbActionSuccess = true;
                } 
                if ($dbActionSuccess) 
                { // Asegúrate que la acción de seguir fue realmente exitosa y es un nuevo seguimiento
    // Crear notificación para el usuario que está SIENDO SEGUIDO
    $idUsuarioQueSigue = $idUsuarioSesion; // Usuario que realiza la acción (el que está en sesión)
    $nombreUsuarioQueSigue = $_SESSION['user_name']; // Nombre del usuario que sigue
    $idUsuarioSeguido = $idPerfilVisitado; // Usuario que es seguido (el perfil que se está visitando)

    $mensajeNotificacion = htmlspecialchars($nombreUsuarioQueSigue) . " ha comenzado a seguirte.";
    $tipoNotificacion = 'follow'; // El nuevo tipo que añadiste al ENUM

    // Preparar la inserción de la notificación
    // idPublicacion será NULL para este tipo de notificación
    $stmtNotif = mysqli_prepare($conn, "INSERT INTO Notificaciones (idUsuarioRecibe, idUsuarioEmite, tipo, mensaje, idPublicacion) VALUES (?, ?, ?, ?, NULL)");
    if ($stmtNotif) {
        mysqli_stmt_bind_param($stmtNotif, 'iiss', $idUsuarioSeguido, $idUsuarioQueSigue, $tipoNotificacion, $mensajeNotificacion);
        mysqli_stmt_execute($stmtNotif);
        mysqli_stmt_close($stmtNotif);
    } else {
        // Opcional: Registrar error si la preparación de la notificación falla
        error_log("Error al preparar la notificación de seguimiento: " . mysqli_error($conn));
    }
}
                
                
                else {
                    $response['message'] = 'Error al intentar seguir al usuario: ' . mysqli_stmt_error($stmtInsertSeguidor);
                }
                mysqli_stmt_close($stmtInsertSeguidor);
            } else {
                 $response['message'] = 'Error al preparar la consulta para seguir.';
            }
        } else {
            mysqli_stmt_close($stmtCheck);
            $dbActionSuccess = true; // Ya lo sigue, consideramos la acción "exitosa" en términos de estado final
            $response['message'] = 'Ya sigues a este usuario.';
        }
    } elseif ($accion === 'dejar_de_seguir') {
        $stmtDeleteSeguidor = mysqli_prepare($conn, "DELETE FROM Seguidores WHERE idSeguido = ? AND idSeguidor = ?");
        if ($stmtDeleteSeguidor) {
            mysqli_stmt_bind_param($stmtDeleteSeguidor, 'ii', $idPerfilVisitado, $idUsuarioSesion);
            if (mysqli_stmt_execute($stmtDeleteSeguidor)) {
                $dbActionSuccess = true;
            } else {
                 $response['message'] = 'Error al intentar dejar de seguir al usuario: ' . mysqli_stmt_error($stmtDeleteSeguidor);
            }
            mysqli_stmt_close($stmtDeleteSeguidor);
        } else {
            $response['message'] = 'Error al preparar la consulta para dejar de seguir.';
        }
    } else {
        $response['message'] = 'Acción no válida.';
    }

    // Siempre recalcular y enviar el estado actual
    $stmtCheckFollowCurrent = mysqli_prepare($conn, "SELECT idSeguidores FROM Seguidores WHERE idSeguido = ? AND idSeguidor = ?");
    mysqli_stmt_bind_param($stmtCheckFollowCurrent, 'ii', $idPerfilVisitado, $idUsuarioSesion);
    mysqli_stmt_execute($stmtCheckFollowCurrent);
    mysqli_stmt_store_result($stmtCheckFollowCurrent);
    $response['esSeguidor'] = mysqli_stmt_num_rows($stmtCheckFollowCurrent) > 0;
    mysqli_stmt_close($stmtCheckFollowCurrent);

    $stmtCountCurrent = mysqli_prepare($conn, "SELECT COUNT(*) AS numSeguidores FROM Seguidores WHERE idSeguido = ?");
    mysqli_stmt_bind_param($stmtCountCurrent, 'i', $idPerfilVisitado);
    mysqli_stmt_execute($stmtCountCurrent);
    $resultCountCurrent = mysqli_stmt_get_result($stmtCountCurrent);
    if ($rowCountCurrent = mysqli_fetch_assoc($resultCountCurrent)) {
        $response['numSeguidores'] = (int)$rowCountCurrent['numSeguidores'];
    }
    mysqli_stmt_close($stmtCountCurrent);
    
    if ($dbActionSuccess) {
        $response['success'] = true;
        // Si el mensaje no se estableció previamente (como 'Ya sigues a este usuario'), poner uno genérico de éxito.
        if ($response['message'] === 'Error desconocido.' || $response['message'] === 'Error al actualizar la base de datos.') {
            $response['message'] = $accion === 'seguir' ? 'Ahora sigues a este usuario.' : 'Has dejado de seguir a este usuario.';
        }
    } else {
        // Si dbActionSuccess es false, el mensaje de error ya debería estar establecido.
        // Si no, poner uno genérico.
         if ($response['message'] === 'Error desconocido.') {
            $response['message'] = 'No se pudo completar la acción en la base de datos.';
         }
    }

    echo json_encode($response);
    exit();
}


// Obtener datos del usuario del perfil
$sql = "SELECT * FROM datos_sesion WHERE idUsuario = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $perfil_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $user_name = htmlspecialchars($row['nomUs']);
    $full_name = htmlspecialchars($row['nombre']);
    $user_email = htmlspecialchars($row['correo']);
    $user_role = htmlspecialchars($row['usAdmin']);
    $birth_date = htmlspecialchars($row['nacimiento']);
} else {
    echo "Error: No se encontró el usuario.";
    exit();
}
$esSeguidor = false;
$numSeguidores = 0;

// Verificar si el usuario de la sesión sigue al usuario del perfil (solo si no es el mismo perfil)
if ($user_sesion_id != $perfil_id) {
    $stmtSigue = mysqli_prepare($conn, "SELECT idSeguidores FROM Seguidores WHERE idSeguido = ? AND idSeguidor = ?");
    if ($stmtSigue) {
        mysqli_stmt_bind_param($stmtSigue, 'ii', $perfil_id, $user_sesion_id);
        mysqli_stmt_execute($stmtSigue);
        mysqli_stmt_store_result($stmtSigue);
        if (mysqli_stmt_num_rows($stmtSigue) > 0) {
            $esSeguidor = true;
        }
        mysqli_stmt_close($stmtSigue);
    } else {
        error_log("Error al preparar statement para verificar seguimiento: " . mysqli_error($conn));
    }
}

// Obtener el número de seguidores del usuario del perfil
$stmtContador = mysqli_prepare($conn, "SELECT COUNT(*) AS numSeguidores FROM Seguidores WHERE idSeguido = ?");
if ($stmtContador) {
    mysqli_stmt_bind_param($stmtContador, 'i', $perfil_id);
    mysqli_stmt_execute($stmtContador);
    $resultContador = mysqli_stmt_get_result($stmtContador);
    if ($rowContador = mysqli_fetch_assoc($resultContador)) {
        $numSeguidores = (int)$rowContador['numSeguidores'];
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
    <title>Perfil de <?php echo $user_name; ?></title>
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
            // Obtener la imagen del perfil
            $sqlMultimedia = "SELECT * FROM Usuarios WHERE idUsuario = ?";
            $stmtMultimedia = mysqli_prepare($conn, $sqlMultimedia);
            mysqli_stmt_bind_param($stmtMultimedia, 'i', $perfil_id);
            mysqli_stmt_execute($stmtMultimedia);
            $resultadoMultimedia = mysqli_stmt_get_result($stmtMultimedia);

            if ($resultadoMultimedia ) {
                if ($media = mysqli_fetch_assoc($resultadoMultimedia)) {
                    $mime = htmlspecialchars($media['tipo_Img'] ?? 'image/png');
                    $base64 = base64_encode($media['imagen']);
                    if($media['imagen']===null){
                        echo '<img id="imgPerfil" src="../assets/image_default.png" alt="Avatar Usuario" class="img-cirUs">';
                     }else{
                         echo '<img class="img-cirUs" src="data:' . $mime . ';base64,' . $base64 . '">';
                }
            } 
        }
            
            ?>
            <ul id="list-perfil">
                <li><strong>Nombre Usuario:</strong><?php echo $user_name; ?></li>
                <li><strong>Nombre:</strong> <?php echo $full_name; ?></li>
                <li><strong>Correo:</strong> <?php echo $user_email; ?></li>
                <li><strong>Edad:</strong> <?php
                                            $fechaActual = new DateTime();
                                            $fechaNacimiento = new DateTime($birth_date);
                                            $edad = $fechaActual->diff($fechaNacimiento)->y;
                                            echo htmlspecialchars($edad . " años"); ?></li>
                <li><strong>Rol:</strong> <?php echo $user_role == 1 ? 'Administrador' : 'Usuario'; ?></li>
                 <li><strong>Seguidores:</strong> <span id="follower-count"><?php echo $numSeguidores; ?></span></li>
            </ul>

         
               <button id="btnAccionSeguir" type="button"
                        class="btn-accion-perfil <?php echo $esSeguidor ? 'seguido' : 'seguir'; ?>"
                        data-perfil-id="<?php echo $perfil_id; ?>"
                        data-accion="<?php echo $esSeguidor ? 'dejar_de_seguir' : 'seguir'; ?>">
                    <i id="iconBtnAccionSeguir" class="fa-solid <?php echo $esSeguidor ? 'fa-user-minus' : 'fa-user-plus'; ?>"></i>
                    <span id="textoBtnAccionSeguir"><?php echo $esSeguidor ? ' Dejar de seguir' : ' Seguir'; ?></span>
                </button>
          
        </div>

        <div class="contenedor_Publicaciones">
            <?php
            // Obtener las publicaciones del usuario del perfil
            $query = "SELECT p.*, m.contenido, m.tipo_Img, m.video,m.video_path, u.nomUs AS autor,
                    (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios,
                    (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli AND idUsuario = ?) AS hasLiked,
                    FormatearFecha(p.fechaC) AS fecha_formateada,
                    p.nLikes AS numLikes
                  FROM Publicaciones p
                  JOIN Multimedia m ON m.idPubli = p.idPubli
                  JOIN Usuarios u ON u.idUsuario = p.idUsuario
                  WHERE p.estado = 1 AND p.idUsuario = ?
                  ORDER BY p.fechaC DESC";

            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, 'ii', $_SESSION['user_id'], $perfil_id); // Usar $_SESSION['user_id'] y $perfil_id
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $mime = htmlspecialchars($row['tipo_Img'] ?? 'image/png');
                    $isVideo = htmlspecialchars($row['video']);
                    $video = htmlspecialchars($row['video_path'] ?? null); // Ruta del video si es un video
                    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']);
                    $baseAppUrl = 'https://stork-holy-yeti.ngrok-free.app/DEVWEB'; // Reemplazar con tu URL base
                    $urlPublicacion = $baseAppUrl . '/front/publicacion.php?id=' . htmlspecialchars($row['idPubli']);
                    $titulo = rawurlencode(htmlspecialchars($row['titulo']));
                    $mensaje = rawurlencode("¡Mira esta publicación que encontré! " . htmlspecialchars($row['titulo']) . " " . $urlPublicacion);
                    $whatsappUrl = "https://wa.me/?text=" . $mensaje;
                    $hasLiked = htmlspecialchars($row['hasLiked']) > 0;
                    $numComentarios = htmlspecialchars($row['comentarios']);
                    $numLikes = htmlspecialchars($row['numLikes']);
                    ?>
                    <div class="card-container">
                        <div class="card">
                            <div class="card-header">
                                <span class="autor"><?php echo htmlspecialchars($row['autor']); ?></span>
                                <span class="fecha"><?php echo htmlspecialchars($row['fecha_formateada']); ?></span>
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
                                    <img class="media" src= "<?php echo $mediaSrc; ?>" alt="Contenido multimedia">
                                <?php endif; ?>
                            </div>

                            <div class="card-footer">
                                <?php if (isset($_GET['id']) && intval($_GET['id']) == $_SESSION['user_id']) { ?>
                                    <button class="btn drop" onclick="window.location.href='../Back/bajaPublicaciones.php?id=<?php echo htmlspecialchars($row['idPubli']); ?>'">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </button>
                                <?php } else { ?>
                                    <button class="btn like-btn <?php echo $hasLiked ? 'liked' : ''; ?>" data-idpubli="<?php echo htmlspecialchars($row['idPubli']); ?>">
                                        <i class="fa-solid fa-thumbs-up"></i>
                                        <span class="like-text"><?php echo $hasLiked ? 'Te gusta' : 'Me gusta'; ?></span>
                                    </button>
                                    <span class="like-count">
                                        <?php echo $numLikes; ?>
                                    </span>
                                    <button class="btn comment" onclick="window.location.href='publicacion.php?id=<?php echo htmlspecialchars($row['idPubli']); ?>'">
                                        <i class="fa-solid fa-comment"></i> Comentar
                                    </button>
                                    <span class="Coment-count"><?php echo $numComentarios; ?></span>
                                    <a class="btn share" href="<?php echo htmlspecialchars($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer">
                                        <i class="fa-brands fa-whatsapp"></i> Compartir
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
            <?php
                }
            } else {
                echo "Error al obtener las publicaciones.";
            }
            ?>
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
    <script src="../js/search.js"></script>
    <script src="../js/script.js"></script>
    <script src="../js/likes.js"></script>
     <script src="../js/perfilext.js"></script>
</body>

</html>