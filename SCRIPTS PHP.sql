-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS DEVWEB;

-- Seleccionar la base de datos a usar
USE DEVWEB;

-- Tabla de usuarios registrados
CREATE TABLE Usuarios (
    idUsuario INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del usuario',
    nombre NVARCHAR(100) NOT NULL COMMENT 'Nombre completo del usuario',
    nomUs NVARCHAR(50) NOT NULL COMMENT 'Nombre de usuario para el inicio de sesión',
    contra NVARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada del usuario',
    correo NVARCHAR(50) NOT NULL COMMENT 'Correo electrónico del usuario',
    nacimiento DATE NOT NULL COMMENT 'Fecha de nacimiento del usuario',
    imagen MEDIUMBLOB NULL COMMENT 'Imagen de perfil del usuario',
    estado BOOLEAN DEFAULT 1 COMMENT 'Estado del usuario: 1=activo, 0=inactivo',
    usAdmin BOOLEAN DEFAULT 0 COMMENT 'Si el usuario es administrador: 1=admin, 0=normal',
    fechaC DATETIME DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha de creación del registro',
    fechaM DATE NULL COMMENT 'Fecha de modificación del registro',
    tipo_Img NVARCHAR(100) NULL COMMENT 'Tipo MIME de la imagen'
) COMMENT='Tabla que almacena la información de los usuarios de la página';
Create table Seguidores(
idSeguidores int not null auto_increment primary key comment'identificador de la fila seguidores',
idSeguido int not null comment'usuario que fue seguido dentro de la app',
idSeguidor int not null comment'usuario que siguio a otro dentro de la app',
 FOREIGN KEY (idSeguido) REFERENCES Usuarios(idUsuario),
 FOREIGN KEY (idSeguidor) REFERENCES Usuarios(idUsuario)
)comment="Tabla para almacenar la relacion seguidores y seguidos para cada usuario dentro de la app";


-- Tabla de categorías para clasificar publicaciones
CREATE TABLE Categorias (
    idCat INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la categoría',
    estado BOOLEAN DEFAULT 1 COMMENT 'Estado de la categoría: 1=activa, 0=inactiva',
    nombre VARCHAR(50) UNIQUE COMMENT 'Nombre único de la categoría'
) COMMENT='Categorías disponibles para clasificar las publicaciones';

-- Tabla de publicaciones realizadas por los usuarios
CREATE TABLE Publicaciones (
    idPubli INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la publicación',
    titulo VARCHAR(50) NOT NULL COMMENT 'Título de la publicación',
    descripcion VARCHAR(255) NOT NULL COMMENT 'Descripción o contenido de la publicación',
    categoria VARCHAR(50) COMMENT "categoría asociada a la publicación",
    estado BOOLEAN DEFAULT 1 COMMENT 'Estado de la publicación: 1=activa, 0=eliminada',
    idUsuario INT COMMENT 'Usuario autor de la publicación',
    fechaC DATE DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha de creación de la publicación', -- Se crea como DATE inicialmente
    fechaM DATE NULL COMMENT 'Fecha de última modificación',
    nLikes INT DEFAULT 0,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (categoria) REFERENCES Categorias(nombre)
) COMMENT='Publicaciones creadas por los usuarios';

-- Modificar la columna fechaC en Publicaciones a DATETIME (como estaba en el ALTER original)
-- Nota: La tabla se creó con fechaC DATE, esta modificación la cambia a DATETIME.
ALTER TABLE Publicaciones MODIFY fechaC DATETIME DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha de creación del registro';


-- Tabla para multimedia asociada a las publicaciones
CREATE TABLE Multimedia(
    idMulti INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de multimedia',
    contenido MEDIUMBLOB COMMENT "Imagen o video de la publicación",
    tipo_Img NVARCHAR(100) NULL COMMENT 'Tipo MIME de la imagen/video',
    video BOOLEAN COMMENT "Define si se subió un video (1) o una imagen (0)",
    idPubli INT NOT NULL,
    video_path nvarchar(255) comment"Si se subio un video esta sera la ruta de donde se obtendra.",
    FOREIGN KEY (idPubli) REFERENCES Publicaciones(idPubli)
) COMMENT='Almacena archivos multimedia para las publicaciones';
alter table Multimedia add 
    video_path nvarchar(255) comment"Si se subio un video esta sera la ruta de donde se obtendra.";
-- Tabla que almacena los "likes" de usuarios a publicaciones
CREATE TABLE Likes (
    idLike INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del like',
    idPublicacion INT COMMENT 'ID de la publicación que recibió el like',
    idUsuario INT COMMENT 'ID del usuario que dio el like',
    FOREIGN KEY (idPublicacion) REFERENCES Publicaciones(idPubli),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
) COMMENT='Registro de likes que los usuarios dan a las publicaciones';

-- Tabla de comentarios realizados por los usuarios en publicaciones
CREATE TABLE Comentarios (
    idComentario INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del comentario',
    comen VARCHAR(255) COMMENT 'Contenido del comentario',
    idPublicacion INT COMMENT 'ID de la publicación comentada',
    idUsuario INT COMMENT 'ID del usuario que hizo el comentario',
    fechaC DATETIME DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha en la que se hizo el comentario',
    FOREIGN KEY (idPublicacion) REFERENCES Publicaciones(idPubli),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
) COMMENT='Comentarios realizados por los usuarios en publicaciones';

-- Tabla para almacenar notificaciones
CREATE TABLE Notificaciones (
    idNotificacion INT AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la notificación',
    idUsuarioRecibe INT COMMENT 'Usuario que recibe la notificación',
    idUsuarioEmite INT COMMENT 'Usuario que generó la notificación (ej. el que dio like)',
    idPublicacion INT COMMENT 'Publicación relacionada con la notificación',
    tipo ENUM('like', 'comentario', 'compartir') NOT NULL COMMENT 'Tipo de notificación', -- Tipo de notificación
    mensaje VARCHAR(255) NOT NULL COMMENT 'Mensaje de la notificación', -- Mensaje de la notificación
    fechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación de la notificación',
    leida BOOLEAN DEFAULT 0 COMMENT 'Estado de lectura: 0: No leída, 1: Leída', -- 0: No leída, 1: Leída
    FOREIGN KEY (idUsuarioRecibe) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idUsuarioEmite) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idPublicacion) REFERENCES Publicaciones(idPubli)
)COMMENT="Tabla para almacenar notificaciones relacionadas con las interacciones en las publicaciones";

CREATE TABLE `conversaciones` (
  `id_conversacion` INT AUTO_INCREMENT PRIMARY KEY,
  `id_usuario1` INT NOT NULL,
  `id_usuario2` INT NOT NULL,
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `ultima_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_usuario1`) REFERENCES `usuarios`(`idUsuario`) ON DELETE CASCADE,
  FOREIGN KEY (`id_usuario2`) REFERENCES `usuarios`(`idUsuario`) ON DELETE CASCADE,
  UNIQUE KEY `unique_conversation` (`id_usuario1`, `id_usuario2`) -- Para evitar duplicados, asegúrate que id_usuario1 < id_usuario2 en tu lógica de creación
);

CREATE TABLE `mensajes` (
  `id_mensaje` INT AUTO_INCREMENT PRIMARY KEY,
  `id_conversacion` INT NOT NULL,
  `id_emisor` INT NOT NULL,
  `contenido_mensaje` TEXT NOT NULL,
  `fecha_envio` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `leido` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`id_conversacion`) REFERENCES `conversaciones`(`id_conversacion`) ON DELETE CASCADE,
  FOREIGN KEY (`id_emisor`) REFERENCES `usuarios`(`idUsuario`) ON DELETE CASCADE
);

-- Insertar categorías iniciales
INSERT INTO Categorias (nombre, estado) VALUES
    ('Moda', 1),
    ('Tecnología', 1),
    ('Cocina', 1),
    ('Deportes', 1),
    ('Arte', 1);

-- ---------------------FUNCIONES------------------------------

-- Delimitador para la definición de funciones, procedimientos y triggers
DELIMITER //

-- Función para extraer un fragmento de una descripción
CREATE FUNCTION ExtractoDescripcion(descripcion TEXT, longitud INT)
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE resultado VARCHAR(255);
    IF LENGTH(descripcion) > longitud THEN
        SET resultado = CONCAT(LEFT(descripcion, longitud), '...');
    ELSE
        SET resultado = descripcion;
    END IF;
    RETURN resultado;
END// -- Delimitador de fin de función

-- Restaurar el delimitador
DELIMITER ;

-- Delimitador para la definición de funciones, procedimientos y triggers
DELIMITER //

-- Función para formatear una fecha de forma amigable (ej. "Hace 5 minutos", "Hoy a las...")
CREATE FUNCTION FormatearFecha(fecha DATETIME)
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE resultado VARCHAR(255);
    DECLARE diff INT;
    SET diff = TIMESTAMPDIFF(SECOND, fecha, NOW());

    IF diff < 60 THEN
        SET resultado = 'Hace unos segundos';
    ELSEIF diff < 3600 THEN
        SET resultado = CONCAT('Hace ', FLOOR(diff / 60), ' minutos');
    ELSEIF diff < 86400 AND DATE(fecha) = CURDATE() THEN
        SET resultado = CONCAT('Hoy a las ', DATE_FORMAT(fecha, '%H:%i'));
    ELSEIF diff < 172800 AND DATE(fecha) = CURDATE() - INTERVAL 1 DAY THEN
        SET resultado = CONCAT('Ayer a las ', DATE_FORMAT(fecha, '%H:%i'));
    ELSEIF YEAR(fecha) = YEAR(NOW()) THEN
        SET resultado = DATE_FORMAT(fecha, '%d de %M a las %H:%i');
    ELSE
        SET resultado = DATE_FORMAT(fecha, '%d de %M de %Y a las %H:%i');
    END IF;

    RETURN resultado;
END// -- Delimitador de fin de función

-- Restaurar el delimitador
DELIMITER ;

-- ---------------------PROCEDIMIENTOS ALMACENADOS (SP)------------------------------

-- Delimitador para la definición de funciones, procedimientos y triggers
DELIMITER //

-- Procedimiento almacenado para operaciones CRUD en la tabla Usuarios
CREATE PROCEDURE sp_Usuarios_CRUD (
    IN p_accion VARCHAR(10),       -- 'INSERT', 'UPDATE', 'DELETE'
    IN p_idUsuario INT,
    IN p_nombre NVARCHAR(100),
    IN p_nomUs NVARCHAR(50),
    IN p_contra NVARCHAR(255),
    IN p_correo NVARCHAR(50),
    IN p_nacimiento DATE,
    IN p_Img MEDIUMBLOB,
    IN p_tipoImg NVARCHAR(100),
    IN p_estado BOOLEAN
)
BEGIN
    IF p_accion = 'INSERT' THEN
        INSERT INTO Usuarios (
            nombre, nomUs, contra, correo, nacimiento, estado, fechaM, imagen, tipo_Img
        ) VALUES (
            p_nombre, p_nomUs, p_contra, p_correo, p_nacimiento, p_estado, NULL, p_Img, p_tipoImg
        );

    ELSEIF p_accion = 'UPDATE' THEN
        UPDATE Usuarios
        SET nombre = p_nombre,
            nomUs = p_nomUs,
            contra = p_contra,
            correo = p_correo,
            nacimiento = p_nacimiento,
            estado = p_estado,
            imagen = p_Img,
            tipo_Img = p_tipoImg,
            fechaM = CURRENT_DATE()
        WHERE idUsuario = p_idUsuario;

    ELSEIF p_accion = 'DELETE' THEN
        -- Eliminación lógica: actualizar estado a inactivo
        UPDATE Usuarios SET estado = 0 WHERE idUsuario = p_idUsuario;
    END IF;
END //

-- Restaurar el delimitador
DELIMITER ;

-- ---------------------TRIGGERS------------------------------

-- Delimitador para la definición de funciones, procedimientos y triggers
DELIMITER //

-- Trigger que actualiza el contador de likes en Publicaciones después de un INSERT en Likes
CREATE TRIGGER actualizar_likes_insert
AFTER INSERT ON Likes
FOR EACH ROW
BEGIN
    UPDATE Publicaciones
    SET nLikes = nLikes + 1
    WHERE idPubli = NEW.idPublicacion;
END//

-- Restaurar el delimitador
DELIMITER ;

-- Delimitador para la definición de funciones, procedimientos y triggers
DELIMITER //

-- Trigger que actualiza el contador de likes en Publicaciones después de un DELETE en Likes
CREATE TRIGGER actualizar_likes_delete
AFTER DELETE ON Likes
FOR EACH ROW
BEGIN
    UPDATE Publicaciones
    SET nLikes = nLikes - 1
    WHERE idPubli = OLD.idPublicacion;
END//

-- Restaurar el delimitador
DELIMITER ;

-- ---------------------VISTAS (VIEWS)---------------------------
-- Vista para obtener las últimas publicaciones con información adicional
CREATE VIEW ultimas_publicaciones AS
SELECT
    p.*, -- Seleccionar todas las columnas de Publicaciones
    m.contenido,
    m.tipo_Img AS tipo_ImgPubli, -- Renombrar para evitar conflicto con imagen de usuario
    m.video, m.video_path,
    u.nomUs AS autor,
    u.imagen AS imgPerfil,
    u.tipo_Img AS tipo_ImgUser,
    FormatearFecha(p.fechaC) AS fecha_formateada, -- Usar la función para formatear la fecha
    ExtractoDescripcion(p.descripcion, 100) AS extracto, -- Usar la función para el extracto
    (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios -- Contar comentarios
FROM Publicaciones p
JOIN Multimedia m ON m.idPubli = p.idPubli
JOIN Usuarios u ON u.idUsuario = p.idUsuario
WHERE p.estado = 1 -- Solo publicaciones activas
ORDER BY p.fechaC DESC; -- Ordenar por fecha de creación descendente

-- Vista para obtener las publicaciones con más likes
CREATE VIEW publicaciones_mas_likes AS
SELECT
    p.*, -- Seleccionar todas las columnas de Publicaciones
    m.contenido,
    m.tipo_Img AS tipo_ImgPubli, -- Renombrar
    m.video,m.video_path,
    u.nomUs AS autor,
    u.imagen AS imgPerfil,
    u.tipo_Img AS tipo_ImgUser,
    FormatearFecha(p.fechaC) AS fecha_formateada, -- Usar la función
    ExtractoDescripcion(p.descripcion, 100) AS extracto, -- Usar la función
    (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios -- Contar comentarios
FROM Publicaciones p
JOIN Multimedia m ON m.idPubli = p.idPubli
JOIN Usuarios u ON u.idUsuario = p.idUsuario
WHERE p.estado = 1 -- Solo publicaciones activas
ORDER BY p.nLikes DESC; -- Ordenar por número de likes descendente

-- Vista para obtener las publicaciones con más comentarios
CREATE VIEW publicaciones_mas_comentadas AS
SELECT
    p.*, -- Seleccionar todas las columnas de Publicaciones
    m.contenido,
    m.tipo_Img AS tipo_ImgPubli, -- Renombrar
    m.video,m.video_path,
    u.nomUs AS autor,
    u.imagen AS imgPerfil,
    u.tipo_Img AS tipo_ImgUser,
    FormatearFecha(p.fechaC) AS fecha_formateada, -- Usar la función
    ExtractoDescripcion(p.descripcion, 100) AS extracto, -- Usar la función
    (SELECT COUNT(*) FROM Comentarios WHERE idPublicacion = p.idPubli) AS comentarios -- Contar comentarios
FROM Publicaciones p
JOIN Multimedia m ON m.idPubli = p.idPubli
JOIN Usuarios u ON u.idUsuario = p.idUsuario
WHERE p.estado = 1 -- Solo publicaciones activas
ORDER BY comentarios DESC; -- Ordenar por número de comentarios descendente

-- Vista para obtener datos básicos del usuario para la sesión
CREATE VIEW datos_sesion AS
SELECT
    idUsuario,
    nomUs,
    nombre,
    correo,
    imagen,
    usAdmin,
    nacimiento
FROM Usuarios
WHERE estado = 1; -- Solo usuarios activos

-- Vista para obtener comentarios de publicaciones con datos del usuario que comenta
CREATE VIEW comentarios_publicacion AS
SELECT
    c.comen,
    c.idPublicacion,
    u.nomUs,
    u.imagen,
    u.tipo_Img,
    FormatearFecha(c.fechaC) AS fecha_formateada -- Usar la función para formatear la fecha del comentario
FROM Comentarios c
JOIN Usuarios u ON c.idUsuario = u.idUsuario
ORDER BY c.fechaC DESC; -- Ordenar por fecha del comentario descendente

-- Vista para contar los likes por usuario
CREATE VIEW consulta_uslikes AS
SELECT
    u.idUsuario,
    u.nomUs AS Usuario,
    COUNT(l.idUsuario) AS totalLikes -- Contar los likes asociados a cada usuario
FROM Usuarios u
LEFT JOIN Likes l ON u.idUsuario = l.idUsuario
GROUP BY u.idUsuario
ORDER BY totalLikes DESC; -- Ordenar por total de likes descendente

-- Vista para contar las publicaciones por usuario
CREATE VIEW consulta_usPubli AS
SELECT
    u.idUsuario,
    u.nomUs AS Usuario,
    COUNT(p.idUsuario) AS totalPublicaciones -- Contar las publicaciones asociadas a cada usuario
FROM Usuarios u
LEFT JOIN Publicaciones p ON u.idUsuario = p.idUsuario
GROUP BY u.idUsuario
ORDER BY totalPublicaciones DESC; -- Ordenar por total de publicaciones descendente

-- Vista para contar los comentarios por usuario
CREATE VIEW consulta_usComent AS
SELECT
    u.idUsuario,
    u.nomUs AS Usuario,
    COUNT(c.idUsuario) AS totalComentarios -- Contar los comentarios asociados a cada usuario
FROM Usuarios u
LEFT JOIN Comentarios c ON u.idUsuario = c.idUsuario
GROUP BY u.idUsuario
ORDER BY totalComentarios DESC; -- Ordenar por total de comentarios descendente

-- Vista para obtener datos de los usuarios más recientes
CREATE VIEW consulta_usNew AS
SELECT
    idUsuario,
    nomUs AS Usuario,
    nombre,
    correo,
    usAdmin,
    nacimiento,
    fechaC AS Alta -- Fecha de alta del usuario
FROM Usuarios
ORDER BY fechaC DESC; -- Ordenar por fecha de creación descendente

-- Vista para obtener publicaciones con su contador de likes
CREATE VIEW consulta_pubLikes AS
SELECT
    idPubli AS idPublicacion,
    titulo,
    descripcion,
    categoria,
    nLikes AS Likes
FROM Publicaciones
WHERE estado = 1 -- Solo publicaciones activas
ORDER BY Likes DESC; -- Ordenar por número de likes descendente
-- CONTRASENA BDD JORGE 9na]H36az*rcut)z
