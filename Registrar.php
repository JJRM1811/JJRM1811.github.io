<?php
session_start(); // Manejo de errores y datos de entrada
$mensajeError = isset($_SESSION['mensajeError']) ? $_SESSION['mensajeError'] : '';
unset($_SESSION['mensajeError']); // Limpiar el mensaje después de mostrarlo
?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wy Web</title>
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div class="wrapper">
    <form action="Insertar_Datos.php" method="POST">
      <h2>Registrar</h2>
      
      <?php
      // Mostrar mensaje de error si existe
      if (!empty($mensajeError)) {
        echo "<p style='color: red; text-align: center;'>$mensajeError</p>";
      }
      ?>
          
      <div class="input-field">
        <input type="text" name="nombre" required>
        <label>Introduce tu nombre de usuario</label>
      </div>

      <div class="input-field">
        <input type="password" name="contraseña" required>
        <label>Introduce tu contraseña</label>
      </div>

      <div class="input-field">
        <input type="email" name="correo" required>
        <label>Introduce tu correo</label>
      </div>
    
        
      <div class="input-field">
        <input type="text" name="cedula" required>
        <label>Introduce tu cédula</label>
      </div>

      <div class="input-field">
        <input type="text" name="carrera" required>
        <label>Introduce tu carrera</label>
      </div>
        
      <button type="submit">Registrar</button>
    </form>
  </div>
</body>

</html>
