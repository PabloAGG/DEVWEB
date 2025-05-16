<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: InicioSesion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sqlUsuario = "SELECT * FROM datos_sesion WHERE idUsuario = ?";
$stmtUsuario = mysqli_prepare($conn, $sqlUsuario);
mysqli_stmt_bind_param($stmtUsuario, 'i', $user_id);
mysqli_stmt_execute($stmtUsuario);
$resultUsuario = mysqli_stmt_get_result($stmtUsuario);

if ($rowUsuario = mysqli_fetch_assoc($resultUsuario)) {
    $user_name = $rowUsuario['nomUs'];
    $full_name = $rowUsuario['nombre'];
    $user_email = $rowUsuario['correo'];
    $user_role = $rowUsuario['usAdmin'];
    $birth_date = $rowUsuario['nacimiento'];
} else {
    // Considera redirigir o mostrar un mensaje de error más amigable
    error_log("Error: No se encontró el usuario con ID: " . $user_id);
    die("Error: No se pudo cargar la información del usuario.");
}
mysqli_stmt_close($stmtUsuario);

// --- Validar y obtener ID de Publicación ---
$idPubli = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idPubli <= 0) {
    // Considera redirigir a una página de error o al dashboard
    die("ID de publicación inválido.");
}

// --- Obtener la Publicación específica ---
// La consulta ya incluye m.idMulti, lo cual es correcto.
$queryPublicacion = "SELECT p.*, m.idMulti, m.contenido, m.tipo_Img, m.video, u.nomUs AS autor,u.imagen AS autorImg,u.tipo_Img AS autorType ,FormatearFecha(p.fechaC) AS fecha_formateada,
  (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli AND idUsuario = ?) AS hasLiked
              FROM Publicaciones p
              JOIN Multimedia m ON m.idPubli = p.idPubli
              JOIN Usuarios u ON u.idUsuario = p.idUsuario
              WHERE p.estado = 1 AND p.idPubli=?";

$stmt = mysqli_prepare($conn, $queryPublicacion);
if (!$stmt) {
    error_log("Error al preparar la consulta de publicación: " . mysqli_error($conn));
    die("Error al cargar la publicación.");
}
mysqli_stmt_bind_param($stmt, "ii", $user_id, $idPubli);
mysqli_stmt_execute($stmt);
$resultPublicacion = mysqli_stmt_get_result($stmt);
$publicacion = mysqli_fetch_assoc($resultPublicacion);
mysqli_stmt_close($stmt);

if (!$publicacion) {
    // Considera redirigir a una página de error o al dashboard
    die("Publicación no encontrada o no accesible.");
}

$hasLiked = $publicacion['hasLiked'] > 0;
$numLikes = $publicacion['nLikes'];
$fechaFormateada = $publicacion['fecha_formateada'];

// Generar URL de ngrok dinámicamente si es posible, o configurarla de forma centralizada
$baseAppUrl = 'https://stork-holy-yeti.ngrok-free.app/DEVWEB'; // Ejemplo, idealmente configurable
$urlPublicacion = $baseAppUrl . '/front/publicacion.php?id=' . $publicacion['idPubli'];
$titulo = rawurlencode($publicacion['titulo']);
$mensaje = rawurlencode("¡Mira esta publicación que encontré! " . $publicacion['titulo'] . " " . $urlPublicacion);
$whatsappUrl = "https://wa.me/?text=" . $mensaje;

// --- Obtener Comentarios para la Publicación específica ---
$comentarios = [];
$stmtComentarios = $conn->prepare("
    SELECT c.*, u.nomUs, u.imagen AS imgComentador, u.tipo_Img AS tipoImgComentador, FormatearFecha(c.fechaC) AS fecha_formateada_comentario
    FROM Comentarios c
    JOIN Usuarios u ON c.idUsuario = u.idUsuario
    WHERE c.idPublicacion = ?
    ORDER BY c.fechaC DESC");

if ($stmtComentarios) {
    $stmtComentarios->bind_param("i", $idPubli);
    $stmtComentarios->execute();
    $resultComentarios = $stmtComentarios->get_result();
    while ($rowComentario = $resultComentarios->fetch_assoc()) {
        $comentarios[] = $rowComentario;
    }
    $stmtComentarios->close();
} else {
    error_log("Error al preparar consulta de comentarios: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($publicacion['titulo'] ?? 'Publicación'); ?> - DEVWEB</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/Publicacion.css">
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>
<body class="cuerpo">

 <header>
  <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px" alt="Logo DEVWEB"></a>  <h6 id="titulo">DEVWEB</h6>
  <div class="identificador">
   <button onclick="location.href='Perfil.php'"><?php echo htmlspecialchars($user_name); ?></button>
  </div>
 </header>
<main>
<?php
// Variables para la multimedia de la publicación principal
$idMultiPub = $publicacion['idMulti'] ?? null;
$mimePub = $publicacion['tipo_Img'] ?? 'application/octet-stream'; // Default MIME type
$isVideoPub = $publicacion['video'] ?? false;
$contenidoPub = $publicacion['contenido'] ?? null;
?>

<article class="card-container">
    <div class="card">
        <div class="card-header">
            <div class="">
        <?php if ($publicacion['autorImg'] !== null && isset($publicacion['autorType'])):
            $mimeUsuarioAutor = $publicacion['autorType'];
            $base64AutorImg = base64_encode($publicacion['autorImg']);
            echo '<img class="img-cirUs" src="data:' . htmlspecialchars($mimeUsuarioAutor) . ';base64,' . $base64AutorImg . '" alt="Avatar de ' . htmlspecialchars($publicacion['autor']) . '">';
        else: ?>
            <img src="../assets/image_default.png" alt="Avatar Usuario" class="img-cirUs">
        <?php endif; ?>
            <span class="autor"><a href="PerfilExt.php?id=<?php echo $publicacion['idUsuario']?>"><?php echo htmlspecialchars($publicacion['autor']); ?></a></span> </div>
            <span class="fecha"><?php echo htmlspecialchars($fechaFormateada); ?></span>
        </div>

        <div class="card-body">
            <h2><?php echo htmlspecialchars($publicacion['titulo']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($publicacion['descripcion'])); // nl2br para saltos de línea ?></p>

            <?php if ($isVideoPub && $idMultiPub):
                // Ruta al script de streaming. Ajusta si es necesario.
                $videoStreamUrlPub = '../back/stream_video.php?id=' . $idMultiPub;
            ?>
                <video class="media" controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($videoStreamUrlPub); ?>" type="<?php echo htmlspecialchars($mimePub); ?>">
                    Tu navegador no soporta el elemento de video.
                </video>
            <?php elseif (!$isVideoPub && $contenidoPub !== null):
                // Mostrar imagen usando Base64
                $imgBase64Pub = base64_encode($contenidoPub);
            ?>
                <img class="media" src="data:<?php echo htmlspecialchars($mimePub); ?>;base64,<?php echo $imgBase64Pub; ?>" alt="<?php echo htmlspecialchars($publicacion['titulo']); ?>">
            <?php else: ?>
                <p class="text-muted"><em>No hay multimedia disponible para esta publicación.</em></p>
            <?php endif; ?>
        </div>

        <div class="card-footer">
            <button class="btn like-btn <?php echo $hasLiked ? 'liked' : ''; ?>" data-idpubli="<?php echo $publicacion['idPubli']; ?>">
                <i class="fa-solid fa-thumbs-up"></i>
                <span class="like-text"><?php echo $hasLiked ? 'Te gusta' : 'Me gusta'; ?></span>
            </button>
            <span class="like-count">
                <?php echo $numLikes; ?>
            </span>
            <a class="btn share" href="<?php echo htmlspecialchars($whatsappUrl); ?>" target="_blank" rel="noopener noreferrer">
                <i class="fa-brands fa-whatsapp"></i> Compartir
            </a>
        </div>
    </div>

    <section class="comentarios-seccion">
        <h3>Comentarios</h3>
        <form id="form-comentario" onsubmit="guardarComentario(event); return false;">
            <input type="hidden" name="publi_id_comentario" value="<?php echo $idPubli; ?>">
            <textarea name="comentario" placeholder="Escribe un comentario..." required aria-label="Escribe un comentario"></textarea>
            <br>
            <div id="mensaje-comentario" class="mensaje-ajax" style="display:none;"></div>
            <button type="submit" class="btn-ver-mas"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
        </form>

        <div id="lista-comentarios">
            <?php if (empty($comentarios)): ?>
                <p>Sé el primero en comentar.</p>
            <?php else: ?>
                <?php foreach ($comentarios as $coment): ?>
                    <div class="comentario-item">
                        <div class="comenPresent">
                             <strong>
                        <?php if ($coment['imgComentador'] !== null && isset($coment['tipoImgComentador'])):
                            $mimeComentador = $coment['tipoImgComentador'];
                            $base64ComentadorImg = base64_encode($coment['imgComentador']);
                            echo '<img class="img-cirUs" src="data:' . htmlspecialchars($mimeComentador) . ';base64,' . $base64ComentadorImg . '" alt="Avatar de ' . htmlspecialchars($coment['nomUs']) . '">';
                        else: ?>
                            <img src="../assets/image_default.png" alt="Avatar Usuario" class="img-cirUs">
                        <?php endif; ?>
                        <?php echo htmlspecialchars($coment['nomUs']); ?>
                        </strong>
                        <span> (<?php echo htmlspecialchars($coment['fecha_formateada_comentario']); ?>):</span>
                       </div>
                        <p><?php echo nl2br(htmlspecialchars($coment['comen'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</article>

</main>
<script>
// Define la variable user_name para Publicacion_comen.js si es necesaria globalmente.
// Si Publicacion_comen.js la obtiene de otra forma (ej. un elemento en el DOM), esto no es necesario.
const currentUserName = "<?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>";
</script>
<script src="../js/search.js"></script>
<script src="../js/Publicacion_comen.js"></script>
<script src="../js/likes.js"></script>
</body>
</html>