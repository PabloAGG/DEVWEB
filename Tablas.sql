CREATE DATABASE PagWeb1;
USE PagWeb1;


INSERT INTO Usuarios (nomUs, contra, correo, nacimiento, fechaC, fechaM, nom, apellidos)
VALUES ('jorge17', 'contra123', 'jorge@gmail.com', '2004-06-27', curdate(), CURDATE(), 'Jorge', 'Rodriguez');

CREATE TABLE Usuarios (
    idUsuario INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    imagen BLOB NULL,
    nomUs NVARCHAR(50) NOT NULL ,
    contra NVARCHAR(20) NOT NULL,
    correo NVARCHAR(50) NOT NULL,
    nacimiento DATE NOT NULL,
    fechaC DATE,
    fechaM DATE,
    estado BOOLEAN DEFAULT 0,
    nom NVARCHAR(50) NOT NULL,
    apellidos NVARCHAR(50) NOT NULL
);

CREATE TABLE Publicaciones (
    idPubli INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    fechaC DATE,
    fechaM DATE,
    titulo VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
     imagen BLOB NULL,
    estado BOOLEAN DEFAULT 0,
    idUsuario INT,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);

CREATE TABLE Categorias (
    idCat INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    estado BOOLEAN DEFAULT 0,
    nombre VARCHAR(50)
);
CREATE TABLE Likes (
    idLike INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    idPublicacion INT,
    idUsuario INT, 
    FOREIGN KEY (idPublicacion) REFERENCES Publicaciones(idPubli),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);

CREATE TABLE Comentarios (
    idComentario INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    comen varchar(255),
    idPublicacion INT,
    idUsuario INT, 
    FOREIGN KEY (idPublicacion) REFERENCES Publicaciones(idPubli),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);





INSERT INTO Categorias (nombre) VALUES ('Videojuegos');
INSERT INTO Categorias (nombre) VALUES ('Desarrollo Web');

INSERT INTO Categorias (nombre) VALUES ('Moda');

INSERT INTO Categorias (nombre) VALUES ('Gastronomia');
INSERT INTO Categorias (nombre) VALUES ('Musica');

INSERT INTO Categorias (nombre) VALUES ('Politica');
INSERT INTO Categorias (nombre) VALUES ('Fotografia');


