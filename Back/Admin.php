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

    
</head>
<body  class="cuerpo">

 <header>
  <a href="../front/dashboard.php"><img src="../front/LOGOWEB.jpg" width="60px" height="60px"></a>  <h6 id="titulo">DEVWEB</h6>
  <div class="identificador">
   <!-- <a href="Perfil.html"><img src="Gojo.jpg" alt="" class="img-circular"></a> -->
   <button onclick="location.href='../front/Perfil.php'"><?php echo $user_name?></button>
                   </div>
      </header>
<main>
    <div class="consultas">
<?php
$sql = "SELECT * FROM Usuarios";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
        <h2>Consulta de Usuarios</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                 
                    <th>Rol</th>
                    <th>Nacimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) == 0) { ?>
                    <tr>
                        <td colspan="5">No hay usuarios registrados.</td>
                    </tr>
                <?php }  ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['idUsuario']; ?></td>
                        <td><?php echo $row['nombre']; ?></td>
                        <td><?php echo $row['correo']; ?></td>
                        <td><?php echo $row['usAdmin'] ? 'Administrador' : 'Usuario'; ?></td>
                        <td><?php echo $row['nacimiento']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</main>

<footer>
    <p id="datos">DEVWEB<br>Pablo Garcia 2006335<br>Jorge Rodriguez 2007179</p>
</footer>

</body>
</html>