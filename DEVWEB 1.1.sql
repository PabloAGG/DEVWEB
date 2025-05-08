CREATE DATABASE DEVWEB;
USE  DEVWEB;
select*from publicaciones;
ALTER TABLE Publicaciones modify fechaC DATETIME DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha de creación del registro';
-- Tabla de usuarios registrados
CREATE TABLE Usuarios (
    idUsuario INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único del usuario',
    nombre NVARCHAR(100) NOT NULL COMMENT 'Nombre completo del usuario',
    nomUs NVARCHAR(50) NOT NULL COMMENT 'Nombre de usuario para el inicio de sesión',
    contra NVARCHAR(255) NOT NULL COMMENT 'Contraseña hasheada del usuario',
    correo NVARCHAR(50) NOT NULL COMMENT 'Correo electrónico del usuario',
    nacimiento DATE NOT NULL COMMENT 'Fecha de nacimiento del usuario',
    imagen mediumblob NULL COMMENT 'Imagen de perfil del usuario',
    estado BOOLEAN DEFAULT 1 COMMENT 'Estado del usuario: 1=activo, 0=inactivo',
    usAdmin BOOLEAN DEFAULT 0 COMMENT 'Si el usuario es administrador: 1=admin, 0=normal',
    fechaC DATETIME DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha de creación del registro',
    fechaM DATE NULL COMMENT 'Fecha de modificación del registro',
    tipo_Img nvarchar(100) null comment 'MIME imagen'
) COMMENT='Tabla que almacena la información de los usuarios de la pagina';
-- Tabla de categorías para clasificar publicaciones
CREATE TABLE Categorias (
    idCat INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la categoría',
    estado BOOLEAN DEFAULT 1 COMMENT 'Estado de la categoría: 1=activa, 0=inactiva',
    nombre VARCHAR(50) UNIQUE COMMENT 'Nombre único de la categoría'
) COMMENT='Categorías disponibles para clasificar las publicaciones';

INSERT INTO Categorias (nombre, estado)
VALUES 
    ('Moda', 1),
    ('Tecnología', 1),
    ('Cocina', 1),
    ('Deportes', 1),
    ('Arte', 1);


-- Tabla de publicaciones realizadas por los usuarios
CREATE TABLE Publicaciones (
    idPubli INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de la publicación',
    titulo VARCHAR(50) NOT NULL COMMENT 'Título de la publicación',
    descripcion VARCHAR(255) NOT NULL COMMENT 'Descripción o contenido de la publicación',
    categoria VARCHAR(50) comment "categoria asociada a la publicacion",
    estado BOOLEAN DEFAULT 1 COMMENT 'Estado de la publicación: 1=activa, 0=eliminada',
    idUsuario INT COMMENT 'Usuario autor de la publicación',
    fechaC DATE DEFAULT CURRENT_TIMESTAMP() COMMENT 'Fecha de creación de la publicación',
    fechaM DATE NULL COMMENT 'Fecha de última modificación',
    nLikes int default 0,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
        FOREIGN KEY (categoria) REFERENCES Categorias(nombre)
) COMMENT='Publicaciones creadas por los usuarios';

CREATE TABLE Multimedia(
idMulti INT NOT NULL AUTO_INCREMENT PRIMARY KEY COMMENT 'Identificador único de multimedia',
contenido MEDIUMBLOB COMMENT "IMAGEN O VIDEO DE LA PUBLICACION",
    tipo_Img nvarchar(100) null comment 'MIME imagen',
    video boolean comment "definir si se subio un video o una imagen",
idPubli INT NOT NULL,
    FOREIGN KEY (idPubli) REFERENCES Publicaciones(idPubli)
);



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
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'DEVWEB';


DELIMITER //

CREATE  PROCEDURE sp_Usuarios_CRUD (
    IN p_accion VARCHAR(10),       -- 'INSERT', 'UPDATE', 'DELETE'
    IN p_idUsuario INT,
    IN p_nombre NVARCHAR(100),
    IN p_nomUs NVARCHAR(50),
    IN p_contra NVARCHAR(255),
    IN p_correo NVARCHAR(50),
    IN p_nacimiento DATE,
    IN p_Img MEDIUMBLOB,
    IN p_tipoImg nvarchar(100),
    IN p_estado BOOLEAN
)
BEGIN
    IF p_accion = 'INSERT' THEN
        INSERT INTO Usuarios (
            nombre, nomUs, contra, correo, nacimiento, estado, fechaM
        ) VALUES (
            p_nombre, p_nomUs, p_contra, p_correo, p_nacimiento, p_estado, NULL
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
            tipo_Img=p_tipoImg,
            fechaM = CURRENT_DATE()
        WHERE idUsuario = p_idUsuario;

    ELSEIF p_accion = 'DELETE' THEN
        UPDATE Usuarios SET estado = 0 WHERE idUsuario=p_IdUsuario;
    END IF;
END //

DELIMITER ;

-- ---------------------TRIGGERS------------------------------
DELIMITER //

CREATE TRIGGER actualizar_likes_insert
AFTER INSERT ON Likes
FOR EACH ROW
BEGIN
    UPDATE Publicaciones
    SET nLikes = nLikes + 1
    WHERE idPubli = NEW.idPublicacion;
END//

DELIMITER ;



DELIMITER //

CREATE TRIGGER actualizar_likes_delete
AFTER DELETE ON Likes
FOR EACH ROW
BEGIN
    UPDATE Publicaciones
    SET nLikes = nLikes - 1
    WHERE idPubli = OLD.idPublicacion;
END//

DELIMITER ;


-- funciones ----------------------------------------------------
DELIMITER //
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
END;

DELIMITER;

DELIMITER //

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
END//

DELIMITER ;


