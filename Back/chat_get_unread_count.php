<?php
// Back/chat_get_unread_count.php
header('Content-Type: application/json');
session_start();
require_once 'DB_connection.php'; // Asegúrate de que la ruta sea correcta

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['unread_count' => 0, 'error' => 'Usuario no autenticado']);
    exit;
}

$current_user_id = $_SESSION['user_id'];
$total_unread_messages = 0;

if (!$conn || $conn->connect_error) {
    error_log('Error de conexión a la BD en chat_get_unread_count: ' . ($conn ? $conn->connect_error : 'No se pudo crear $conn'));
    echo json_encode(['unread_count' => 0, 'error' => 'Error de conexión a la base de datos']);
    exit;
}

// Es buena práctica también establecer la zona horaria para la sesión de MySQL
$conn->query("SET time_zone = '+00:00'");


$sql = "SELECT COUNT(m.id_mensaje) AS unread_count
        FROM mensajes m
        JOIN conversaciones c ON m.id_conversacion = c.id_conversacion
        WHERE (c.id_usuario1 = ? OR c.id_usuario2 = ?)  
          AND m.id_emisor != ?                         
          AND m.leido = FALSE";                     

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $total_unread_messages = (int)$row['unread_count'];
        }
    } else {
        error_log("Error al ejecutar conteo de no leídos para user_id {$current_user_id}: " . $stmt->error);
        // En caso de error, se devolverá 0, lo cual es seguro para el cliente
    }
    $stmt->close();
} else {
    error_log("Error al preparar conteo de no leídos para user_id {$current_user_id}: " . $conn->error);
    // En caso de error, se devolverá 0
}

$conn->close();

echo json_encode(['unread_count' => $total_unread_messages]);
?>