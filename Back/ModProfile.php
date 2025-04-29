<?php

// Conexión a la base de datos
require 'DB_connection.php';
session_start(); // Iniciar la sesión 
  $user_id = $_SESSION['user_id'];

// Verificamos si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombreCompleto = mysqli_real_escape_string($conn, $_POST['nombre_completo']);
    $nombreUsuario = mysqli_real_escape_string($conn, $_POST['nombre_usuario']);
    $email = mysqli_real_escape_string($conn, $_POST['email_usuario']);
    $contraseña = password_hash($_POST['contraseña_usuario'], PASSWORD_DEFAULT);
    $fechaNacimiento = $_POST['fecha_usuario'];
    // if (isset($_FILES['imgRuta']) && $_FILES['imgRuta']['error'] == UPLOAD_ERR_OK) {
    //     $imgData = file_get_contents($_FILES['imgRuta']['tmp_name']); // Leer la imagen como binario
    // } else {
    //     $imgData = NULL; // No se subió imagen
    // }

    $pswCheck = isset($_POST['psw-change']) ? $_POST['psw-change'] : null;

    // Consulta para buscar el usuario por nombre de usuario o correo electrónico
    $check_query = "SELECT idUsuario FROM Usuarios WHERE nomUs = ? OR correo = ? AND idUsuario != ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "ssi", $nombreUsuario, $email,$user_id);
    mysqli_stmt_execute($check_stmt);
    mysqli_stmt_store_result($check_stmt);
   

    if (mysqli_stmt_num_rows($check_stmt) > 0) {
        $error = "";
        if (existeCampo($conn, 'nomUs', $nombreUsuario)) {
            $error = "username_exists";
             header("Location: ../front/EditData.php?error=$error");
        exit();
        } else if (existeCampo($conn, 'nomUs', $email)) {
            $error = "email_exists";
            header("Location: ../front/EditData.php?error=$error");
            exit();
        }
       
    }

    if ($pswCheck == "on") {
        if (isset($_FILES['imgRuta']) && $_FILES['imgRuta']['error'] == UPLOAD_ERR_OK) {
            $imgData = file_get_contents($_FILES['imgRuta']['tmp_name']);
            $tipoImg = mime_content_type($_FILES['imgRuta']['tmp_name']); // 🔥 Aquí capturas el MIME
    
            $query = "CALL sp_Usuarios_CRUD(
                'UPDATE', ?, ?, ?, ?, ?, ?, ?, ?, 1);";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "isssssss", $user_id, $nombreCompleto, $nombreUsuario, $contraseña, $email, $fechaNacimiento, $imgData, $tipoImg);
            $result = mysqli_stmt_execute($stmt);
    
        } else {
            // No hay nueva imagen
            $query = "UPDATE Usuarios 
                      SET nombre = ?, nomUs = ?, correo = ?, contra = ?, nacimiento = ?, fechaM = CURRENT_DATE() 
                      WHERE idUsuario = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "sssssi", $nombreCompleto, $nombreUsuario, $email, $contraseña, $fechaNacimiento, $user_id);
        }
    
    } elseif (isset($_FILES['imgRuta']) && $_FILES['imgRuta']['error'] == UPLOAD_ERR_OK) {
        $imgData = file_get_contents($_FILES['imgRuta']['tmp_name']);
        $tipoImg = mime_content_type($_FILES['imgRuta']['tmp_name']); // 🔥 Aquí también
    
        $query = "UPDATE Usuarios 
                  SET nombre = ?, nomUs = ?, correo = ?, nacimiento = ?, imagen = ?, tipo_Img = ?, fechaM = CURRENT_DATE() 
                  WHERE idUsuario = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssssi", $nombreCompleto, $nombreUsuario, $email, $fechaNacimiento, $imgData, $tipoImg, $user_id);
    
    } else {
        $query = "UPDATE Usuarios 
                  SET nombre = ?, nomUs = ?, correo = ?, nacimiento = ?, fechaM = CURRENT_DATE() 
                  WHERE idUsuario = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $nombreCompleto, $nombreUsuario, $email, $fechaNacimiento, $user_id);
    }
    
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        mysqli_stmt_close($stmt);
        mysqli_stmt_close($check_stmt);
        mysqli_close($conn);
        header('Location: ../front/Perfil.php?success=user_updated');
        exit();
    } else {
        mysqli_stmt_close($stmt);
        mysqli_stmt_close($check_stmt);
        mysqli_close($conn);
        header('Location: ../front/EditData.php?error=user_not_updated');
        exit();
    }
}


    function existeCampo($conn, $campo, $valor) {
        $query = "SELECT idUsuario FROM Usuarios WHERE $campo = ? AND idUsuario != ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "si", $valor, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        return mysqli_stmt_num_rows($stmt) > 0;
    }

// Cerramos la conexión
mysqli_close($conn);
?>
