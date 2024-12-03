<?php
session_start(); 

if (!isset($_SESSION['nombre'])) {
  
    header("Location: Loggin.php");
    exit();
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
    <link rel="stylesheet" href="rootStyle.css">
</head>
<body>
   
    <div class="derecha">
        <header class="header">
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</p>
            <a href="Logout.php" class="cerrar-sesion">Cerrar Sesión</a>
        </header>

        <main class="main">
                   
            <?php
                include 'mostrarUsuarios.php';
            ?>
        </main>
    </div>

</body>
</html>
