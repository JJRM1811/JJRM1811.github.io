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

$stmt = $conn->prepare(
    "SELECT m.nombreMateria, s.nombreSeccion, a.descripcion, a.nota
    FROM asignaciones a
    JOIN secciones s ON a.seccionId = s.id
    JOIN materias m ON s.materiaId = m.id
    WHERE m.estudianteNombre = ?"
);

if ($stmt === false) {
    die('Error al preparar la consulta: ' . $conn->error);
}

$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado === false) {
    die('Error al ejecutar la consulta: ' . $conn->error);
}

$materiasAsignadas = [];
while ($row = $resultado->fetch_assoc()) {
    $materiasAsignadas[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="mostrarNotas.css">
    <title>Mis Materias y Notas</title>
</head>
<body>
<div class="container">
        <h1>Mostrar Notas</h1>
        <p>Aquí puedes ver y agregar tus notas.</p>

        <!-- Botón para abrir el modal -->
        <button id="openModal">Agregar Notas</button>

        <!-- Modal -->
        <div class="modal" id="modal">
            <div class="modal-content">
                <button id="closeModal">Cerrar</button>
                <iframe src="cuadroNotas.php"></iframe>
            </div>
        </div>
    </div>

        <!-- Botón para abrir el modal de actualización -->
        <button id="updateModalBtn">Actualizar Notas</button>

        <!-- Modal de actualización -->
        <div class="modal" id="updateModal">
            <div class="modal-content">
                <button id="closeUpdateModal">Cerrar</button>
                <iframe src="actualizarNotas.php"></iframe>
            </div>
        </div>

        <script>
            // Obtener elementos del DOM para agregar notas
            const openModalButton = document.getElementById('openModal');
            const closeModalButton = document.getElementById('closeModal');
            const modal = document.getElementById('modal');

            // Abrir el modal de agregar notas
            openModalButton.onclick = function () {
                modal.style.display = 'flex';
            };

            // Cerrar el modal de agregar notas
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
                    // Cierra el modal de agregar notas
                    modal.style.display = 'none';
                    // Recarga la página para reflejar los cambios
                    location.reload();
                }
            });
        </script>
        
    <div class="container">
        <?php if (empty($materiasAsignadas)): ?>
            <p>No hay materias asignadas disponibles.</p>
        <?php else: ?>
            <?php 
            // Agrupar las asignaciones por materia
            $materiasAgrupadas = [];
            foreach ($materiasAsignadas as $materia) {
                $materiasAgrupadas[$materia['nombreMateria']][] = $materia;
            }

            // Mostrar las materias con sus asignaciones
            foreach ($materiasAgrupadas as $nombreMateria => $asignaciones): ?>
                <div class="perfil-caja">
                    <h2><?php echo htmlspecialchars($nombreMateria); ?></h2>
                    
                    <?php 
                    // Variable para almacenar las secciones ya mostradas
                    $seccionesMostradas = [];
                    $primeraAsignacion = true; // Flag para saber si es la primera asignación
                    foreach ($asignaciones as $asignacion): 
                        // Verificar si la sección ya ha sido mostrada
                        if (!in_array($asignacion['nombreSeccion'], $seccionesMostradas)): 
                            // Mostrar la sección solo la primera vez
                            $seccionesMostradas[] = $asignacion['nombreSeccion']; 
                            ?>
                            <h3><?php echo htmlspecialchars($asignacion['nombreSeccion']); ?></h3>
                        <?php endif; ?>

                        <!-- Mostrar solo la palabra "Asignación" una vez -->
                        <?php if ($primeraAsignacion): ?>
                            <div class="asignacion">
                                <div class="descrip">
                                    <p><strong>Asignación:</strong> </p>
                                    <p><strong>Nota:</strong> </p>
                                </div>
                            </div>
                            <?php $primeraAsignacion = false; ?>
                        <?php endif; ?>

                        <div class="asignacion">
                            <div class="descrip">
                                <p><strong></strong> <?php echo htmlspecialchars($asignacion['descripcion']); ?></p>
                                <p><strong></strong> <?php echo htmlspecialchars($asignacion['nota']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    
                    <!-- Botón de eliminación -->
                    <form method="post" action="eliminarMateria.php" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta materia?');">
                        <input type="hidden" name="nombreMateria" value="<?php echo htmlspecialchars($nombreMateria); ?>">
                        <button type="submit" style="background-color: red;">Eliminar Materia</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
