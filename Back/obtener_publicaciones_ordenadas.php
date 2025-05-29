<?php
require 'DB_connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'No estás autenticado.']);
    exit();
}
$user_id = $_SESSION['user_id'];
$orden = $_GET['orden'] ?? 'ultimas'; // Obtener el criterio de ordenación

$query = "";
// Definir la consulta base según el orden
switch ($orden) {
    case 'seguidos':
        $queryBase = "SELECT
        up.*,
        (SELECT COUNT(*) FROM Likes WHERE idPublicacion = up.idPubli AND idUsuario = ?) AS hasLiked
    FROM
        ultimas_publicaciones up
    INNER JOIN
        Seguidores s ON up.idUsuario = s.idSeguido
    WHERE
        s.idSeguidor = ?
    ORDER BY
        up.fechaC DESC";
        break;

    case 'ultimas':
        $queryBase = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
                      FROM ultimas_publicaciones v"; // Asegúrate que ultimas_publicaciones incluye idMulti
        break;
    case 'comentadas':
        $queryBase = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
                      FROM publicaciones_mas_comentadas v"; // Asegúrate que publicaciones_mas_comentadas incluye idMulti
        break;
    case 'gustadas':
        $queryBase = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
                      FROM publicaciones_mas_likes v"; // Asegúrate que publicaciones_mas_likes incluye idMulti
        break;
    default:
         $queryBase = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
                       FROM ultimas_publicaciones v"; // Default
        break;
}

$stmt = mysqli_prepare($conn, $queryBase); // Usar $queryBase aquí
if (!$stmt) {
    error_log("Error al preparar la consulta en obtener_publicaciones_ordenadas.php: " . mysqli_error($conn));
    echo json_encode(['error' => 'Error al obtener publicaciones.']);
    exit();
}

if ($orden === 'seguidos') {
    // Para la consulta de seguidos, necesitamos el user_id dos veces
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
} else {
    // Para las otras consultas, solo necesitamos el user_id una vez
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$publicacionesHTML = '';
while ($row = mysqli_fetch_assoc($result)) {
    // Asegúrate de que 'idMulti' y 'tipo_ImgPubli' (o el nombre correcto del campo MIME de la multimedia)
    // estén siendo seleccionados por tus vistas SQL.
    $idMulti = $row['idMulti'] ?? null; // ID de la tabla Multimedia
    $mime = $row['tipo_ImgPubli'] ?? ($row['tipo_Img'] ?? 'application/octet-stream'); // Tipo MIME del contenido
    $isVideo = $row['video'];
    $video= $row['video_path'] ?? null; // Ruta del video si es un video
    $hasLiked = $row['hasLiked'] > 0;
    $numLikes = $row['nLikes'];
    $fechaFormateada = $row['fecha_formateada'];
    $numComentarios = $row['comentarios'];
    $urlPublicacion = 'https://stork-holy-yeti.ngrok-free.app/DEVWEB/front/publicacion.php?id=' . $row['idPubli'];
    $titulo = rawurlencode($row['titulo']);
    $mensaje = rawurlencode("¡Mira esta publicación que encontré! $titulo $urlPublicacion");
   
    $whatsappUrl = "https://wa.me/?text=$mensaje";
    // $facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($urlPublicacion);
    
    $publicacionesHTML .= '<div class="card-container">';
    $publicacionesHTML .= '   <div class="card">';
    $publicacionesHTML .= '       <div class="card-header">';
    $publicacionesHTML .= '           <div class="userPres">';
    if ($row['imgPerfil'] !== null) {
        $mimeusuario = $row['tipo_ImgUser'] ?? 'image/png';
        $base64UserImg = base64_encode($row['imgPerfil']);
        $publicacionesHTML .= '<img  loading="lazy" class="img-cirUs" src="data:' . htmlspecialchars($mimeusuario) . ';base64,' . htmlspecialchars($base64UserImg) . '">';
    } else {
        $publicacionesHTML .= '<img id="imgPerfil"  loading="lazy" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs">';
    }
    $publicacionesHTML .= '           <span class="autor"><a href="PerfilExt.php?id=' . $row['idUsuario'] . '">' . htmlspecialchars($row['autor']) . '</a></span></div>';
    $publicacionesHTML .= '           <span class="fecha">' . htmlspecialchars($fechaFormateada) . '</span>';
    $publicacionesHTML .= '       </div>';
    $publicacionesHTML .= '       <div class="card-body">';
    $publicacionesHTML .= '           <h2>' . htmlspecialchars($row['titulo']) . '</h2>';
    $publicacionesHTML .= '           <p>' . htmlspecialchars($row['extracto']) . '</p>';
    
    if ($isVideo==1) {
        // Modificación para usar el script de streaming
   

    $publicacionesHTML .= '           <video class="media" controls preload="metadata">'; // preload="metadata" es buena práctica
     $publicacionesHTML .= '               <source src="' . $video . '" type="' . htmlspecialchars($mime) . '">';
    $publicacionesHTML .= '               Tu navegador no soporta el elemento de video.';
     $publicacionesHTML .= '           </video>';
    } elseif (!$isVideo && isset($row['contenido'])) {
        // Mantener Base64 para imágenes si así lo deseas, ya que es menos problemático
        $imgBase64 = base64_encode($row['contenido']);
        $publicacionesHTML .= '           <img class="media"  loading="lazy" src="data:' . htmlspecialchars($mime) . ';base64,' . $imgBase64 . '" alt="Contenido multimedia">';
    }
    // Si es video pero no hay idMulti, o si no hay contenido, podrías poner un placeholder o mensaje.

    $publicacionesHTML .= '       </div>';
    $publicacionesHTML .= '       <div class="card-footer">';
    $publicacionesHTML .= '           <button class="btn like-btn ' . ($hasLiked ? 'liked' : '') . '" data-idpubli="' . $row['idPubli'] . '">';
    $publicacionesHTML .= '               <i class="fa-solid fa-thumbs-up"></i>';
    $publicacionesHTML .= '               <span class="like-text">' . ($hasLiked ? 'Te gusta' : 'Me gusta') . '</span>';
    $publicacionesHTML .= '           </button>';
    $publicacionesHTML .= '           <span class="like-count">' . $numLikes . '</span>';
    $publicacionesHTML .= '           <button class="btn comment" onclick="window.location.href=\'publicacion.php?id=' . $row['idPubli'] . '\'"><i class="fa-solid fa-comment"></i> Comentar</button>';
    $publicacionesHTML .= '           <span class="Coment-count">' . $numComentarios . '</span>'; // Corregido 'like-count' a 'Coment-count' si es para comentarios
    $publicacionesHTML .= '<a class="btn share" href="' . htmlspecialchars($whatsappUrl) . '" target="_blank">';
    $publicacionesHTML .= '<i class="fa-brands fa-whatsapp"></i> Compartir</a>';
    $publicacionesHTML .= '       </div>';
    $publicacionesHTML .= '   </div>';
    $publicacionesHTML .= '</div>';
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
echo json_encode(['html' => $publicacionesHTML]);
?>