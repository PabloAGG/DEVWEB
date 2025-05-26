<?php
header('Content-Type: application/json');
session_start();
require_once 'DB_connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id_conversacion'])) {
    echo json_encode(['error' => 'Datos incompletos o usuario no autenticado']);
    exit;
}

$user_id_actual = $_SESSION['user_id'];
$id_conversacion = intval($_GET['id_conversacion']);
$mensajes = [];

// Opcional: verificar que el usuario actual es parte de la conversación
// (Esto es importante por seguridad)
$sql_check = "SELECT 1 FROM conversaciones WHERE id_conversacion = ? AND (id_usuario1 = ? OR id_usuario2 = ?)";
if($stmt_check = $conn->prepare($sql_check)){
    $stmt_check->bind_param("iii", $id_conversacion, $user_id_actual, $user_id_actual);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if($result_check->num_rows == 0){
        echo json_encode(['error' => 'Acceso no autorizado a la conversación']);
        $stmt_check->close();
        $conn->close();
        exit;
    }
    $stmt_check->close();
}


$sql = "SELECT m.id_mensaje, m.id_emisor, m.contenido_mensaje,m.fecha_envio, u.nomUs AS nombre_emisor
        FROM mensajes m
        JOIN usuarios u ON m.id_emisor = u.idUsuario
        WHERE m.id_conversacion = ?
        ORDER BY m.fecha_envio ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_conversacion);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $mensajes[] = $row;
    }
    $stmt->close();

    // Opcional: Marcar mensajes como leídos para el usuario actual
    $sql_update_leido = "UPDATE mensajes SET leido = TRUE WHERE id_conversacion = ? AND id_emisor != ? AND leido = FALSE";
    if($stmt_leido = $conn->prepare($sql_update_leido)){
        $stmt_leido->bind_param("ii", $id_conversacion, $user_id_actual);
        $stmt_leido->execute();
        $stmt_leido->close();
    }
}
$conn->close();


echo json_encode($mensajes);
?>