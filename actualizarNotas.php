<?php
// Incluir archivo de conexión a la base de datos
session_start();

include 'conexion.php';

if (!isset($_SESSION['nombre'])) {
    // Redirigir si no hay sesión activa
    header("Location: Loggin.php");
    exit();
}

$usuario = $_SESSION['nombre'];
$conecta = new Conecta();
$conn = $conecta->conectarDB();

// Inicializamos las variables necesarias
$materiaSeleccionada = isset($_POST['materias']) ? $_POST['materias'] : "";
$seccionSeleccionada = isset($_POST['seccionId']) ? $_POST['seccionId'] : "";
$asignacionSeleccionada = isset($_POST['asignacionId']) ? $_POST['asignacionId'] : "";

// Listado de materias
$stmt = $conn->prepare("SELECT nombreMateria FROM materias WHERE estudianteNombre = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$materias = [];
while ($row = $result->fetch_assoc()) {
    $materias[] = $row['nombreMateria'];
}
$stmt->close();

// Listado de secciones y asignaciones (si hay materia seleccionada)
$secciones = [];
$asignaciones = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar sección
    if (isset($_POST['eliminarSeccion']) && isset($_POST['seccionId'])) {
        $seccionId = intval($_POST['seccionId']);

        // Verificar si tiene asignaciones asociadas
        $stmt = $conn->prepare("SELECT COUNT(*) FROM asignaciones WHERE seccionId = ?");
        $stmt->bind_param("i", $seccionId);
        $stmt->execute();
        $stmt->bind_result($countAsignaciones);
        $stmt->fetch();
        $stmt->close();

        if ($countAsignaciones > 0) {
            echo "<script>alert('No se puede eliminar la sección porque tiene asignaciones asociadas.');</script>";
        } else {
            $stmt = $conn->prepare("DELETE FROM secciones WHERE id = ?");
            $stmt->bind_param("i", $seccionId);
            $stmt->execute();
            echo "<script>alert('Sección eliminada correctamente.');</script>";
        }
    }

    // Eliminar asignación
    if (isset($_POST['eliminarAsignacion']) && isset($_POST['asignacionId'])) {
        $asignacionId = intval($_POST['asignacionId']);
        $stmt = $conn->prepare("DELETE FROM asignaciones WHERE id = ?");
        $stmt->bind_param("i", $asignacionId);
        $stmt->execute();
        echo "<script>alert('Asignación eliminada correctamente.');</script>";
    }

    // Actualizar asignaciones o secciones
    if (isset($_POST['update'])) {
        $asignacionNueva = trim($_POST['asignacionNueva']);
        $notaNueva = trim($_POST['notaNueva']);
        $seccionNueva = trim($_POST['seccionNueva']);

        if ($asignacionSeleccionada && $asignacionNueva !== "" && $notaNueva !== "") {
            $stmt = $conn->prepare("UPDATE asignaciones SET descripcion = ?, nota = ? WHERE id = ?");
            $stmt->bind_param("sdi", $asignacionNueva, $notaNueva, $asignacionSeleccionada);
            $stmt->execute();
            echo "<script>alert('Asignación y nota actualizadas correctamente.');</script>";
        }

        if ($seccionSeleccionada && $seccionNueva !== "") {
            $stmt = $conn->prepare("UPDATE secciones SET nombreSeccion = ? WHERE id = ?");
            $stmt->bind_param("si", $seccionNueva, $seccionSeleccionada);
            $stmt->execute();
            echo "<script>alert('Sección actualizada correctamente.');</script>";
        }
    }
}

if ($materiaSeleccionada) {
    $stmt = $conn->prepare("SELECT id FROM materias WHERE nombreMateria = ? AND estudianteNombre = ?");
    $stmt->bind_param("ss", $materiaSeleccionada, $usuario);
    $stmt->execute();
    $stmt->bind_result($materiaId);
    $stmt->fetch();
    $stmt->close();

    if ($materiaId) {
        $stmt = $conn->prepare("SELECT id, nombreSeccion FROM secciones WHERE materiaId = ?");
        $stmt->bind_param("i", $materiaId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $secciones[] = $row;
        }
        $stmt->close();
    }
}

if ($seccionSeleccionada) {
    $stmt = $conn->prepare("SELECT id, descripcion FROM asignaciones WHERE seccionId = ?");
    $stmt->bind_param("i", $seccionSeleccionada);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $asignaciones[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Notas</title>
    <link rel="stylesheet" href="actualizar.css">
</head>
<body>
    <form method="POST">
        <label for="materias">Selecciona una materia:</label>
        <select name="materias" id="materias" onchange="this.form.submit()">
            <option value="">Seleccione una materia</option>
            <?php foreach ($materias as $materia): ?>
                <option value="<?php echo htmlspecialchars($materia); ?>" <?php echo ($materiaSeleccionada == $materia) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($materia); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($materiaSeleccionada): ?>
            <label for="seccionId">Selecciona una sección:</label>
            <select name="seccionId" id="seccionId" onchange="this.form.submit()">
                <option value="">Seleccione una sección</option>
                <?php foreach ($secciones as $seccion): ?>
                    <option value="<?php echo $seccion['id']; ?>" <?php echo ($seccionSeleccionada == $seccion['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($seccion['nombreSeccion']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="eliminarSeccion">Eliminar Sección</button>
        <?php endif; ?>

        <?php if ($seccionSeleccionada): ?>
            <label for="seccionNueva">Nuevo nombre de la sección:</label>
            <input type="text" name="seccionNueva" id="seccionNueva">

            <label for="asignacionId">Selecciona una asignación:</label>
            <select name="asignacionId" id="asignacionId" onchange="this.form.submit()">
                <option value="">Seleccione una asignación</option>
                <?php foreach ($asignaciones as $asignacion): ?>
                    <option value="<?php echo $asignacion['id']; ?>" <?php echo ($asignacionSeleccionada == $asignacion['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($asignacion['descripcion']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="eliminarAsignacion">Eliminar Asignación</button>
        <?php endif; ?>

        <?php if ($asignacionSeleccionada): ?>
            <label for="asignacionNueva">Nuevo nombre de la asignación:</label>
            <input type="text" name="asignacionNueva" id="asignacionNueva" required>

            <label for="notaNueva">Nueva nota:</label>
            <input type="text" name="notaNueva" id="notaNueva" required>
        <?php endif; ?>

        <button type="submit" name="update">Actualizar</button>
    </form>
</body>
</html>
