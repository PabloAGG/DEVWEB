<?php
session_start();
require 'DB_connection.php';

header('Content-Type: application/json'); // Indica que la respuesta es JSON

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Prohibido
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$idNotificacion = isset($_POST['idNotificacion']) ? intval($_POST['idNotificacion']) : 0; // Esperamos POST, pero GET también serviría

if ($idNotificacion <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de notificación inválido.']);
    exit();
}

// Marcar la notificación como leída para el usuario actual
$query = "UPDATE Notificaciones SET leida = 1 WHERE idNotificacion = ? AND idUsuarioRecibe = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $idNotificacion, $user_id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída.']);
    } else {
        // La notificación no existe o no pertenece a este usuario
        echo json_encode(['success' => false, 'message' => 'Notificación no encontrada o no tienes permiso.']);
    }
} else {
     error_log("Error al actualizar notificación como leída: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Error al marcar notificación como leída.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>