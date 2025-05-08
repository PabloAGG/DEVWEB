
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Sesion</title>
    <script src="https://kit.fontawesome.com/093074d40c.js" crossorigin="anonymous"></script>
   
    <link rel="stylesheet" href="../css/InicioSesio.css">
</head>

<body class="cuerpo">
    <header>
    <img src="LOGOWEB.jpg" width="60px" height="60px">   <h6 id="titulo">DEVWEB</h6>
    
 </header>
<main>

        <div class="contenedor_login">
                   
            <!--Login-->
            <form id="formLogin" action="../Back/Login.php"  class="form__log" method="POST">
                <h2 id="IS">Iniciar Sesión</h2>
                <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-user"></i></span>
            <input id="usuario" type="user" name="user_name" placeholder="" required>
            <label for="user_name">Usuario</label>
        </div>

        <div class="contenedor-input">
            <span class="icono"><i class="fa-solid fa-lock"></i></span>
            <input id="password" type="password" name="password_user" placeholder=""  required>
            <label for="password_user">Contraseña</label>
        </div>
                <button class="btnEx" type="submit" >Entrar</button>   <br>
                
                <div class="Contenedor_regus">
            <span>¿Aún no tienes una cuenta?</span>
           <a id="registro" href="RegistroUs.php"> Registrate es gratis!</a><br>

        </div>
                </form>
        </div>

   

</main>

    <footer>
        <p id="datos">DEVWEB<br>Pablo Garcia 2006335<br>Jorge Rodriguez 2007179</p>
</footer>

<script src="../js/Login.js"></script>
</body>
</html>