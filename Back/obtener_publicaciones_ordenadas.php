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
switch ($orden) {
    case 'ultimas':
        $query = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
          FROM ultimas_publicaciones v";
        break;
    case 'comentadas':
        $query = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
          FROM  publicaciones_mas_comentadas v";
        break;
    case 'gustadas':
        $query = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
          FROM publicaciones_mas_likes v";
        break;
    default:
         $query = "SELECT v.*, (SELECT COUNT(*) FROM Likes WHERE idPublicacion = v.idPubli AND idUsuario = ?) AS hasLiked
          FROM ultimas_publicaciones v";
        break;
}

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$publicacionesHTML = '';
while ($row = mysqli_fetch_assoc($result)) {
    $mime = $row['tipo_Img'] ?? 'image/png';
    $isVideo = $row['video'];
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']);
    $hasLiked = $row['hasLiked'] > 0;
    $numLikes = $row['nLikes'];
    $fechaFormateada = $row['fecha_formateada'];
    $numComentarios = $row['comentarios'];
    $urlPublicacion = 'https://stork-holy-yeti.ngrok-free.app/DEVWEB/front/publicacion.php?id=' . $row['idPubli'];
    $titulo = rawurlencode($row['titulo']);
    $mensaje = rawurlencode("¡Mira esta publicación que encontré! $titulo $urlPublicacion");
   
    $whatsappUrl = "https://wa.me/?text=$mensaje";

    $facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($urlPublicacion);
    
    $publicacionesHTML .= '<div class="card-container">';
    $publicacionesHTML .= '   <div class="card">';
    $publicacionesHTML .= '       <div class="card-header">';
    $publicacionesHTML .= '           <div class="userPres">';
    if ($row['imgPerfil'] !== null) {
        $mimeusuario = $row['tipo_ImgUser'] ?? 'image/png';
        $base64 = base64_encode($row['imgPerfil']);
        $publicacionesHTML .= '<img class="img-cirUs" src="data:' . htmlspecialchars($mimeusuario) . ';base64,' . htmlspecialchars($base64) . '">';
    } else {
        $publicacionesHTML .= '<img id="imgPerfil" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs">';
    }
    $publicacionesHTML .= '           <span class="autor">' . htmlspecialchars($row['autor']) . '</span></div>';
    $publicacionesHTML .= '           <span class="fecha">' . htmlspecialchars($fechaFormateada) . '</span>';
    $publicacionesHTML .= '       </div>';
    $publicacionesHTML .= '       <div class="card-body">';
    $publicacionesHTML .= '           <h2>' . htmlspecialchars($row['titulo']) . '</h2>';
    $publicacionesHTML .= '           <p>' . htmlspecialchars($row['extracto']) . '</p>';
    if ($isVideo) {
        $publicacionesHTML .= '           <video class="media" controls>';
        $publicacionesHTML .= '               <source src="' . $mediaSrc . '" type="' . $mime . '">';
        $publicacionesHTML .= '               Tu navegador no soporta video.';
        $publicacionesHTML .= '           </video>';
    } else {
        $publicacionesHTML .= '           <img class="media" src="' . $mediaSrc . '" alt="Contenido multimedia">';
    }
    $publicacionesHTML .= '       </div>';
    $publicacionesHTML .= '       <div class="card-footer">';
    $publicacionesHTML .= '           <button class="btn like-btn ' . ($hasLiked ? 'liked' : '') . '" data-idpubli="' . $row['idPubli'] . '">';
    $publicacionesHTML .= '               <i class="fa-solid fa-thumbs-up"></i>';
    $publicacionesHTML .= '               <span class="like-text">' . ($hasLiked ? 'Te gusta' : 'Me gusta') . '</span>';
    $publicacionesHTML .= '           </button>';
    $publicacionesHTML .= '           <span class="like-count">' . $numLikes . '</span>';
    $publicacionesHTML .= '           <button class="btn comment" onclick="window.location.href=\'publicacion.php?id=' . $row['idPubli'] . '\'"><i class="fa-solid fa-comment"></i> Comentar</button>';
    $publicacionesHTML .= '           <span class="like-count">' . $numComentarios . '</span>';
    $publicacionesHTML .= '<a class="btn share" href="' . $whatsappUrl . '" target="_blank">';
    $publicacionesHTML .= '<i class="fa-brands fa-whatsapp"></i> Compartir</a>';
    // $publicacionesHTML .= '<a class="btn share" href="' . $facebookUrl . '" target="_blank">';
    // $publicacionesHTML .= '<i class="fa-brands fa-facebook"></i> Compartir</a>';
    

    $publicacionesHTML .= '       </div>';
    $publicacionesHTML .= '   </div>';
    $publicacionesHTML .= '</div>';
}

echo json_encode(['html' => $publicacionesHTML]);
?>