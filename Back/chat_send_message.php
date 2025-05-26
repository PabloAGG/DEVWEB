<?php
session_start();
require_once 'DB_connection.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_POST['id_conversacion']) || !isset($_POST['contenido_mensaje'])) {
    echo json_encode(['error' => 'Datos incompletos o usuario no autenticado']);
    exit;
}

$id_emisor = $_SESSION['user_id'];
$id_conversacion = intval($_POST['id_conversacion']);
$contenido_mensaje = trim($_POST['contenido_mensaje']); // trim para evitar mensajes vacíos

if (empty($contenido_mensaje)) {
    echo json_encode(['error' => 'El mensaje no puede estar vacío']);
    exit;
}

$sql = "INSERT INTO mensajes (id_conversacion, id_emisor, contenido_mensaje) VALUES (?, ?, ?)";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iis", $id_conversacion, $id_emisor, $contenido_mensaje);
    if ($stmt->execute()) {
        // Actualizar 'ultima_actualizacion' en la tabla 'conversaciones'
        $sql_update_conv = "UPDATE conversaciones SET ultima_actualizacion = CURRENT_TIMESTAMP WHERE id_conversacion = ?";
        if($stmt_update = $conn->prepare($sql_update_conv)){
            $stmt_update->bind_param("i", $id_conversacion);
            $stmt_update->execute();
            $stmt_update->close();
        }
        echo json_encode(['success' => true, 'id_mensaje' => $stmt->insert_id, 'fecha_envio' => date('Y-m-d H:i:s')]);
    } else {
        echo json_encode(['error' => 'Error al enviar el mensaje']);
    }
    $stmt->close();
} else {
    echo json_encode(['error' => 'Error al preparar la consulta']);
}
$conn->close();

?>