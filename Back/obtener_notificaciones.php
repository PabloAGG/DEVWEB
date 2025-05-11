<?php
session_start();
require 'DB_connection.php';

header('Content-Type: application/json'); // Indica que la respuesta es JSON

if (!isset($_SESSION['user_id'])) {
 http_response_code(403); // Prohibido
 echo json_encode(['error' => 'No has iniciado sesión.']);
 exit();
}

$user_id = $_SESSION['user_id'];

// Consulta para obtener notificaciones (puedes limitar la cantidad si son muchas)
$query = "SELECT
n.idNotificacion,
n.mensaje,
 n.fechaCreacion,
 n.leida,
 n.idPublicacion,
n.idUsuarioEmite,
            n.tipo, -- *** CAMBIO: AGREGAR ESTA LÍNEA ***
 u.nomUs AS usuarioEmiteNombre -- Obtener el nombre del usuario que emite
 FROM Notificaciones n
 JOIN Usuarios u ON n.idUsuarioEmite = u.idUsuario -- Unir con Usuarios para obtener el nombre
 WHERE n.idUsuarioRecibe = ?
 ORDER BY n.fechaCreacion DESC
 LIMIT 20"; // Limitar a las 20 más recientes, por ejemplo

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notificaciones = [];
while ($row = mysqli_fetch_assoc($result)) {
 $notificaciones[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

// Devuelve el array de notificaciones en formato JSON
echo json_encode($notificaciones);
?>