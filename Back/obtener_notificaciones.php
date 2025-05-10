<?php
session_start();
require 'DB_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo "<p>No has iniciado sesión.</p>"; // O un mensaje más apropiado
    exit();
}

$user_id = $_SESSION['user_id'];

function obtenerNotificacionesUsuario($conn, $idUsuario) {
    $query = "SELECT * FROM Notificaciones WHERE idUsuarioRecibe = ? ORDER BY fechaCreacion DESC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$notificaciones = obtenerNotificacionesUsuario($conn, $user_id);

if (empty($notificaciones)) {
    echo "<p>No tienes notificaciones.</p>";
} else {
    foreach ($notificaciones as $notificacion) {
        ?>
        <div class="notificacion-cuadro <?php echo $notificacion['leida'] ? 'notificacion-leida' : ''; ?>">
            <p><?php echo htmlspecialchars($notificacion['mensaje']); ?></p>
            <small>Fecha: <?php echo htmlspecialchars($notificacion['fechaCreacion']); ?></small>
            <?php if (!$notificacion['leida']): ?>
                <a href="?leer_notificacion=<?php echo $notificacion['idNotificacion']; ?>">Marcar como leída</a>
            <?php endif; ?>
        </div>
        <?php
    }
}

mysqli_close($conn);
?>