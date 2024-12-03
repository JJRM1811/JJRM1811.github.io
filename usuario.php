<?php

if (!isset($_SESSION['nombre'])) {
    header("Location: Loggin.php");
    exit();
}

include 'conexion.php';

$usuario = $_SESSION['nombre'];
$conecta = new Conecta();
$conn = $conecta->conectarDB();

$datosUsuario = [];

// Manejo de actualización de datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoNombre = $_POST['nombreReal'];
    $apellido = $_POST['apellido'];
    $correo = $_POST['correo'];
    $fechaNacimiento = $_POST['nacimiento'];
    $edad = $_POST['edad'];
    $carrera = $_POST['carrera'];

    $stmt = $conn->prepare(
        "UPDATE estudiantes SET nombreReal = ?, apellido = ?, correo = ?, carrera = ?, nacimiento = ?, edad = ? WHERE nombre = ?"
    );
    $stmt->bind_param("sssssss", $nuevoNombre, $apellido, $correo, $carrera, $fechaNacimiento, $edad, $usuario);
    $stmt->execute();

    echo "<script>alert('Información actualizada correctamente.');</script>";
    header("Refresh:0"); // Refrescar para mostrar los datos actualizados
    exit();
}

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT nombre, nombreReal, apellido, cedula, carrera, correo, nacimiento, edad FROM estudiantes WHERE nombre = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $datosUsuario = $resultado->fetch_assoc();
} else {
    echo "Error: Usuario no encontrado.";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="usuarios.css">
</head>
<body>

    <div class="container">
        <h1>Perfil de Usuario</h1>
        <div id="viewMode">
            <p><strong>Nombre de Usuario:</strong> <?php echo htmlspecialchars($datosUsuario['nombre']); ?></p>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($datosUsuario['nombreReal']); ?></p>
            <p><strong>Apellido:</strong> <?php echo htmlspecialchars($datosUsuario['apellido']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($datosUsuario['correo']); ?></p>
            <p><strong>Carrera:</strong> <?php echo htmlspecialchars($datosUsuario['carrera']); ?></p>
            <p><strong>Cédula:</strong> <?php echo htmlspecialchars($datosUsuario['cedula']); ?></p>
            <p><strong>Fecha de Nacimiento:</strong> <?php echo htmlspecialchars($datosUsuario['nacimiento']); ?></p>
            <p><strong>Edad:</strong> <?php echo htmlspecialchars($datosUsuario['edad']); ?></p>
            <button onclick="toggleForm()">Actualizar Información</button>
        </div>

        <div id="editMode" class="hidden">
            <form method="POST">
                <div>
                    <label for="nombreReal">Nombre:</label>
                    <input type="text" id="nombreReal" name="nombreReal" value="<?php echo htmlspecialchars($datosUsuario['nombreReal']); ?>" required>
                </div>
                <div>
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($datosUsuario['apellido']); ?>" required>
                </div>
                <div>
                    <label for="correo">Correo:</label>
                    <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($datosUsuario['correo']); ?>" required>
                </div>
                <div>
                    <label for="nacimiento">Fecha de Nacimiento:</label>
                    <input type="date" id="nacimiento" name="nacimiento" value="<?php echo htmlspecialchars($datosUsuario['nacimiento']); ?>">
                </div>
                <div>
                    <label for="edad">Edad:</label>
                    <input type="number" id="edad" name="edad" value="<?php echo htmlspecialchars($datosUsuario['edad']); ?>" min="0">
                </div>
                <div>
                    <label for="carrera">Carrera:</label>
                    <input type="text" id="carrera" name="carrera" value="<?php echo htmlspecialchars($datosUsuario['carrera']); ?>" required>
                </div>
                <button type="submit">Guardar Cambios</button>
                <button type="button" onclick="toggleForm()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function toggleForm() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            viewMode.classList.toggle('hidden');
            editMode.classList.toggle('hidden');
        }
    </script>
</body>
</html>
