<?php
session_start();
require 'DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "0"; // O un valor por defecto
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT COUNT(*) FROM Notificaciones WHERE idUsuarioRecibe = ? AND leida = 0";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
echo $row[0]; // Devuelve solo el número

mysqli_close($conn);
?>