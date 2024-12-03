<?php
session_start(); 

if (!isset($_SESSION['nombre'])) {
    header("Location: Loggin.php");
    exit();
}

if ($_SESSION['nombre'] == 'admin') {
    header("Location: root.php"); 
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal</title>
    <link rel="stylesheet" href="mainStyle.css">
</head>
<body>
    <div class="izq">
        <nav class="nav">
            <a href="?page=usuario">Usuario</a>
            <a href="?page=calendario">Calendario</a>
            <a href="?page=mostrarNotas">Notas</a>
        </nav>
    </div>
    <div class="derecha">
        <header class="header">
            <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>!</p>
            <a href="Logout.php" class="cerrar-sesion">Cerrar Sesión</a>
        </header>

        <main class="main">
            <?php
            if (isset($_GET['page'])) {
                $page = $_GET['page'];

                switch ($page) {
                    case 'usuario':
                        include 'usuario.php';
                        break;
                    case 'calendario':
                        include 'calendario.php';
                        break;
                    case 'mostrarNotas':
                        include 'mostrarNotas.php';
                        break;
                }
            } else {
                echo '<h1 class="centro">Bienvenido, selecciona una opción del menú.</h1>';
            }
            ?>
        </main>
    </div>
</body>
</html>
