<?php
session_start();

if (!isset($_SESSION['nombre'])) {
    header("Location: Loggin.php");
    exit();
}

include 'conexion.php';

$usuario = $_SESSION['nombre'];  // nombre del estudiante
$nombreUsuario = $_POST['nombreUsuario'];  // nombre del usuario a eliminar

// Verificar si se ha recibido el nombre del usuario
if (!$nombreUsuario) {
    echo "Error: No se ha recibido el nombre del usuario.";
    exit();
}

// Crear la conexión con la base de datos
$conecta = new Conecta();
$conn = $conecta->conectarDB();

// Verificar la conexión a la base de datos
if ($conn->connect_error) {
    echo "Error de conexión: " . $conn->connect_error;
    exit();
}

// Iniciar una transacción para asegurarse de que todo se elimine correctamente
$conn->begin_transaction();

try {
    // Eliminar asignaciones relacionadas con el usuario
    $stmt = $conn->prepare("DELETE FROM asignaciones WHERE seccionId IN (SELECT id FROM secciones WHERE materiaId IN (SELECT id FROM materias WHERE estudianteNombre = ?))");
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();

    // Eliminar secciones relacionadas con el usuario
    $stmt = $conn->prepare("DELETE FROM secciones WHERE materiaId IN (SELECT id FROM materias WHERE estudianteNombre = ?)");
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();

    // Eliminar materias relacionadas con el usuario
    $stmt = $conn->prepare("DELETE FROM materias WHERE estudianteNombre = ?");
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();

    // Eliminar el usuario
    $stmt = $conn->prepare("DELETE FROM estudiantes WHERE nombre = ?");
    $stmt->bind_param("s", $nombreUsuario);
    $stmt->execute();

    // Verificar si la eliminación fue exitosa
    if ($stmt->affected_rows > 0) {
        // Confirmar la transacción si la eliminación fue exitosa
        $conn->commit();
        header("Location: main.php?mensaje=Usuario eliminado exitosamente");
    } else {
        throw new Exception("No se encontró el usuario o no se eliminó correctamente.");
    }

    // Cerrar la sentencia preparada
    $stmt->close();
} catch (Exception $e) {
    // Rollback en caso de error
    $conn->rollback();
    header("Location: main.php?error=Hubo un error al eliminar el usuario: " . $e->getMessage());
} finally {
    // Cerrar la conexión a la base de datos
    $conn->close();
}
?>
