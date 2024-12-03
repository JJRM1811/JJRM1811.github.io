<?php
session_start(); // Iniciar sesión para almacenar mensajes de error
include 'conexion.php';

try {
    // Conexión a la base de datos
    $conexion = new Conecta();
    $cnn = $conexion->conectarDB();

    // Capturar los datos del formulario
    $nombre = $_POST["nombre"] ?? '';
    $contraseña = $_POST["contraseña"] ?? '';

    // Validar que ambos campos estén llenos
    if (!empty($nombre) && !empty($contraseña)) {
        // Consulta para buscar al usuario por nombre
        $sql = "SELECT contraseña FROM estudiantes WHERE nombre = ?";
        $stmt = $cnn->prepare($sql);

        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . $cnn->error);
        }

        $stmt->bind_param("s", $nombre);
        $stmt->execute();
        $stmt->store_result();

        // Verificar si el nombre existe en la base de datos
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashedPassword);
            $stmt->fetch();

            // Verificar la contraseña
            if (password_verify($contraseña, $hashedPassword)) {
                $_SESSION['nombre'] = $nombre;
                // Redirección en caso de éxito
                header("Location: main.php");
                exit();
            } else {
                $_SESSION['mensajeError'] = "Contraseña incorrecta.";
            }
        } else {
            $_SESSION['mensajeError'] = "No existe una cuenta asociada a este nombre.";
        }

        $stmt->close();
    } else {
        $_SESSION['mensajeError'] = "Por favor, complete todos los campos.";
    }

    // Guardar el nombre ingresado en sesión para rellenar automáticamente el formulario
    $_SESSION['nombre'] = $nombre;

    // Redirigir de vuelta al formulario
    header("Location: loggin.php");
    exit();
} catch (Exception $e) {
    $_SESSION['mensajeError'] = "Ocurrió un error: " . htmlspecialchars($e->getMessage());
    header("Location: loggin.php");
    exit();
}
?>
