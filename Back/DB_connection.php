<?php
date_default_timezone_set('UTC');
$host = "localhost";
$user = "root"; // Ajusta esto si tienes otro usuario
$password = "9na]H36az*rcut)z"; // Ajusta esto si tienes contraseña
$database = "DEVWEB";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}
?>
