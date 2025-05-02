<?php
session_start();
require 'DB_connection.php'; // ← verifica que el nombre del archivo tenga la extensión .php
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titleP']);
    $desc = mysqli_real_escape_string($conn, $_POST['descP']);
    $categoria = $_POST['select'];

    // Validar archivo multimedia
    if (isset($_FILES['fpubfot']) && $_FILES['fpubfot']['error'] == UPLOAD_ERR_OK) {
        $imgData = file_get_contents($_FILES['fpubfot']['tmp_name']); 
        $tipoImg = mime_content_type($_FILES['fpubfot']['tmp_name']); 
        $esVideo = strpos($tipoImg, 'video') !== false ? 1 : 0;
        $allowed = ['image/jpeg', 'image/png', 'video/mp4'];
        if (!in_array($tipoImg, $allowed)) {
            header("Location: ../front/dashboard.php?error=formato_invalido");
            exit();
        }
    } else {
        $imgData = NULL; 
        $tipoImg = NULL;
        $esVideo = 0;
    }

    // 1. Insertar en Publicaciones
    $queryPubli = "INSERT INTO Publicaciones (titulo, descripcion, categoria, idUsuario) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $queryPubli);
    mysqli_stmt_bind_param($stmt, "sssi", $titulo, $desc, $categoria, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $idPubli = mysqli_insert_id($conn); // ← Obtenemos el ID de la publicación recién insertada

        // 2. Insertar en Multimedia (solo si se subió algo)
        if ($imgData !== NULL) {
            $queryMulti = "INSERT INTO Multimedia (contenido, tipo_Img, video, idPubli) VALUES (?, ?, ?, ?)";
            $stmt2 = mysqli_prepare($conn, $queryMulti);
            mysqli_stmt_bind_param($stmt2, "ssii", $imgData, $tipoImg, $esVideo, $idPubli);
            mysqli_stmt_execute($stmt2);
        }

        // Redirigir con éxito
        header("Location: ../front/dashboard.php?success=publicacion_creada");
        exit();
    } else {
        header("Location: ../front/dashboard.php?error=fallo_crear");
        exit();
    }
}
?>
