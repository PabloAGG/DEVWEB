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
 $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : ''; // Cambiar '0' por '' (cadena vacía)
 $categoria_seleccionada = isset($_GET['categoria']) ? $_GET['categoria'] : '';
 
 // Construir la consulta SQL dinámicamente
 $query = "SELECT p.*, m.contenido, m.tipo_Img, m.video, u.nomUs AS autor, u.imagen AS imgPerfil, u.tipo_Img AS tipo_ImgUser,
           FormatearFecha(p.fechaC) AS fecha_formateada,
           (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios,
           (SELECT COUNT(*) FROM Likes WHERE idPublicacion = p.idPubli AND idUsuario = ?) AS hasLiked,
           p.nLikes
           FROM Publicaciones p
           JOIN Multimedia m ON m.idPubli = p.idPubli
           JOIN Usuarios u ON u.idUsuario = p.idUsuario
           WHERE p.estado = 1";
 
 $params = array("i", $user_id); // Inicializar con el tipo 'i' para user_id
 $param_values = array(&$user_id);
 
 if (!empty($busqueda)) {
     $query .= " AND (p.titulo LIKE ? OR p.descripcion LIKE ? OR u.nomUs LIKE ?)";
     $params[0] .= "sss"; // Añadir tipos para los parámetros de búsqueda (strings)
     $busqueda_param = '%' . $busqueda . '%';
     $param_values[] = &$busqueda_param;
     $param_values[] = &$busqueda_param;
     $param_values[] = &$busqueda_param;
 }
 
 if (!empty($categoria_seleccionada)) {
     $query .= " AND p.categoria = ?";
     $params[0] .= "s"; // Añadir tipo para el parámetro de categoría (string)
     $param_values[] = &$categoria_seleccionada;
 }
 
 $query .= " ORDER BY p.fechaC DESC";
 
 $stmt = mysqli_prepare($conn, $query);
 
 // Usar la función array_merge para combinar los tipos y valores de los parámetros
 $bind_params = array_merge(array($params[0]), $param_values);
 // Llama a la función mysqli_stmt_bind_param directamente
 call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt), $bind_params));
 
 mysqli_stmt_execute($stmt);
 $resultBusq = mysqli_stmt_get_result($stmt);
 
 // ... (El resto de tu código HTML para mostrar los resultados)
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
<label for="categoria-select">Filtrar por Categoría:</label>
<select name="categoria" id="categoria-select">
    <option value="">Todas las Categorías</option>
    <?php
    $query_categorias = "SELECT * FROM Categorias";
    $stmt_categorias = mysqli_prepare($conn, $query_categorias);
    mysqli_stmt_execute($stmt_categorias);
    $result_categorias = mysqli_stmt_get_result($stmt_categorias);
    while ($row_categoria = mysqli_fetch_assoc($result_categorias)) {
        $selected = ($categoria_seleccionada == $row_categoria['nombre']) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($row_categoria['nombre']) . '" ' . $selected . '>' . htmlspecialchars($row_categoria['nombre']) . '</option>';
    }
    ?>
</select>
<br>

<div class="contenedor_publicaciones">
  <h3>Resultados de busqueda "<?php echo $busqueda?>"</h3>
<?php
while ($row = mysqli_fetch_assoc($resultBusq)) { 
    $mime = $row['tipo_Img'] ?? 'image/png'; 
    $isVideo = $row['video']; 
    $mediaSrc = 'data:' . $mime . ';base64,' . base64_encode($row['contenido']); 
    $hasLiked = $row['hasLiked'] > 0; 
    $numLikes = $row['nLikes']; 
    $fechaFormateada = $row['fecha_formateada']; 
    $numComentarios = $row['comentarios']; 
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
            <span class="fecha"><?php echo htmlspecialchars($fechaFormateada); ?></span> 
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
            <span class="Coment-count"> 
         <?php echo $numComentarios; ?> 
    </span> 
            <button class="btn share"><i class="fa-solid fa-share"></i> Compartir</button> 
        </div> 
    </div> 
</div> 
<?php } ?> 

</div>




  </main>
 
<script src="../js/BusqAv.js"></script>
<script src="../js/search.js"></script>
        <script src="../js/script.js"></script></body>
</body>
</html>