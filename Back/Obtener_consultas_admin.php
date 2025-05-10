<?php

require 'DB_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'No estás autenticado.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$orden = $_GET['Consultas'] ?? 'UsAct'; // Obtener el criterio de ordenación
$formato = $_GET['formato'] ?? 'html';
$consultasHTML = '';
$query = "";
$stmt = null; // Inicializar $stmt fuera del switch
$filename = 'consulta_' . $orden . '_' . date('Y-m-d_H-i-s'); 
switch ($orden) {
    case 'UsAct':
        $query = "SELECT idUsuario, nomUs AS Usuario, nombre, correo, usAdmin, nacimiento FROM Usuarios WHERE estado = 1";
        break;
    case 'PubAct':
        $query = "SELECT p.*, u.nomUs AS Usuario FROM Publicaciones p JOIN Usuarios u ON p.idUsuario = u.idUsuario WHERE p.estado = 1";
        break;
    case 'UsLikes':
        $query = "SELECT * FROM consulta_usLikes";
        break;
    case 'UsComent':
        $query = "SELECT * FROM consulta_usComent";
        break;
    case 'UsPublicaciones':
        $query = "SELECT * FROM consulta_usPubli";
        break;
    case 'UsNew':
        $query = "SELECT * FROM consulta_usNew";
        break;
        case 'PubLikes':
        $query = "SELECT * FROM consulta_pubLikes";
            break;
    default:
        $query = "SELECT idUsuario, nomUs AS Usuario, nombre, correo, usAdmin, nacimiento FROM Usuarios WHERE estado = 1"; // Default
        break;
}

// if ($query) {
//     $stmt = mysqli_prepare($conn, $query);
//     if ($stmt) {
//         mysqli_stmt_execute($stmt);
//         $result = mysqli_stmt_get_result($stmt);

//         // Build the HTML table
//         $consultasHTML = '<table class="admin-table">'; // Apply a class for styling
//         $consultasHTML .= '<thead><tr>';

//         // Get column names dynamically
//         $fieldinfo = mysqli_fetch_fields($result);
//         foreach ($fieldinfo as $field) {
//             $consultasHTML .= '<th>' . htmlspecialchars($field->name) . '</th>';
//         }
//         $consultasHTML .= '</tr></thead><tbody>';

//         while ($row = mysqli_fetch_assoc($result)) {
//             $consultasHTML .= '<tr>';
//             foreach ($row as $value) {
//                 $consultasHTML .= '<td>' . htmlspecialchars($value) . '</td>';
//             }
//             $consultasHTML .= '</tr>';
//         }

//         $consultasHTML .= '</tbody></table>';

//         mysqli_stmt_close($stmt); // Close the statement
//     } else {
//         $consultasHTML = "Error preparing statement: " . mysqli_error($conn);
//     }
// } else {
//     $consultasHTML = "No query selected.";
// }
if ($query) {
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
if ($formato === 'csv') {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            $fieldinfo = mysqli_fetch_fields($result);
            $header = [];
            foreach ($fieldinfo as $field) {
                $header[] = $field->name;
            }
            fputcsv($output, $header);

            while ($row = mysqli_fetch_assoc($result)) {
                fputcsv($output, array_values($row));
            }
            fclose($output);
            exit(); // Importante: Detener la ejecución para que no se envíe HTML adicional
        }else if ($formato === 'pdf') {
           require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php'; // Si "Back" y "vendor" están en el mismo nivel

            // Crear nuevo PDF
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            // Establecer información del documento
            $pdf->SetCreator('DEVWEB');
            $pdf->SetAuthor('DEVWEB');
            $pdf->SetTitle('Consulta Administrativa');
            $pdf->SetSubject('Resultados de la consulta');

            // Establecer cabecera y pie de página (opcional)
            $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'DEVWEB', 'Pablo Garcia y Jorge Rodriguez');
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // Establecer fuentes por defecto
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            // Establecer márgenes
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // Establecer auto salto de página
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // Establecer factor de escala de imagen
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            // Añadir una página
            $pdf->AddPage();

            // Generar HTML para la tabla
            $html = '<table border="1" cellpadding="5">';
            $html .= '<thead><tr>';
            $fieldinfo = mysqli_fetch_fields($result);
            foreach ($fieldinfo as $field) {
                $html .= '<th>' . htmlspecialchars($field->name) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            // Escribir HTML en el PDF
            $pdf->writeHTML($html, true, false, true, false, '');

            // Enviar el PDF al navegador
            $pdf->Output($filename . '.pdf', 'D'); // 'D' para descarga, 'I' para mostrar en el navegador
            exit();
        } else {
            $consultasHTML = '<table class="admin-table">'; // Apply a class for styling
            $consultasHTML .= '<thead><tr>';
            // Get column names dynamically
            $fieldinfo = mysqli_fetch_fields($result);
            foreach ($fieldinfo as $field) {
                $consultasHTML .= '<th>' . htmlspecialchars($field->name) . '</th>';
            }
            $consultasHTML .= '</tr></thead><tbody>';

            while ($row = mysqli_fetch_assoc($result)) {
                $consultasHTML .= '<tr>';
                foreach ($row as $value) {
                    $consultasHTML .= '<td>' . htmlspecialchars($value) . '</td>';
                }
                $consultasHTML .= '</tr>';
            }

            $consultasHTML .= '</tbody></table>';
echo json_encode(['html' => $consultasHTML]);
            mysqli_stmt_close($stmt);
             // Asegúrate de enviar el JSON aquí también
        }
    } else {
        $consultasHTML = "Error preparing statement: " . mysqli_error($conn);
        echo json_encode(['html' => $consultasHTML]);
    }
} else {
    $consultasHTML = "No query selected.";
    echo json_encode(['html' => $consultasHTML]);
}
?>