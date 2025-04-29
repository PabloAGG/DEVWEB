<?php
require 'DB_connection.php'; // Tu conexión a la base de datos

$user_id = $_GET['id']; // ID del usuario que quieres mostrar
$ruta_default= null;
$query = "SELECT imagen FROM Usuarios WHERE idUsuario = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result) && !empty($row['imagen'])) {
    header("Content-Type: image/jpeg"); 
    echo (is_string($row['imagen'])) ? $row['imagen'] : stream_get_contents($row['imagen']);
} else {
    // Si no hay foto, usar una imagen default
    $ruta_default = '../assets/image_default.png';

    if (file_exists($ruta_default)) {
        header("Content-Type: image/png"); 
        readfile($ruta_default); 
    } 
}
mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
