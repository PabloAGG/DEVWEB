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
    <title>Crea una Publicacion</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/Publicacion.css">
    
</head>
<body  class="cuerpo">

 <header>
  <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px"></a>  <h6 id="titulo">DEVWEB</h6>
  <div class="identificador">
   <!-- <a href="Perfil.html"><img src="Gojo.jpg" alt="" class="img-circular"></a> -->
   <button onclick="location.href='Perfil.php'"><?php echo $user_name?></button>
                   </div>
      </header>
<main>
<div class="EspPub">
    <form class="espPubform" action="dashboard.html" method="post">

        <input type="text" name="titleP" placeholder="Titulo"><br>
     
<label for="descP">Descripcion</label><br>
        <textarea name="descP" id="descP" aria-label="With textarea"></textarea><br>
   

      <label for="fpubfot">Agrega una foto</label>
      <input type="file" name="fpubfot" id="ffoto"><br>
<label for="select">Categoria</label>
<select name="select" id="categorias">
  <option value="value1">Fotografia</option>
  <option value="value2" selected>Videojuegos</option>
  <option value="value3">Gastronomia</option>
  <option value="value4">Moda</option>
  <option value="value5" >Musica</option>
  <option value="value6">Politica</option>
</select>
<br>
<!-- <ul class="list-group">
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
  </ul> -->
  <div id="botonesPubli">
<button class="btnEx" type="submit" >Publicar</button>
<button class="btnEx" type="button" onclick="location.href='dashboard.php'">carcelar</button></div>
</form>
</div>
</main>

<footer>
    <p id="datos">DEVWEB<br>Pablo Garcia 2006335<br>Jorge Rodriguez 2007179</p>
</footer>

</body>
</html>