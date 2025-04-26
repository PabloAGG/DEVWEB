<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrate!</title>
    <link rel="stylesheet" href="../css/estiloslog.css">
    <link rel="stylesheet" href="../css/registro-edit.css">
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
</head>
<body class="cuerpo">
    
   <header>
    <a href="InicioSesion.php"><img src="LOGOWEB.jpg" width="60px" height="60px"></a>  <h6 id="titulo">DEVWEB</h6>
    </header>

<main>
<div class="contenedor_FormReg">
<form class="formRegUs" action="../Back/Registro.php" method="post">
    <h3>Registrate</h3><br>
    <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-signature"></i></span>
            <input type="text" name="nombre_completo" placeholder="" required>
            <label for="nombre_completo">Nombre completo</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-user"></i></span>
            <input type="text" name="nombre_usuario" placeholder="" required>
            <label for="nombre_usuario">Nombre de Usuario</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email_usuario" placeholder="" required>
            <label for="email_usuario">Correo</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="contraseña_usuario" placeholder="" required>
            <label for="contraseña_usuario">Contraseña</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="contraseña_Check" placeholder="" required>
            <label for="contraseña_usuario"> Confirma tu contraseña</label>
        </div>

        <div id="error-contraseña" style="color: red; display: none;">
             La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial.
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-cake-candles"></i></span>
            <input type="date" name="fecha_usuario" placeholder="" required>
            <label for="fecha_usuario">Fecha de nacimiento</label>
        </div>
        


    <button class="btnEx" >Registrarme</button><br>
    <div class="Contenedor_regus">
        <span>¿Ya tienes una cuenta?</span>
        <a id="registro" href="InicioSesion.php"> Inicia Sesión!</a><br>
    </div>
</form>

</div>
</main>


<script src="../js/Registro.js"></script>
</body>
</html>