<?php
session_start(); // Manejo de errores y datos de entrada
include 'conexion.php';

try {
    $conexion = new Conecta();
    $cnn = $conexion->conectarDB();

    // Capturar los datos del formulario
    $correo = $_POST["correo"] ?? '';
    $nombre = $_POST["nombre"] ?? '';
    $contraseña = password_hash($_POST["contraseña"] ?? '', PASSWORD_DEFAULT);
    $cedula = $_POST["cedula"] ?? '';
    $carrera = $_POST["carrera"] ?? '';

    // Validar que todos los campos estén completos
    if (!empty($correo) && !empty($nombre) &&  !empty($contraseña) && !empty($cedula) && !empty($carrera)) {
        
        // Verificar si el usuario ya existe
        $sqlCorreo = "SELECT nombre FROM estudiantes WHERE nombre = ?";
        $stmtCorreo = $cnn->prepare($sqlCorreo);
        $stmtCorreo->bind_param("s", $nombre);
        $stmtCorreo->execute();
        $stmtCorreo->store_result();

        if ($stmtCorreo->num_rows > 0) {
            $_SESSION['mensajeError'] = "El usuario ya está registrado.";
            throw new Exception();
        }

        $stmtCorreo->close();

        // Verificar si la cédula ya existe
        $sqlCedula = "SELECT cedula FROM estudiantes WHERE cedula = ?";
        $stmtCedula = $cnn->prepare($sqlCedula);
        $stmtCedula->bind_param("s", $cedula);
        $stmtCedula->execute();
        $stmtCedula->store_result();

        if ($stmtCedula->num_rows > 0) {
            $_SESSION['mensajeError'] = "La cédula ya está registrada.";
            throw new Exception();
        }

        $stmtCedula->close();

        // Insertar los datos si no existen conflictos
        $sqlInsert = "INSERT INTO estudiantes (correo, nombre, contraseña, cedula, carrera) VALUES (?, ?, ?, ?, ?)";
        $stmtInsert = $cnn->prepare($sqlInsert);
        $stmtInsert->bind_param("sssss", $correo, $nombre, $contraseña, $cedula, $carrera);

        if ($stmtInsert->execute()) {
            header("Location: Loggin.php");
            exit();
        } else {
            $_SESSION['mensajeError'] = "Error al registrar los datos: " . htmlspecialchars($stmtInsert->error);
        }

        $stmtInsert->close();
    } else {
        $_SESSION['mensajeError'] = "Por favor, complete todos los campos.";
    }

    // Guardar los datos ingresados en sesión para mostrarlos nuevamente
    $_SESSION['correo'] = $correo;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['cedula'] = $cedula;
    $_SESSION['carrera'] = $carrera;

    // Redirigir al formulario con errores
    header("Location: Registrar.php");
    exit();
} catch (Exception $e) {
    // Redirigir al formulario si ocurre una excepción
    header("Location: Registrar.php");
    exit();
} finally {
    $conexion->cerrar();
}
