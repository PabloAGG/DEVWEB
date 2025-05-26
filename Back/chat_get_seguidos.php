<?php
// Back/chat_get_seguidos.php
header('Content-Type: application/json');

session_start();
require_once 'DB_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

if (!$conn || $conn->connect_error) {
    error_log('Error de conexión a la BD en chat_get_seguidos: ' . ($conn ? $conn->connect_error : 'No se pudo crear $conn'));
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}
// Zona horaria para la sesión MySQL

$id_usuario_actual = $_SESSION['user_id'];
$usuarios_seguidos_con_info = [];


$sql_seguidos = "SELECT u.idUsuario, u.nomUs
                 FROM usuarios u 
                 INNER JOIN seguidores s ON u.idUsuario = s.idSeguido
                 WHERE s.idSeguidor = ?";

if ($stmt_seguidos = $conn->prepare($sql_seguidos)) {
    $stmt_seguidos->bind_param("i", $id_usuario_actual);
    $stmt_seguidos->execute();
    $result_seguidos = $stmt_seguidos->get_result();


    $sql_get_conv_id = "SELECT id_conversacion FROM conversaciones 
                        WHERE (id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?)";
    $stmt_get_conv = $conn->prepare($sql_get_conv_id);

    $sql_unread_in_conv = "SELECT COUNT(id_mensaje) as unread_count 
                           FROM mensajes 
                           WHERE id_conversacion = ? AND id_emisor = ? AND leido = FALSE";
    $stmt_unread_count = $conn->prepare($sql_unread_in_conv);

    while ($user_data = $result_seguidos->fetch_assoc()) {
        $id_seguido = $user_data['idUsuario'];
        $user_data['unread_from_user_count'] = 0; // Valor por defecto

        // Encontrar el ID de la conversación con este seguidor
        $conv_id = null;
        $u1 = min($id_usuario_actual, $id_seguido);
        $u2 = max($id_usuario_actual, $id_seguido);
        
        $stmt_get_conv->bind_param("iiii", $u1, $u2, $u2, $u1);
        $stmt_get_conv->execute();
        $result_conv_id = $stmt_get_conv->get_result();
        if ($row_conv_id = $result_conv_id->fetch_assoc()) {
            $conv_id = $row_conv_id['id_conversacion'];
        }


        if ($conv_id && $stmt_unread_count) {
            // Contar mensajes no leídos de $id_seguido para $id_usuario_actual en esta conversación
            $stmt_unread_count->bind_param("ii", $conv_id, $id_seguido);
            $stmt_unread_count->execute();
            $result_unread = $stmt_unread_count->get_result();
            if ($row_unread = $result_unread->fetch_assoc()) {
                $user_data['unread_from_user_count'] = $row_unread['unread_count'];
            }
            // No cerramos $stmt_unread_count aquí para reutilizarlo. Se cierra después.
        }
        
        // Asegurar codificación UTF-8 para nombres de usuario
        $user_data['nomUs'] = mb_convert_encoding($user_data['nomUs'], 'UTF-8', 'auto');
        
        $usuarios_seguidos_con_info[] = $user_data;
    }
    
    // Cerrar statements preparados después del bucle
    if ($stmt_get_conv) $stmt_get_conv->close();
    if ($stmt_unread_count) $stmt_unread_count->close();
    $stmt_seguidos->close();

} else {
    error_log("Error al preparar la consulta de seguidos (para user_id: {$id_usuario_actual}): " . $conn->error);
    echo json_encode(['error' => 'Error del servidor al obtener la lista de seguidos.']);
    $conn->close();
    exit;
}

$conn->close();
echo json_encode($usuarios_seguidos_con_info);
?>