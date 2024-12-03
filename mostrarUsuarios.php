<?php
// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['nombre'])) {
    header("Location: Loggin.php");
    exit();
}

include 'conexion.php';

$usuario = $_SESSION['nombre'];
$conecta = new Conecta();
$conn = $conecta->conectarDB();

$datosUsuarios = [];

// Obtener datos de todos los usuarios
$stmt = $conn->prepare("SELECT nombre, contraseña, nombreReal, apellido, cedula, carrera, correo, nacimiento, edad FROM estudiantes");
$stmt->execute();
$resultado = $stmt->get_result();

while ($row = $resultado->fetch_assoc()) {
    $datosUsuarios[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Mostrar.css">
    <title>Usuarios disponibles</title>
</head>
<body>
    <div class="container">
        <h1>Usuarios disponibles</h1>
        <p class="ptitulo">Aquí puedes ver, editar y eliminar usuarios.</p>

        <!-- Botón para abrir el modal -->
        <button id="openModal">Agregar Usuarios</button>

        <!-- Modal -->
        <div class="modal" id="modal">
            <div class="modal-content">
                <button id="closeModal">Cerrar</button>
                <iframe src="Registrar.php"></iframe>
            </div>
        </div>

        <!-- Botón para abrir el modal de actualización -->
        <button id="updateModalBtn" class="izqboton">Editar Usuarios</button>

        <!-- Modal de actualización -->
        <div class="modal" id="updateModal">
            <div class="modal-content">
                <button id="closeUpdateModal">Cerrar</button>
                <iframe src="actualizarUsuarios.php"></iframe>
            </div>
        </div>

        <script>
            // Obtener elementos del DOM para agregar usuarios
            const openModalButton = document.getElementById('openModal');
            const closeModalButton = document.getElementById('closeModal');
            const modal = document.getElementById('modal');

            // Abrir el modal de agregar usuarios
            openModalButton.onclick = function () {
                modal.style.display = 'flex';
            };

            // Cerrar el modal de agregar usuarios
            closeModalButton.onclick = function () {
                modal.style.display = 'none';
            };

            // Cerrar el modal al hacer clic fuera del contenido
            window.onclick = function (event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
                if (event.target === updateModal) {
                    updateModal.style.display = 'none';
                }
            };

            // Obtener elementos del DOM para actualizar información
            const updateModalButton = document.getElementById('updateModalBtn');
            const closeUpdateModalButton = document.getElementById('closeUpdateModal');
            const updateModal = document.getElementById('updateModal');

            // Abrir el modal de actualización
            updateModalButton.onclick = function () {
                updateModal.style.display = 'flex';
            };

            // Cerrar el modal de actualización
            closeUpdateModalButton.onclick = function () {
                updateModal.style.display = 'none';
            };

            // Escuchar el mensaje enviado desde el iframe
            window.addEventListener('message', function (event) {
                if (event.data === 'closeModal') {
                    // Cerrar el modal de agregar usuarios
                    modal.style.display = 'none';
                    // Recargar la página para reflejar los cambios
                    location.reload();
                }
            });
        </script>
        
        <div class="usuarios-container">
            <?php if (empty($datosUsuarios)): ?>
                <p>No hay usuarios registrados disponibles.</p>
            <?php else: ?>
                <?php foreach ($datosUsuarios as $usuario): ?>
                    <div class="perfil-caja">
                        <h2><?php echo htmlspecialchars($usuario['nombre']); ?></h2>
                        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombreReal']); ?></p>
                        <p><strong>Apellido:</strong> <?php echo htmlspecialchars($usuario['apellido']); ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo']); ?></p>
                        <p><strong>Carrera:</strong> <?php echo htmlspecialchars($usuario['carrera']); ?></p>
                        <p><strong>Cédula:</strong> <?php echo htmlspecialchars($usuario['cedula']); ?></p>
                        <p><strong>Fecha de Nacimiento:</strong> <?php echo htmlspecialchars($usuario['nacimiento']); ?></p>
                        <p><strong>Edad:</strong> <?php echo htmlspecialchars($usuario['edad']); ?></p>
                        <!-- Botón de eliminación -->
                        <form method="post" action="eliminarUsuario.php" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este Usuario?');">
                            <input type="hidden" name="nombreUsuario" value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                            <button type="submit" style="background-color: red;">Eliminar Usuario</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
