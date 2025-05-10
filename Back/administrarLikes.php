<?php

require 'DB_connection.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../front/InicioSesion.php");
    exit();
}
$user_id = $_SESSION['user_id']; // ID del usuario logueado
$publi_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($publi_id <= 0) { //  Corregí la variable aquí (era $idPubli en lugar de $publi_id)
    die(json_encode(['success' => false, 'message' => 'ID de publicación inválido.'])); // Importante: Salir con JSON
}
$check_query = "SELECT * FROM Likes WHERE idUsuario=? AND idPublicacion=?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $user_id, $publi_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

// Error handling para la consulta SELECT
if (mysqli_errno($conn)) {
    error_log("Error en la consulta SELECT: " . mysqli_error($conn));
    echo json_encode(['success' => false, 'message' => 'Error al verificar el like.']);
    exit();
}


if (mysqli_stmt_num_rows($check_stmt) > 0) {
    // Si ya existe un like, lo eliminamos
    $delete_query = "DELETE FROM Likes WHERE idUsuario=? AND idPublicacion=?";
    $delete_stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($delete_stmt, "ii", $user_id, $publi_id);
    mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);

    // Error handling para la consulta DELETE
    if (mysqli_errno($conn)) {
        error_log("Error en la consulta DELETE: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Error al quitar el like.']);
        exit();
    }

    echo json_encode(['success' => true, 'action' => 'unlike']);
} else {
    // Si no existe un like, lo insertamos
    $insert_query = "INSERT INTO Likes (idUsuario, idPublicacion) VALUES (?, ?)";
    $insert_stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $publi_id);
    mysqli_stmt_execute($insert_stmt);
    mysqli_stmt_close($insert_stmt);

    // Error handling para la consulta INSERT
    if (mysqli_errno($conn)) {
        error_log("Error en la consulta INSERT: " . mysqli_error($conn));
        echo json_encode(['success' => false, 'message' => 'Error al dar el like.']);
        exit();
    }

     // --- INICIO: Lógica de Notificaciones ---
    $idPublicacion = $publi_id; // Obtén el ID de la publicación
    $idUsuarioRecibe = obtenerAutorPublicacion($conn, $idPublicacion); // Función para obtener el autor
    $idUsuarioEmite = $_SESSION['user_id']; // El usuario que dio el "like"

    if ($idUsuarioRecibe != $idUsuarioEmite) { // No notificar al autor por sus propios likes
        $mensaje = "El usuario " . obtenerNombreUsuario($conn, $idUsuarioEmite) . " le dio me gusta a tu publicación.";

        $query_notificacion = "INSERT INTO Notificaciones (idUsuarioRecibe, idUsuarioEmite, idPublicacion, tipo, mensaje) VALUES (?, ?, ?, 'like', ?)";
        $stmt_notificacion = mysqli_prepare($conn, $query_notificacion);
        mysqli_stmt_bind_param($stmt_notificacion, "iiis", $idUsuarioRecibe, $idUsuarioEmite, $idPublicacion, $mensaje);
        mysqli_stmt_execute($stmt_notificacion);

        if (mysqli_errno($conn)) {
            error_log("Error al insertar la notificación: " . mysqli_error($conn));
        }
    }
    echo json_encode(['success' => true, 'action' => 'like']);
}
mysqli_stmt_close($check_stmt);
mysqli_close($conn);
function obtenerAutorPublicacion($conn, $idPublicacion) {
    $query = "SELECT idUsuario FROM Publicaciones WHERE idPubli = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idPublicacion);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['idUsuario'];
}

function obtenerNombreUsuario($conn, $idUsuario) {
    $query = "SELECT nomUs FROM Usuarios WHERE idUsuario = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['nomUs'];
}
?>