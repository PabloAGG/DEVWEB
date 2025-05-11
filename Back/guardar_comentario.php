<?php
// Iniciar la sesión
session_start();

// Conexión a la base de datos
require_once("DB_connection.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comentar']);
        exit();
    }

    // Obtener los valores del formulario
    $publiId = isset($_POST['publi_id']) ? intval($_POST['publi_id']) : 0;
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    // Validar el comentario
    if (empty($comentario)) {
        echo json_encode(['success' => false, 'message' => 'Comentario vacío']);
        exit();
    }

    // Obtener el ID del usuario
    $usuarioId = $_SESSION['user_id']; // Asegurarse de que el usuario esté logueado

    // Insertar el comentario en la base de datos
    if ($publiId > 0 && $usuarioId > 0) {
        $stmt = $conn->prepare("INSERT INTO Comentarios ( comen,idPublicacion, idUsuario) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $comentario,$publiId, $usuarioId );

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comentario guardado correctamente']);
                // --- Lógica de Notificaciones para Comentarios ---
    $idPublicacion = $publiId; // Ya tienes el ID de la publicación
    $idUsuarioEmite = $usuarioId; // El usuario que comentó (ya tienes su ID)
    $idUsuarioRecibe = obtenerAutorPublicacion($conn, $idPublicacion); // Necesitas una función para obtener el autor de la publicación

    // Evitar notificar al autor por su propio comentario
    if ($idUsuarioRecibe != $idUsuarioEmite) {
        // Obtener el nombre del usuario que comentó para el mensaje
        $nombreUsuarioEmite = obtenerNombreUsuario($conn, $idUsuarioEmite); // Necesitas una función para obtener el nombre

        $mensaje = "El usuario " . htmlspecialchars($nombreUsuarioEmite) . " comentó en tu publicación.";

        // Insertar la notificación
        $query_notificacion = "INSERT INTO Notificaciones (idUsuarioRecibe, idUsuarioEmite, idPublicacion, tipo, mensaje) VALUES (?, ?, ?, 'comentario', ?)";
        $stmt_notificacion = mysqli_prepare($conn, $query_notificacion);
        mysqli_stmt_bind_param($stmt_notificacion, "iiis", $idUsuarioRecibe, $idUsuarioEmite, $idPublicacion, $mensaje);
        mysqli_stmt_execute($stmt_notificacion);

        if (mysqli_errno($conn)) {
            // Considera usar error_log() en lugar de echo para errores internos
            error_log("Error al insertar la notificación de comentario: " . mysqli_error($conn));
            // No necesitas enviar este error al frontend si el comentario sí se guardó
        }
    }
        } else 
        {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el comentario']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

function obtenerAutorPublicacion($conn, $idPublicacion) {
    $query = "SELECT idUsuario FROM Publicaciones WHERE idPubli = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idPublicacion);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt); // Importante cerrar
    return $row ? $row['idUsuario'] : null; // Retorna ID o null si no se encuentra
}

function obtenerNombreUsuario($conn, $idUsuario) {
    $query = "SELECT nomUs FROM Usuarios WHERE idUsuario = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt); // Importante cerrar
    return $row ? $row['nomUs'] : "Usuario Desconocido"; // Retorna nombre o un valor por defecto
}

?>