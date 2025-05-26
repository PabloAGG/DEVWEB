<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
// Verificar si el usuario ha iniciado sesión       
if (!isset($_SESSION['user_id'])) {
    header('Location: ../front/InicioSesion.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
}
$user_id = $_SESSION['user_id'];
 $sql = "SELECT * FROM datos_sesion WHERE idUsuario = ?";
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

if (isset($_GET['leer_notificacion']) && is_numeric($_GET['leer_notificacion'])) {
 $idNotificacion = $_GET['leer_notificacion'];
 marcarNotificacionLeida($conn, $idNotificacion);
 header("Location: dashboard.php"); // Redirigir para evitar re-procesamiento
 exit();
}
function marcarNotificacionLeida($conn, $idNotificacion) {
 $query = "UPDATE Notificaciones SET leida = 1 WHERE idNotificacion = ?";
 $stmt = mysqli_prepare($conn, $query);
 mysqli_stmt_bind_param($stmt, "i", $idNotificacion);
 mysqli_stmt_execute($stmt);
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifica tus datos</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/edit.css">
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="cuerpo">
  <header>
 <div class="logo">  <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px" alt="Logo DEVWEB"></a></div>
 <div class="barrPrin">
<button onclick="location.href='dashboard.php'">Inicio</button>
<button onclick="location.href='Perfil.php'">Perfil</button>
<button onclick="location.href='BusqAv.php'">Categorias</button>
<button onclick="location.href='../Back/LogOut.php'">Cerrar sesion</button>
</div>
 <div class="search-container">
<input type="text" class="search-bar" placeholder="Buscar...">
 <button class="search-button"><i class="fa-solid fa-magnifying-glass"></i></button>
 </div>
 <div class="notificaciones">
 <button id="btn-notificaciones" title="Notificaciones">
<i class="fa-solid fa-bell"></i>
<span id="contador-notificaciones" class="contador-notificaciones">0</span>
 </button>
         <div id="lista-notificaciones" class="lista-notificaciones">
 <p>Cargando notificaciones...</p>
 </div>
         </div>


         <div class="mensajes">
<button id="btn-mensajes" title="Mensajes"><i class="fas fa-comment-alt"></i></button>
         </div>


 <div class="identificador">
 <button onclick="location.href='Perfil.php'"><?php echo htmlspecialchars($user_name); ?></button>
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
    <button class="btnEx mod" type="submit" >Modificar</button>  <button class="btnEx eliminar" type="button" onclick="location.href='../Back/bajaUsuario.php'">Eliminar mi cuenta</button>
</form>

</div>
</main>

 <script src="../js/notis.js"></script>
<script src="../js/script.js"></script>
<script src="../js/search.js"></script>
<!-- <script src="../js/Registro.js"></script> -->
<script src="../js/EditData.js"></script>
</body>
</html>