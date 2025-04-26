<?php

// Conexión a la base de datos
require 'DB_connection.php';
session_start(); // Iniciar la sesión 


// Verificamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreCompleto = mysqli_real_escape_string($conn, $_POST['nombre_completo']);
    $nombreUsuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $email = mysqli_real_escape_string($conn, $_POST['email_usuario']);
    $contraseña = password_hash($_POST['contraseña_usuario'], PASSWORD_DEFAULT);
    $fechaNacimiento = $_POST['fecha_usuario'];


    $check_query = "SELECT idUsuario FROM Usuarios WHERE nomUs = ? OR correo = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ss", $nombreUsuario, $email);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
   

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $error = "";
        if (existeCampo($conn, 'nomUs', $nombreUsuario)) {
            $error = "username_exists";
        } else {
            $error = "email_exists";
        }
        header("Location: ../front/EditData.php?error=$error");
        exit();
    }
    $user_id = $_SESSION['user_id'];
    // Consulta para buscar el usuario por nombre de usuario o correo electrónico
    $query = "CALL sp_Usuarios_CRUD(
    'UPDATE', ?, ?, ?, ?, ?, ?, 1, 0);";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "isssss", $user_id,$nombreCompleto, $nombreUsuario, $contraseña, $email, $fechaNacimiento);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

    if ($result) {
        header('Location: ../front/Perfil.php?success=user_updated');
        exit();
    } else {
        header('Location: ../front/EditData.php?error=user_not_updated');
        exit(); // Salimos del script después de redirigir
    }

  exit(); // Salimos del script después de redirigir
    }


// Cerramos la conexión
mysqli_close($conn);
?>
