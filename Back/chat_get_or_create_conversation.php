<?php
session_start();
require_once 'DB_connection.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !isset($_POST['id_otro_usuario'])) {
    echo json_encode(['error' => 'Datos incompletos o usuario no autenticado']);
    exit;
}

$id_usuario_actual = $_SESSION['user_id'];
$id_otro_usuario = intval($_POST['id_otro_usuario']);

// Asegurar que id_usuario1 < id_usuario2 para la clave única
$id_user1 = min($id_usuario_actual, $id_otro_usuario);
$id_user2 = max($id_usuario_actual, $id_otro_usuario);

$id_conversacion = null;

// Intentar obtener la conversación existente
$sql_select = "SELECT id_conversacion FROM conversaciones WHERE id_usuario1 = ? AND id_usuario2 = ?";
if ($stmt_select = $conn->prepare($sql_select)) {
    $stmt_select->bind_param("ii", $id_user1, $id_user2);
    $stmt_select->execute();
    $result_select = $stmt_select->get_result();
    if ($row_select = $result_select->fetch_assoc()) {
        $id_conversacion = $row_select['id_conversacion'];
    }
    $stmt_select->close();
}

// Si no existe, crearla
if ($id_conversacion === null) {
    $sql_insert = "INSERT INTO conversaciones (id_usuario1, id_usuario2) VALUES (?, ?)";
    if ($stmt_insert = $conn->prepare($sql_insert)) {
        $stmt_insert->bind_param("ii", $id_user1, $id_user2);
        if ($stmt_insert->execute()) {
            $id_conversacion = $stmt_insert->insert_id;
        }
        $stmt_insert->close();
    }
}

$conn->close();

if ($id_conversacion) {
    echo json_encode(['success' => true, 'id_conversacion' => $id_conversacion]);
} else {
    echo json_encode(['error' => 'No se pudo iniciar la conversación']);
}
?>