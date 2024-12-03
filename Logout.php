<?php
session_start();
session_destroy(); // Destruye todas las variables de sesión
header("Location: Loggin.php"); // Redirige al login
exit();
?>
