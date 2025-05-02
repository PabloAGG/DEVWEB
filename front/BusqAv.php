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
    <title>Busqueda</title>
    
 <link rel="stylesheet" href="../css/estiloslog.css">
 <link rel="stylesheet" href="../css/BusqAv.css">

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
<div class="BusqAv">
    <form>
        <h4>Por Fechas</h4>
<label for="fechain">Fecha de inicio</label>
<input type="date" name="fechaini" id="fechaini">
<label for="fechafin">Fecha Fin</label>
<input type="date" name="fechafin" id="fechafin">

<h4>Por categorias</h4>
<label for="fcate">Categorias</label>
<ul class="list-group">
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Fotografia
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Videojuegos
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Gastronomia
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Moda
    </li>
    <li class="list-group-item">
      <input class="form-check-input me-1" type="checkbox" value="" aria-label="...">
      Musica
    </li>
  </ul>
<button class="btnEx" type="submit"><i class="fa fa-search"></i></button>
</form>
</div>

<div class="Dashbusq">
    <h2>Publicaciones</h2>
    <div class="card" style="width: 18rem; height: 18rem;">
      
        <div class="card-body">
          <h5 class="card-title">Titulo Publicacion</h5>
          <p class="card-text">descripcion de publicacion </p>
          
        </div>
       
    </div>
  </main>
 

        <script src="../js/script.js"></script></body>
</body>
</html>