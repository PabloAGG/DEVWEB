<?php
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
// Verificar si el usuario ha iniciado sesión       
if (!isset($_SESSION['user_id'])) {
    header('Location: InicioSesion.php'); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

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
    <title>Mi Perfil</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/Perfil.css">
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
  <div class="contenedor-column">
<div class="perfilUs">
    <img id="imgPerfil" src="Gojo.jpg" alt="Avatar Usuario" class="img-cirUs">
    <ul id="list-perfil">
        <li><strong>Nombre Usuario:</strong><?php echo $user_name?></li>
        <li><strong>Nombre:</strong> <?php echo $full_name?></li>
        <li><strong>Correo:</strong> <?php echo $user_email?></li>
        <li><strong>Edad:</strong> <?php 
        $fechaActual = new DateTime(); // Fecha actual
        $fechaNacimiento = new DateTime($birth_date); // Fecha de nacimiento
        $edad = $fechaActual->diff($fechaNacimiento)->y; // Calcular la diferencia en años
        echo $edad . " años"; ?></li>
        <li><strong>Rol:</strong> <?php echo $user_role == 1 ? 'Administrador' :'Usuario' ; ?></li>
    </ul>
    <div class="btns-perfil">
    <button class="btnEx" onclick="location.href='EditData.php'">Modificar datos</button></div>
<?php
if($user_role == 1 ){ ?>
  <div class="btns-perfil"> <button class="btnEx" onclick="location.href='../Back/Admin.php'">Panel</button> </div>
<?php } ?>

</div>

<div class="contenedor_publi">
<label for="flistpub">Mis Publicaciones</label><br>
<ul class="list-group"></ul>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Titulo1
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Titulo2
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Titulo4
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Titulo5
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Titulo6
    </li>
  </ul>
  <button class="btnEx" >Eliminar</button>
</div>

</div>
</main>

     <footer>
            <p id="datos">DEVWEB<br>Pablo Garcia 2006335<br>Jorge Rodriguez 2007179</p>
      </footer>
      <script src="../js/script.js"></script></body>
</body>
</html>