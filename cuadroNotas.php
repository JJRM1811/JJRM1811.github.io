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

// Inicializamos las listas de asignaciones y notas
$secciones = isset($_POST['secciones']) ? $_POST['secciones'] : [['asignacion' => [""], 'notas' => [""]]];

// Procesar el botón de agregar/eliminar secciones
if (isset($_POST['add-section'])) {
    $secciones[] = ['asignacion' => [""], 'notas' => [""]];
}

if (isset($_POST['delete-section'])) {
    array_pop($secciones);
}

// Procesar el botón de agregar/eliminar notas dentro de cada sección
foreach ($secciones as $index => $seccion) {
    if (isset($_POST["add-note-$index"])) {
        $secciones[$index]['asignacion'][] = "";
        $secciones[$index]['notas'][] = "";
    }

    if (isset($_POST["delete-note-$index"])) {
        array_pop($secciones[$index]['asignacion']);
        array_pop($secciones[$index]['notas']);
    }
}

// Procesar al presionar el botón "Enviar"
if (isset($_POST['result'])) {
    $materia = trim($_POST['materia']);

    // Buscar si la materia ya existe para este usuario
    $stmt = $conn->prepare("SELECT id FROM materias WHERE nombreMateria = ? AND estudianteNombre = ?");
    $stmt->bind_param("ss", $materia, $usuario);
    $stmt->execute();
    $stmt->bind_result($materiaId);
    $stmt->fetch();
    $stmt->close();

    if (!$materiaId) {
        // Si la materia no existe, la creamos
        $stmt = $conn->prepare("INSERT INTO materias (nombreMateria, estudianteNombre) VALUES (?, ?)");
        $stmt->bind_param("ss", $materia, $usuario);
        $stmt->execute();
        $materiaId = $stmt->insert_id; // Obtenemos el ID de la nueva materia
    }

    foreach ($_POST['secciones'] as $sectionIndex => $sectionData) {
        $seccion = trim($sectionData['nombreSeccion']);
    
        // Validar si la sección ya existe para esta materia
        $stmt = $conn->prepare("SELECT COUNT(*) FROM secciones WHERE nombreSeccion = ? AND materiaId = ?");
        $stmt->bind_param("si", $seccion, $materiaId);
        $stmt->execute();
        $stmt->bind_result($countSeccion);
        $stmt->fetch();
        $stmt->close();
    
        if ($countSeccion > 0) {
            // Si la sección ya existe, obtenemos su ID
            $stmt = $conn->prepare("SELECT id FROM secciones WHERE nombreSeccion = ? AND materiaId = ?");
            $stmt->bind_param("si", $seccion, $materiaId);
            $stmt->execute();
            $stmt->bind_result($seccionId);
            $stmt->fetch();
            $stmt->close();
        } else {
            // Insertar la nueva sección
            $stmt = $conn->prepare("INSERT INTO secciones (nombreSeccion, materiaId) VALUES (?, ?)");
            $stmt->bind_param("si", $seccion, $materiaId);
            $stmt->execute();
            $seccionId = $stmt->insert_id;
        }
    
        // Insertar asignaciones y notas relacionadas
        foreach ($sectionData['asignacion'] as $index => $asignacion) {
            $nota = $sectionData['notas'][$index];
    
            // Verificar si la asignación ya existe para esta sección
            $stmt = $conn->prepare("SELECT COUNT(*) FROM asignaciones WHERE descripcion = ? AND seccionId = ?");
            $stmt->bind_param("si", $asignacion, $seccionId);
            $stmt->execute();
            $stmt->bind_result($countAsignacion);
            $stmt->fetch();
            $stmt->close();
    
            if ($countAsignacion == 0) {
                // Insertar la nueva asignación solo si no existe
                $stmt = $conn->prepare("INSERT INTO asignaciones (descripcion, nota, seccionId) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $asignacion, $nota, $seccionId);
                $stmt->execute();
            } else {
                echo "La asignación '$asignacion' ya existe para la sección '$seccion'.<br>";
            }
        }
    }

    // Agregar script para enviar mensaje al iframe padre
    echo "<script>
            window.parent.postMessage('closeModal', '*');
          </script>";
    
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notas</title>
    <link rel="stylesheet" href="cuadroDeNotas.css">
</head>
<body>
    <form method="POST">
        <div class="header">
            <input type="text" id="materia" name="materia" placeholder="introduzca su materia aquí" value="<?php echo isset($_POST['materia']) ? htmlspecialchars($_POST['materia']) : ''; ?>" required>
        </div>

        <?php foreach ($secciones as $sectionIndex => $sectionData): ?>
        <hr>
        <div class="subtitu">
            <input  type="text" id="seccion-1" name="secciones[<?php echo $sectionIndex; ?>][nombreSeccion]" placeholder="introduzca su seccion aquí, Ejemplo: Quiz, Tareas, Parciales" value="<?php echo isset($sectionData['nombreSeccion']) ? htmlspecialchars($sectionData['nombreSeccion']) : ''; ?>"   required>
            
            <div class="boton">
                <!-- Botón para agregar una nueva sección -->
                <button type="submit" name="add-section" id="add-section">Agregar Sección</button>

                <!-- Botón para eliminar la última sección -->
                <?php if ($sectionIndex > 0): ?>
                <button type="submit" name="delete-section" id="delete-section">Eliminar Sección</button>
                <?php endif; ?>
            </div>
        </div>
<hr>
        <div class="notas">
            <div class="arriba">
                <div class="izq">
                    <h2>Asignación</h2>
                    <!-- Generamos dinámicamente los campos de asignación -->
                    <?php foreach ($sectionData['asignacion'] as $index => $asignacion): ?>
                    <div class="input">
                        <input type="text" name="secciones[<?php echo $sectionIndex; ?>][asignacion][]" placeholder="introduzca su asignación aquí" value="<?php echo htmlspecialchars($asignacion); ?>" >
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="der">
                    <h2>Nota</h2>
                    <!-- Generamos dinámicamente los campos de nota -->
                    <?php foreach ($sectionData['notas'] as $index => $nota): ?>
                    <div class="input">
                        <input type="text" name="secciones[<?php echo $sectionIndex; ?>][notas][]" placeholder="introduzca su nota aquí" value="<?php echo htmlspecialchars($nota); ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="abajo">
                <div class="boton">
                    <!-- Botón para agregar una nueva asignación -->
                    <button type="submit" name="add-note-<?php echo $sectionIndex; ?>" id="add-note">Agregar Nota</button>
               
                    <!-- Botón para eliminar la última asignación -->
                    <button type="submit" name="delete-note-<?php echo $sectionIndex; ?>" id="delete-note">Eliminar Nota</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>    
<hr>
        <div class="result">
            <!-- Botón para enviar los datos -->
            <button type="submit" name="result" id="env">Enviar</button>
        </div>
        
    </form>
</body>
</html>
