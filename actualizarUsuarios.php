<?php
// Incluir archivo de conexión a la base de datos
session_start();

include 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    // Redirigir si no hay sesión activa
    header("Location: Loggin.php");
    exit();
}

$conecta = new Conecta();
$conn = $conecta->conectarDB();

// Inicializamos la variable $usuarioSeleccionado
$usuarioSeleccionado = isset($_POST['usuario']) ? $_POST['usuario'] : "";

// Obtener la lista de usuarios para seleccionar
$stmt = $conn->prepare("SELECT nombre FROM estudiantes");
$stmt->execute();
$result = $stmt->get_result();
$usuarios = [];
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row['nombre'];
}
$stmt->close();

// Obtener los datos del usuario seleccionado
$userData = null;
if ($usuarioSeleccionado) {
    $stmt = $conn->prepare("SELECT * FROM estudiantes WHERE nombre = ?");
    $stmt->bind_param("s", $usuarioSeleccionado);
    $stmt->execute();
    $result = $stmt->get_result();
    $userData = $result->fetch_assoc();
    $stmt->close();
}

// Procesar la actualización
if (isset($_POST['update']) && $usuarioSeleccionado) {
    // Actualizar los datos del usuario
    $nombreReal = isset($_POST['nombreReal']) ? $_POST['nombreReal'] : "";
    $apellido = isset($_POST['apellido']) ? $_POST['apellido'] : "";
    $correo = isset($_POST['correo']) ? $_POST['correo'] : "";
    $carrera = isset($_POST['carrera']) ? $_POST['carrera'] : "";
    $nacimiento = isset($_POST['nacimiento']) ? $_POST['nacimiento'] : "";

    if ($nombreReal !== "" && $apellido !== "" && $correo !== "" && $carrera !== "" && $nacimiento !== "") {
        $stmt = $conn->prepare("UPDATE estudiantes SET nombreReal = ?, apellido = ?, correo = ?, carrera = ?, nacimiento = ? WHERE nombre = ?");
        $stmt->bind_param("ssssss", $nombreReal, $apellido, $correo, $carrera, $nacimiento, $usuarioSeleccionado);
        if ($stmt->execute()) {
            echo "<script>
                alert('Datos del usuario actualizados correctamente.');
                window.location.href = 'actualizarUsuarios.php';
            </script>";
        } else {
            echo "<script>alert('Error al actualizar los datos del usuario.');</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Usuario</title>
    <link rel="stylesheet" href="actualizar.css">
</head>
<body>
    <form method="POST">
        <!-- Selección de Usuario -->
        <label for="usuario">Selecciona un usuario:</label>
        <select name="usuario" id="usuario" onchange="this.form.submit()">
            <option value="">Seleccione un usuario</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?php echo htmlspecialchars($usuario); ?>" <?php echo ($usuarioSeleccionado == $usuario) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($usuario); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($usuarioSeleccionado && $userData): ?>
            <!-- Mostrar los datos actuales del usuario -->
            <label for="nombreReal">Nombre Real:</label>
            <input type="text" name="nombreReal" id="nombreReal" value="<?php echo htmlspecialchars($userData['nombreReal']); ?>" required>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" id="apellido" value="<?php echo htmlspecialchars($userData['apellido']); ?>" required>

            <label for="correo">Correo:</label>
            <input type="email" name="correo" id="correo" value="<?php echo htmlspecialchars($userData['correo']); ?>" required>

            <label for="carrera">Carrera:</label>
            <input type="text" name="carrera" id="carrera" value="<?php echo htmlspecialchars($userData['carrera']); ?>" required>

            <label for="nacimiento">Fecha de Nacimiento:</label>
            <input type="date" name="nacimiento" id="nacimiento" value="<?php echo htmlspecialchars($userData['nacimiento']); ?>" required>

            <button type="submit" name="update">Actualizar</button>
        <?php endif; ?>
    </form>
</body>
</html>
