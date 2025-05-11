<?php
require 'DB_connection.php'; // Ajusta la ruta si tu estructura de carpetas es diferente

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo "ID de multimedia inválido.";
    exit;
}

$idMulti = intval($_GET['id']);

// Prepara la consulta para obtener el contenido del video y su tipo MIME
$stmt = mysqli_prepare($conn, "SELECT contenido, tipo_Img FROM Multimedia WHERE idMulti = ? AND video = 1");
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    error_log("Error al preparar la consulta para stream_video.php: " . mysqli_error($conn));
    echo "Error al acceder a los datos del video.";
    exit;
}

mysqli_stmt_bind_param($stmt, 'i', $idMulti);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($media = mysqli_fetch_assoc($result)) {
    $videoData = $media['contenido'];
    $mimeType = $media['tipo_Img'];

    if (empty($videoData) || empty($mimeType)) {
        http_response_code(404); // Not Found
        echo "Contenido de video o tipo MIME no encontrado en la base de datos.";
        exit;
    }

    // Enviar las cabeceras HTTP correctas
    header("Content-Type: " . htmlspecialchars($mimeType));
    header("Content-Length: " . strlen($videoData)); // Es útil para el navegador
    // Cabeceras adicionales para mejor compatibilidad con streaming y caching (opcional para una implementación básica)
    // header('Accept-Ranges: bytes');
    // header('Cache-Control: public, max-age=2592000'); // Cache por 30 días, por ejemplo
    // header('Expires: '.gmdate('D, d M Y H:i:s', time()+2592000).' GMT');

    // Enviar el contenido del video
    echo $videoData;

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit; // Terminar el script después de enviar los datos

} else {
    http_response_code(404); // Not Found
    echo "Video no encontrado o el ID no corresponde a un video.";
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    exit;
}
?>