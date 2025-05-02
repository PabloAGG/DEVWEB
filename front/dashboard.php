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
    <title>Pagina principal</title>
   <link rel="stylesheet" href="../css/estiloslog.css">
   <link rel="stylesheet" href="../css/Dashboard.css">

   <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    
    
<button id="publicar" class="btnEx" onclick="toggleForm()">Publicar</button>       
<div class="EspPub" id="EspPub" style="display: none;">
    <form class="espPubform" action="../BACK/Publicar.php" method="post" enctype="multipart/form-data">
<h2>Crea una Publicacion</h2>
        <input type="text" name="titleP" class="input" placeholder="Titulo"><br>
     
<label for="descP">Descripcion</label><br>
        <textarea name="descP" id="descP"  class="input" aria-label="With textarea"></textarea><br>
   

      <label for="fpubfot">Agrega una foto</label>
      <input type="file" name="fpubfot" id="ffoto"><br>

<label for="select">Categoria</label>
<select name="select" id="categorias">
<option value="" disabled selected hidden></option>
<?php
$query="SELECT * FROM Categorias";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
  echo '<option value="' . htmlspecialchars($row['nombre']) . '">' . htmlspecialchars($row['nombre']) . '</option>';
}
?>
</select>
<br>
  <div id="botonesPubli">
<button class="btnEx" type="submit" >Publicar</button>
<button class="btnEx" type="button" onclick="toggleForm()">carcelar</button></div>
</form>
</div>

<div class="contenedor_Publicaciones">
<?php
$query = "SELECT p.*, m.contenido, m.tipo_Img, m.video, u.nomUs AS autor
          FROM Publicaciones p
          JOIN Multimedia m ON m.idPubli = p.idPubli
          JOIN Usuarios u ON u.idUsuario = p.idUsuario
          WHERE p.estado = 1
          ORDER BY p.fechaC DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $mime = $row['tipo_Img'] ?? 'image/png';
    $isVideo = $row['video'];
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']);
?>
<div class="card-container">
    <div class="card">
        <div class="card-header">
            <span class="autor"><?php echo htmlspecialchars($row['autor']); ?></span>
            <span class="fecha"><?php echo htmlspecialchars($row['fechaC']); ?></span>
        </div>

        <div class="card-body">
            <h2><?php echo htmlspecialchars($row['titulo']); ?></h2>
            <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
            <?php if ($isVideo): ?>
                <video class="media" controls>
                    <source src="<?php echo $mediaSrc; ?>" type="<?php echo $mime; ?>">
                    Tu navegador no soporta video.
                </video>
            <?php else: ?>
                <img class="media" src="<?php echo $mediaSrc; ?>" alt="Contenido multimedia">
            <?php endif; ?>
        </div>

        <div class="card-footer">
            <button class="btn like"><i class="fa-solid fa-thumbs-up"></i> Me gusta</button>
            <button class="btn comment"><i class="fa-solid fa-comment"></i> Comentar</button>
            <button class="btn share"><i class="fa-solid fa-share"></i> Compartir</button>
        </div>
    </div>
</div>
<?php } ?>



<div class="Paginas">
           <nav aria-label="Paginacion">
  <ul id="paginacionPublicaciones" class="pagination"> </ul>
</nav>
</div>

</main>


<script src="../js/script.js"></script>
<script src="../js/dashboard.js"></script>
</html>