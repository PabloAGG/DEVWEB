<?php
// Back/chat_get_seguidos.php
header('Content-Type: application/json');

session_start();
require_once 'DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuario no autenticado', 'followed' => [], 'others' => []]);
    exit;
}

if (!$conn || $conn->connect_error) {
    error_log('Error de conexión a la BD en chat_get_seguidos: ' . ($conn ? $conn->connect_error : 'No se pudo crear $conn'));
    echo json_encode(['error' => 'Error de conexión a la base de datos', 'followed' => [], 'others' => []]);
    exit;
}

$id_usuario_actual = $_SESSION['user_id'];
$response_data = [
    'followed' => [],
    'others' => []
];
$followed_user_ids = []; // Para no repetir usuarios en la lista 'others'

// --- Parte 1: Obtener usuarios que el usuario actual sigue ---
$sql_seguidos = "SELECT u.idUsuario, u.nomUs
                 FROM usuarios u 
                 INNER JOIN seguidores s ON u.idUsuario = s.idSeguido
                 WHERE s.idSeguidor = ? and u.estado = 1"; // Aseguramos que el usuario está activo

if ($stmt_seguidos = $conn->prepare($sql_seguidos)) {
    $stmt_seguidos->bind_param("i", $id_usuario_actual);
    $stmt_seguidos->execute();
    $result_seguidos = $stmt_seguidos->get_result();

    $sql_get_conv_id_part1 = "SELECT id_conversacion FROM conversaciones 
                             WHERE (id_usuario1 = ? AND id_usuario2 = ?) OR (id_usuario1 = ? AND id_usuario2 = ?)";
    $stmt_get_conv_part1 = $conn->prepare($sql_get_conv_id_part1);

    $sql_unread_in_conv_part1 = "SELECT COUNT(id_mensaje) as unread_count 
                                FROM mensajes 
                                WHERE id_conversacion = ? AND id_emisor = ? AND leido = FALSE";
    $stmt_unread_count_part1 = $conn->prepare($sql_unread_in_conv_part1);

    while ($user_data = $result_seguidos->fetch_assoc()) {
        $id_seguido = $user_data['idUsuario'];
        $followed_user_ids[] = $id_seguido; // Añadir a la lista de IDs ya procesados
        $user_data['unread_from_user_count'] = 0;

        $conv_id = null;
        $u1 = min($id_usuario_actual, $id_seguido);
        $u2 = max($id_usuario_actual, $id_seguido);
        
        if ($stmt_get_conv_part1) {
            $stmt_get_conv_part1->bind_param("iiii", $u1, $u2, $u2, $u1);
            $stmt_get_conv_part1->execute();
            $result_conv_id = $stmt_get_conv_part1->get_result();
            if ($row_conv_id = $result_conv_id->fetch_assoc()) {
                $conv_id = $row_conv_id['id_conversacion'];
            }
        }

        if ($conv_id && $stmt_unread_count_part1) {
            $stmt_unread_count_part1->bind_param("ii", $conv_id, $id_seguido);
            $stmt_unread_count_part1->execute();
            $result_unread = $stmt_unread_count_part1->get_result();
            if ($row_unread = $result_unread->fetch_assoc()) {
                $user_data['unread_from_user_count'] = intval($row_unread['unread_count']);
            }
        }
        
        $user_data['nomUs'] = mb_convert_encoding($user_data['nomUs'], 'UTF-8', 'auto');
        $response_data['followed'][] = $user_data;
    }
    
    if ($stmt_get_conv_part1) $stmt_get_conv_part1->close();
    if ($stmt_unread_count_part1) $stmt_unread_count_part1->close();
    $stmt_seguidos->close();

} else {
    error_log("Error al preparar la consulta de seguidos (para user_id: {$id_usuario_actual}): " . $conn->error);
    // No salimos, podríamos seguir y obtener 'others'
}


// --- Parte 2: Obtener otras conversaciones activas (con usuarios no seguidos directamente aquí) ---
$sql_other_conversations = "SELECT 
                                c.id_conversacion,
                                IF(c.id_usuario1 = ?, c.id_usuario2, c.id_usuario1) AS id_other_user,
                                u.nomUs AS nomUs_other_user
                            FROM conversaciones c
                            JOIN usuarios u ON u.idUsuario = IF(c.id_usuario1 = ?, c.id_usuario2, c.id_usuario1)
                            WHERE (c.id_usuario1 = ? OR c.id_usuario2 = ?) 
                            HAVING id_other_user != ?"; // Para evitar mostrarse a uno mismo si hay una conversación con uno mismo (poco probable)

if ($stmt_other_convs = $conn->prepare($sql_other_conversations)) {
    $stmt_other_convs->bind_param("iiiii", $id_usuario_actual, $id_usuario_actual, $id_usuario_actual, $id_usuario_actual, $id_usuario_actual);
    $stmt_other_convs->execute();
    $result_other_convs = $stmt_other_convs->get_result();

    $sql_unread_in_conv_part2 = "SELECT COUNT(id_mensaje) as unread_count 
                                 FROM mensajes 
                                 WHERE id_conversacion = ? AND id_emisor = ? AND leido = FALSE";
    $stmt_unread_count_part2 = $conn->prepare($sql_unread_in_conv_part2);

    while ($conv_data = $result_other_convs->fetch_assoc()) {
        $id_other_user = $conv_data['id_other_user'];

        // Si este usuario ya está en la lista de 'followed', no lo añadimos a 'others'
        if (in_array($id_other_user, $followed_user_ids)) {
            continue;
        }
        // Si no hay mensajes no leídos Y quieres ocultar conversaciones vacías/sin actividad de no seguidos,
        // podrías añadir una lógica aquí para verificar si hay mensajes en la conversación
        // y si no, y no hay no leídos, no mostrarla. Por ahora, mostraremos todas las que no son 'followed'.

        $other_user_info = [
            'idUsuario' => $id_other_user,
            'nomUs' => mb_convert_encoding($conv_data['nomUs_other_user'], 'UTF-8', 'auto'),
            'unread_from_user_count' => 0
        ];
        
        $current_conv_id = $conv_data['id_conversacion'];

        if ($stmt_unread_count_part2) {
            $stmt_unread_count_part2->bind_param("ii", $current_conv_id, $id_other_user);
            $stmt_unread_count_part2->execute();
            $result_unread_other = $stmt_unread_count_part2->get_result();
            if ($row_unread_other = $result_unread_other->fetch_assoc()) {
                $other_user_info['unread_from_user_count'] = intval($row_unread_other['unread_count']);
            }
        }
        $response_data['others'][] = $other_user_info;
    }
    $stmt_other_convs->close();
    if ($stmt_unread_count_part2) $stmt_unread_count_part2->close();
} else {
    error_log("Error al preparar la consulta de otras conversaciones (para user_id: {$id_usuario_actual}): " . $conn->error);
}

$conn->close();
echo json_encode($response_data);
?>