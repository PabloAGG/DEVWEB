<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start(); // Iniciar la sesión para manejar la autenticación
require '../Back/DB_connection.php';
// Verificar si el usuario ha iniciado sesión       
if (!isset($_SESSION['user_id'])) {
    header('Location: ../front/InicioSesion.php'); // Redirigir al inicio de sesión si no está autenticado
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
    <title>Panel de Administracion</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/Admin.css">


</head>

<body class="cuerpo">

    <header>
        <a href="../front/dashboard.php"><img src="../front/LOGOWEB.jpg" width="60px" height="60px"></a>
        <h6 id="titulo">DEVWEB</h6>
        <div class="identificador">
            <!-- <a href="Perfil.html"><img src="Gojo.jpg" alt="" class="img-circular"></a> -->
            <button onclick="location.href='../front/Perfil.php'"><?php echo $user_name ?></button>
        </div>
    </header>
    <main>

        <select name="Consultas" id="Consultas">
            <option value="">Consultar:</option>
            <option value="UsAct">Usuarios Activos</option>
            <option value="PubAct">Publicaciones Activas</option>
            <option value="UsLikes">Usuarios Con Más likes</option>
            <option value="UsComent">Usuarios Con Más Comentarios</option>
              <option value="UsPublicaciones">Usuarios Con Más Publicaciones</option>
            <option value="UsNew">Usuarios Recientes</option>
            <option value="PubLikes">Publicaciones Con Mas Likes</option>

        </select>

        <button id="exportar-csv">Exportar a CSV</button>
<button id="exportar-pdf">Exportar a PDF</button>
       <div id="resultados-consultas"></div>

    </main>

  
<script src="../js/Admin.js"></script>
</body>

</html>