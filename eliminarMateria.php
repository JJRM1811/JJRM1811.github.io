<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: Loggin.php");
    exit();
}

include 'conexion.php';

$usuario = $_SESSION['nombre'];  // nombre del estudiante
$nombreMateria = $_POST['nombreMateria'];  // nombre de la materia a eliminar

$conecta = new Conecta();
$conn = $conecta->conectarDB();

// Iniciar una transacción para asegurarse de que todo se elimine correctamente
$conn->begin_transaction();

try {
    // 1. Eliminar las asignaciones de la materia
    $stmt = $conn->prepare("DELETE a FROM asignaciones a
                            JOIN secciones s ON a.seccionId = s.id
                            JOIN materias m ON s.materiaId = m.id
                            WHERE m.nombreMateria = ? AND m.estudianteNombre = ?");
    $stmt->bind_param("ss", $nombreMateria, $usuario);
    $stmt->execute();
    $stmt->close();

    // 2. Eliminar las secciones asociadas a la materia
    $stmt = $conn->prepare("DELETE FROM secciones WHERE materiaId IN (SELECT id FROM materias WHERE nombreMateria = ?)");
    $stmt->bind_param("s", $nombreMateria);
    $stmt->execute();
    $stmt->close();

    // 3. Eliminar la materia
    $stmt = $conn->prepare("DELETE FROM materias WHERE nombreMateria = ?");
    $stmt->bind_param("s", $nombreMateria);
    $stmt->execute();
    $stmt->close();

    // Confirmar la transacción
    $conn->commit();

    echo "Materia eliminada exitosamente.";

} catch (Exception $e) {
    // Si hay un error, deshacer la transacción
    $conn->rollback();
    echo "Hubo un error al eliminar la materia: " . $e->getMessage();
}

$conn->close();

// Redirigir a la página de las materias
header("Location: main.php");
exit();
?>
