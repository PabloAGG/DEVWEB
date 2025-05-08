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
  <div class="logo">   <a href="dashboard.php"><img src="LOGOWEB.jpg" width="60px" height="60px"></a></div>
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
    
      
<div class="EspPub" id="EspPub" >
    <form class="espPubform" action="../BACK/Publicar.php" method="post" enctype="multipart/form-data">
<h2>Crea una Publicacion</h2><br>
<div>
<label for="tituloP">Titulo</label><br>
        <input type="text" name="titleP" id="titleP" class="input" placeholder="Titulo">
     <label for="select">Categoria</label>
<select name="select" id="select">
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
</div>


<label for="descP">Descripcion</label><br>
        <textarea name="descP" id="descP"  class="input" aria-label="With textarea"></textarea>
   

      <label for="fpubfot">Agrega una foto</label>
      <input type="file" name="fpubfot" id="ffoto"><br>


  <div id="botonesPubli">
<button class="btnPub" type="submit" ><i class="fa-solid fa-pen-to-square"></i>Publicar</button></div>
</form>
</div>

<div class="contenedor_Publicaciones">
<?php
$query = "SELECT p.*, m.contenido, m.tipo_Img, m.video, u.nomUs AS autor, u.imagen AS imgPerfil, u.tipo_Img AS tipo_ImgUser,
        --   (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli) AS likes,
        --   (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios,
        --   (SELECT COUNT(*) FROM Compartidos WHERE idPublicacion = p.idPubli) AS compartidos,
  (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli AND idUsuario = ?) AS hasLiked
          FROM Publicaciones p
          JOIN Multimedia m ON m.idPubli = p.idPubli
          JOIN Usuarios u ON u.idUsuario = p.idUsuario
          WHERE p.estado = 1
          ORDER BY p.fechaC DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id); // Bind del ID del usuario actual
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $mime = $row['tipo_Img'] ?? 'image/png';
    $isVideo = $row['video'];
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']);
    $hasLiked = $row['hasLiked'] > 0;
    $numLikes = $row['nLikes'];
?>
<div class="card-container">
    <div class="card">
        <div class="card-header">
            <div class="userPres">
        <?php if ($row['imgPerfil']!==null) {

$mimeusuario = $row['tipo_ImgUser'] ?? 'image/png';
$base64 = base64_encode($row['imgPerfil']);

echo '<img class="img-cirUs" src="data:' . $mimeusuario . ';base64,' . $base64 . '">';

} else {?> 

<img id="imgPerfil" src="../assets/image_default.png"  alt="Avatar Usuario" class="img-cirUs">
<?php  }   ?>
            <span class="autor"><?php echo htmlspecialchars($row['autor']); ?></span></div>
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
        <button class="btn like-btn <?php echo $hasLiked ? 'liked' : ''; ?>" data-idpubli="<?php echo $row['idPubli']; ?>">
    <i class="fa-solid fa-thumbs-up"></i> 
    <span class="like-text"><?php echo $hasLiked ? 'Te gusta' : 'Me gusta'; ?></span>
</button>
<span class="like-count">
         <?php echo $numLikes; ?>
    </span>
            <button class="btn comment" onclick="window.location.href='publicacion.php?id=<?php echo $row['idPubli']; ?>'"><i class="fa-solid fa-comment"></i> Comentar</button>
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

<script src="../js/likes.js"></script>
<script src="../js/script.js"></script>
<script src="../js/dashboard.js"></script>
</html>