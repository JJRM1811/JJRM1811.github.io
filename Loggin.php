<?php
// Iniciar sesión para manejar mensajes de error
session_start();
$mensajeError = isset($_SESSION['mensajeError']) ? $_SESSION['mensajeError'] : '';
unset($_SESSION['mensajeError']); // Limpiar el mensaje después de mostrarlo
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wy Web</title>
  <link rel="stylesheet" href="Style.css">
</head>

<body>
  <div class="wrapper">
    <form action="Login.php" method="POST">
      <h2>Login Form</h2>
      <?php
      // Mostrar mensaje de error si existe
      if (!empty($mensajeError)) {
        echo "<p style='color: red; text-align: center;'>$mensajeError</p>";
      }
      ?>
      <div class="input-field">
        <input type="text" name="nombre" required>
        <label>Introduce tu usuario</label>
      </div>
      <div class="input-field">
        <input type="password" name="contraseña" required>
        <label>Introduce tu contraseña</label>
      </div>
      <div class="forget">
        <label for="remember">
          <input type="checkbox" id="remember">
          <p>Recordarme</p>
        </label>
      </div>
      <button type="submit">Log In</button>
      <div class="register">
        <p>¿No tienes cuenta? <a href="Registrar.php">Registrar</a></p>
      </div>
    </form>
  </div>
</body>

</html>
