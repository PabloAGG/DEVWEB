<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: InicioSesion.php');
    exit();
}

$user_sesion = $_SESSION['user_name'];
$perfil_id = $_GET['id'] ?? null; // Obtener el ID del perfil a mostrar

if ($perfil_id === null) {
    echo "Error: No se ha especificado un ID de perfil.";
    exit(); // O podrías redirigir a una página de error
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
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>

<body class="cuerpo">

    <header>
        <div class="logo"> <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px"></a></div>
        <div class="barrPrin">
            <button onclick="location.href='dashboard.php'">Inicio</button>
            <button onclick="location.href='Perfil.php'">Perfil</button>
            <button onclick="location.href='BusqAv.php'"> Busq Av</button>
            <button onclick="location.href='../Back/LogOut.php'">Cerrar sesion</button>
        </div>
        <div class="search-container">
            <input type="text" class="search-bar" placeholder="Buscar...">
            <button class="search-button"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
        <div class="identificador">
            <button onclick="location.href='Perfil.php'"><?php echo $user_sesion; ?></button>
        </div>
    </header>

    <main>
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
                    echo '<img class="img-cirUs" src="data:' . $mime . ';base64,' . $base64 . '">';
                }
            } else {
                echo '<img id="imgPerfil" src="../assets/image_default.png" alt="Avatar Usuario" class="img-cirUs">';
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
            </ul>
        </div>

        <div class="contenedor_Publicaciones">
            <?php
            // Obtener las publicaciones del usuario del perfil
            $query = "SELECT p.*, m.contenido, m.tipo_Img, m.video, u.nomUs AS autor,
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
                                        <source src="<?php echo $mediaSrc; ?>" type="<?php echo $mime; ?>">
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
    </main>
    <script src="../js/search.js"></script>
    <script src="../js/script.js"></script>
    <script src="../js/likes.js"></script>
</body>

</html>