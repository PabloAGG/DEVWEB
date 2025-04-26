<?php
require 'DB_connection.php';
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
        header("Location: ../front/RegistroUs.php?error=$error");
        exit();
    }
    


    // Insertar el nuevo usuario
    $query = "INSERT INTO Usuarios 
          (nombre, nomUs, contra, correo, nacimiento, estado, usAdmin, fechaM) 
          VALUES (?, ?, ?, ?, ?, 1, 0, NULL)";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param(
    $stmt, 
    "sssss", 
    $nombreCompleto, 
    $nombreUsuario, 
    $contraseña, 
    $email, 
    $fechaNacimiento
);


    if (mysqli_stmt_execute($stmt)) {
        header('Location: ../front/InicioSesion.php?success=user_created');
        exit();
    } else {
        header('Location: ../front/RegistroUs.php?error=user_not_created');
    }
}
//} 
function existeCampo($conn, $campo, $valor) {
    $query = "SELECT idUsuario FROM Usuarios WHERE $campo = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $valor);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    return mysqli_stmt_num_rows($stmt) > 0;
}
// Cerramos la conexión
mysqli_close($conn);
?>