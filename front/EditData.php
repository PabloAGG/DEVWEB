<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
// Verificar si el usuario ha iniciado sesión       

$user_id = $_SESSION['user_id'];
$sql = "SELECT nomUs, nombre,correo, imagen, usAdmin,nacimiento FROM Usuarios WHERE idUsuario = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $user_name = $row['nomUs'];
    $full_name = $row['nombre'];
    $user_email = $row['correo'];
    $profile_image = $row['imagen'];
    $user_role = $row['usAdmin']; 
    $birth_date = $row['nacimiento'];
} else {
    echo "Error: No se encontró el usuario.";
    exit();
}



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica tus datos</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/registro-edit.css">
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>

</head>
<body class="cuerpo">
   
    <header>

    <div class="logo">   <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px"></a>   <h6 id="titulo">DEVWEB</h6></div>
        <div class="barrPrin">
   <button onclick="location.href='dashboard.php'">Inicio</button>
   <button onclick="location.href='Perfil.php'">Perfil</button>
     <button onclick="location.href='BusqAv.php'"> Busq Av</button> 
     <button onclick="location.href='../Back/LogOut.php'">Cerrar sesion</button>
            </div>
            <div class="search-container">
             <input type="text" class="search-bar" placeholder="Buscar...">
             <button class="search-button"><i class="fa-solid fa-magnifying-glass"></i></button>
           </div>
                   <div class="identificador">
   <!-- <a href="Perfil.html"><img src="Gojo.jpg" alt="" class="img-circular"></a> -->
   <button onclick="location.href='Perfil.php'"><?php echo $user_name?></button>
                   </div>
           
       </header>
    
<main>
<div class="contenedor_FormReg">
<form class="formRegUs" action="../Back/ModProfile.php" method="post" enctype="multipart/form-data">
    <h3>Modifica tus datos</h3><br>

    <div id="img-contenedor" class="contenedor-input">
             <span class="icono"><i class="fa-solid fa-image"></i></span>
             <label for="imgRuta">Foto de Perfil:</label>  
             <br>
             <img id="imgPerfil" src="#" alt="Vista previa de la imagen" style="display: none; width: 100px;">
             <br>
             <input class="inputImgPerfil" type="file" id="imgRuta" name="imgRuta" accept="image/*" onchange="previewImage()">
        </div>
        
    <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-signature"></i></span>
            <input type="text" name="nombre_completo" value="<?php echo $full_name?>" required>
            <label for="nombre_completo">Nombre completo</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-user"></i></span>
            <input type="text" name="nombre_usuario" value="<?php echo $user_name?>" required>
            <label for="nombre_usuario">Nombre de Usuario</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email_usuario" value="<?php echo $user_email?>" required>
            <label for="email_usuario">Correo</label>
        </div>

        <div id="psw-contenedor" class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="contraseña_usuario" placeholder="" >
            <label for="contraseña_usuario">Contraseña</label>
        </div>

        <div id="psw-contenedor2" class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="contraseña_Check" placeholder="" >
            <label for="contraseña_usuario"> Confirma tu contraseña</label>
        </div>

        <!-- <div id="error-contraseña" style="color: red; display: none;">
             La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.
        </div> -->

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-cake-candles"></i></span>
            <input type="date" name="fecha_usuario" placeholder="<?php echo $birth_date?>" required>
            <label for="fecha_usuario">Fecha de nacimiento</label>
        </div>
         <label for="#"> <input type="checkbox" name="psw-change" id="psw-change" onclick="togglePasswordVisibility()">Cambiar contraseña</label><br>
    <button class="btnEx" type="submit" >Modificar</button>  <button class="btnEx" type="button" onclick="location.href='Perfil.php'">Regresar</button>
</form>

</div>
</main>

<footer>
    <p id="datos">DEVWEB<br>Pablo Garcia 2006335<br>Jorge Rodriguez 2007179</p>
</footer>

<script src="../js/script.js"></script>
<script src="../js/Registro.js"></script>
<script src="../js/EditData.js"></script>
</body>
</html>