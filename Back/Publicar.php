<?php
session_start();
require 'DB_connection.php'; // Ensure this file correctly establishes $conn

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page or show an error if user_id is not in session
    header("Location: ../front/login.php?error=not_logged_in"); // Example redirect
    exit();
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = mysqli_real_escape_string($conn, $_POST['titleP']);
    $desc = mysqli_real_escape_string($conn, $_POST['descP']);
    $categoria = $_POST['select']; // Assuming this is validated/sanitized as needed

    // Initialize variables for multimedia content and path
    $imgData = NULL;
    $tipoImg = NULL;
    $esVideo = 0;
    $video_path_db = NULL; // Path to be stored in DB (will be relative or absolute as you decide)

    // Define the upload directory for videos
    // This path is relative to *this* PHP script's location (e.g., if Publicar.php is in a 'php_scripts' folder)
    // Adjust if your 'assets' folder is elsewhere.
    $uploadDir = '../assets/'; // e.g., if script is in 'backend/', assets is parallel: root/assets/
    
    // Create the assets directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) { // 0755 permissions are usually good, true for recursive
            // Failed to create directory
            header("Location: ../front/dashboard.php?error=directorio_no_creado");
            exit();
        }
    }

    // Check if a file was uploaded
    if (isset($_FILES['fpubfot']) && $_FILES['fpubfot']['error'] == UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['fpubfot']['tmp_name'];
        $original_filename = $_FILES['fpubfot']['name'];
        $tipoImg = mime_content_type($tmp_name);
        $esVideo = (strpos($tipoImg, 'video') !== false) ? 1 : 0;

        $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
        $allowed_video_types = ['video/mp4']; 

        if ($esVideo == 1) { // It's a video
            if (in_array($tipoImg, $allowed_video_types)) {
                // Generate a unique filename to prevent overwrites
                $fileExtension = pathinfo($original_filename, PATHINFO_EXTENSION);
                $unique_filename = uniqid('video_', true) . '.' . $fileExtension;
                $destination_path = $uploadDir . $unique_filename;

                if (move_uploaded_file($tmp_name, $destination_path)) {
                    $video_path_db = $destination_path; // Store this path in the DB
                    $imgData = NULL; // Video content is not stored in DB, only path
                } else {
                    // Failed to move uploaded file
                    header("Location: ../front/dashboard.php?error=fallo_mover_video");
                    exit();
                }
            } else {
                // Invalid video format
                header("Location: ../front/dashboard.php?error=formato_video_invalido");
                exit();
            }
        } else { // It's an image
            if (in_array($tipoImg, $allowed_image_types)) {
                $imgData = file_get_contents($tmp_name); // Read image data into variable
                $video_path_db = NULL; // No path for images stored directly in DB
            } else {
                // Invalid image format
                header("Location: ../front/dashboard.php?error=formato_imagen_invalido");
                exit();
            }
        }
    } else if (isset($_FILES['fpubfot']) && $_FILES['fpubfot']['error'] != UPLOAD_ERR_NO_FILE) {
        // Handle other file upload errors (e.g., file too large, partial upload)
        header("Location: ../front/dashboard.php?error=error_subida_archivo&code=" . $_FILES['fpubfot']['error']);
        exit();
    }
    // If no file was uploaded (UPLOAD_ERR_NO_FILE), $imgData, $tipoImg, $esVideo, $video_path_db remain as initialized (NULL/0)

    // 1. Insertar en Publicaciones
    $queryPubli = "INSERT INTO Publicaciones (titulo, descripcion, categoria, idUsuario) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $queryPubli);
    if (!$stmt) {
        header("Location: ../front/dashboard.php?error=prepare_publi_failed&detalle=" . urlencode(mysqli_error($conn)));
        exit();
    }
    mysqli_stmt_bind_param($stmt, "sssi", $titulo, $desc, $categoria, $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $idPubli = mysqli_insert_id($conn); // Get the ID of the newly inserted publication
        mysqli_stmt_close($stmt); // Close statement for Publicaciones

        // 2. Insertar en Multimedia (only if a file was uploaded and processed)
        if ($tipoImg !== NULL) { // $tipoImg is set only if a file was successfully processed
            $queryMulti = "INSERT INTO Multimedia (contenido, tipo_Img, video, idPubli, video_path) VALUES (?, ?, ?, ?, ?)";
            $stmt2 = mysqli_prepare($conn, $queryMulti);
            if (!$stmt2) {
                header("Location: ../front/dashboard.php?error=prepare_multi_failed&detalle=" . urlencode(mysqli_error($conn)));
                // Consider deleting the publication entry if multimedia is critical, or log this issue.
                exit();
            }

            // Bind parameters:
            // 1. contenido (BLOB): $imgData (will be NULL for videos)
            // 2. tipo_Img (NVARCHAR): $tipoImg
            // 3. video (BOOLEAN as INT): $esVideo
            // 4. idPubli (INT): $idPubli
            // 5. video_path (NVARCHAR): $video_path_db (will be NULL for images)
            // Using 's' for blob data when it's directly in the variable often works,
            // but for very large files, send_long_data is more robust.
            // NULL values are correctly handled when bound as 's'.
            mysqli_stmt_bind_param($stmt2, "ssiis", $imgData, $tipoImg, $esVideo, $idPubli, $video_path_db);
            
            // For sending BLOB data specifically (if $imgData is not NULL)
            // This step is only strictly necessary if $imgData is large and you encounter issues
            // with just binding it as a string. Often, PHP and MySQL handle it.
            // if ($imgData !== NULL) {
            //    mysqli_stmt_send_long_data($stmt2, 0, $imgData); // 0 is the index of the first '?' (contenido)
            // }


            if (!mysqli_stmt_execute($stmt2)) {
                // Failed to insert multimedia record
                $multimedia_error = mysqli_stmt_error($stmt2);
                mysqli_stmt_close($stmt2);
                mysqli_close($conn);
                // Decide if you want to delete the publication or just warn the user
                header("Location: ../front/dashboard.php?error=fallo_crear_multimedia&detalle=" . urlencode($multimedia_error));
                exit();
            }
            mysqli_stmt_close($stmt2);
        }

        // Successfully created publication (and multimedia if any)
        mysqli_close($conn);
        header("Location: ../front/dashboard.php?success=publicacion_creada");
        exit();

    } else {
        // Failed to insert into Publicaciones
        $publi_error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        header("Location: ../front/dashboard.php?error=fallo_crear_publicacion&detalle=" . urlencode($publi_error));
        exit();
    }
} else {
    // Request method is not POST
    header("Location: ../front/dashboard.php?error=metodo_no_permitido");
    exit();
}
?>