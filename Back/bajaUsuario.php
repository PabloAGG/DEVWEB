<?php

// Conexión a la base de datos
require 'DB_connection.php';
session_start(); // Iniciar la sesión 
  $user_id = $_SESSION['user_id'];

  $query = "CALL sp_Usuarios_CRUD(
    'DELETE', ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1);";
  $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
setcookie('user_name', '', time() - 3600, "/"); // Elimina la cookie 'user_name'
setcookie('user_id', '', time() - 3600, "/");   // Elimina la cookie 'user_id'
session_destroy();
        header('Location: ../front/InicioSesion.php?success=user_deleted');
        exit();
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        // Si hay un error, redirigir a la página de error
        header('Location: ../front/InicioSesion.php?error=user_not_deleted');
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    }
 

  ?>