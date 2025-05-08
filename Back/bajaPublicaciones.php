<?php

// Conexión a la base de datos
require 'DB_connection.php';
session_start(); // Iniciar la sesión 
  $user_id = $_SESSION['user_id'];
 $id_publi = $_GET['id'] ?? null; // Obtener el ID de la publicación a eliminar
  $query = "UPDATE Publicaciones SET estado = 0 WHERE idPubli = ? AND idUsuario = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ii", $id_publi, $user_id);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        header('Location: ../front/Perfil.php?success=post_deleted');
        exit();
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        // Si hay un error, redirigir a la página de error
        header('Location: ../front/Perfil.php?error=post_not_deleted');
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
  ?>