<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: InicioSesion.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sqlUsuario = "SELECT nomUs, nombre,correo, imagen,tipo_Img, usAdmin,nacimiento FROM Usuarios WHERE idUsuario = ?";
$stmtUsuario = mysqli_prepare($conn, $sqlUsuario);
mysqli_stmt_bind_param($stmtUsuario, 'i', $user_id);
mysqli_stmt_execute($stmtUsuario);
$resultUsuario = mysqli_stmt_get_result($stmtUsuario);

if ($rowUsuario = mysqli_fetch_assoc($resultUsuario)) {
    $user_name = $rowUsuario['nomUs'];
    $full_name = $rowUsuario['nombre'];
    $user_email = $rowUsuario['correo'];
    $profile_image = $rowUsuario['imagen'];
    $img_type = $rowUsuario['tipo_Img'];
    $user_role = $rowUsuario['usAdmin'];
    $birth_date = $rowUsuario['nacimiento'];
} else {
    echo "Error: No se encontró el usuario.";
    exit();
}
mysqli_stmt_close($stmtUsuario);

// --- Validar y obtener ID de Publicación ---
$idPubli = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($idPubli <= 0) {
    die("ID de publicación inválido.");
}

// --- Obtener la Publicación específica ---
$queryPublicacion = "SELECT p.*, m.contenido, m.tipo_Img, m.video, u.nomUs AS autor,
--  (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli) AS likes,
--           (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios,
--           (SELECT COUNT(*) FROM Compartidos WHERE idPublicacion = p.idPubli) AS compartidos,
  (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli AND idUsuario = ?) AS hasLiked
              FROM Publicaciones p
              JOIN Multimedia m ON m.idPubli = p.idPubli
              JOIN Usuarios u ON u.idUsuario = p.idUsuario
              WHERE p.estado = 1 AND p.idPubli=?";


$stmt = mysqli_prepare($conn, $queryPublicacion);
mysqli_stmt_bind_param($stmt, "ii", $user_id, $idPubli); // Bind del ID del usuario y el ID de la publicación
mysqli_stmt_execute($stmt);
$resultPublicacion = mysqli_stmt_get_result($stmt);
$publicacion = mysqli_fetch_assoc($resultPublicacion); // Obtener una sola fila
mysqli_stmt_close($stmt);

if (!$publicacion) {
    die("Publicación no encontrada.");
}

$hasLiked = $publicacion['hasLiked'] > 0;
$numLikes = $publicacion['nLikes'];

// --- Obtener Comentarios para la Publicación específica ---
$comentarios = []; // Inicializar como array vacío
$stmtComentarios = $conn->prepare("
    SELECT c.comen, u.nomUs,u.imagen,u.tipo_Img, c.fechaC
    FROM Comentarios c
    JOIN Usuarios u ON c.idUsuario = u.idUsuario
    WHERE c.idPublicacion = ? ORDER BY c.fechaC DESC
");
if ($stmtComentarios) {
    $stmtComentarios->bind_param("i", $idPubli);
    $stmtComentarios->execute();
    $resultComentarios = $stmtComentarios->get_result();
    // Guardar comentarios en un array para usar después
    while ($rowComentario = $resultComentarios->fetch_assoc()) {
        $comentarios[] = $rowComentario;
    }
    $stmtComentarios->close(); // Cerrar statement
} else {
    error_log("Error al preparar consulta de comentarios: " . $conn->error);
    // No detener la ejecución, la sección de comentarios estará vacía
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DEVWEB</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/Publicacion.css">

   <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>

</head>
<body  class="cuerpo">

 <header>
  <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px"></a>  <h6 id="titulo">DEVWEB</h6>
  <div class="identificador">
   <button onclick="location.href='Perfil.php'"><?php echo $user_name?></button>
             </div>
     </header>
<main>
<?php
    $mime = $publicacion['tipo_Img'] ?? 'image/png';
    $isVideo = $publicacion['video'];
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($publicacion['contenido']);
?>
<article class="card-container">
    <div class="card">
        <div class="card-header">
        <?php if ($profile_image!==null) {

$mimeusuario = $img_type ?? 'image/png';
$base64 = base64_encode($profile_image);

echo '<img class="img-cirUs" src="data:' . $mimeusuario . ';base64,' . $base64 . '">';

} else {?> 

<img id="imgPerfil" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs">
<?php  }   ?>
            <span class="autor"><?php echo htmlspecialchars($publicacion['autor']); ?></span>
            <span class="fecha"><?php echo htmlspecialchars($publicacion['fechaC']); ?></span>
        </div>

        <div class="card-body">
            <h2><?php echo htmlspecialchars($publicacion['titulo']); ?></h2>
            <p><?php echo htmlspecialchars($publicacion['descripcion']); ?></p>
            <?php if ($isVideo): ?>
                <video class="media" controls>
                    <source src="<?php echo $mediaSrc; ?>" type="<?php echo $mime; ?>">
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
            <button class="btn share"><i class="fa-solid fa-share"></i> Compartir</button>
        </div>
    </div>
    <section class="comentarios-seccion">
                    <h3>Comentarios</h3>
                    <form id="form-comentario" onsubmit="guardarComentario(event)">
                        <input type="hidden" name="publi_id_comentario" value="<?= $idPubli ?>">
                        <textarea name="comentario" placeholder="Escribe un comentario..." required aria-label="Escribe un comentario"></textarea>
                        <br>
                        <div id="mensaje-comentario" class="mensaje-ajax"></div> <button type="submit" class="btn-ver-mas"><i class="fa-solid fa-paper-plane"></i></button>
                    </form>

                    <div id="lista-comentarios">
                        <?php if (empty($comentarios)): ?>
                            <p>Sé el primero en comentar.</p>
                        <?php else: ?>
                            <?php foreach ($comentarios as $coment): ?>
                                <div class="comentario-item">
                                <strong>
                                  <?php   

    if ($coment['imagen']!==null) {

        $mime = $coment['tipo_Img'] ?? 'image/png';
        $base64 = base64_encode($coment['imagen']);

        echo '<img class="img-cirUs" src="data:' . $mime . ';base64,' . $base64 . '">';
    
} else {?> 
    
<img id="imgPerfil" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs">
<?php  }   ?><?= htmlspecialchars($coment['nomUs'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span> (<?= htmlspecialchars($coment['fechaC'], ENT_QUOTES, 'UTF-8') ?>):</span>
                                    <p><?= nl2br(htmlspecialchars($coment['comen'], ENT_QUOTES, 'UTF-8')) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>
</article>


</main>
<script src="../js/Publicacion_comen.js"></script>
<script src="../js/likes.js"></script>
</body>
</html>