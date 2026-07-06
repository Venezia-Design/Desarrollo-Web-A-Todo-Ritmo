<?php
include 'conn.php';
 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location: home.php");
    exit;
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'profesor') {
    $bio = $_POST['biografia'];
    $dni = $_POST['dni_profesor'];
 
    $stmt = $conn->prepare("UPDATE profesores SET Biografia = ? WHERE dni_profesor = ?");
    $stmt->bind_param("si", $bio, $dni);
    $stmt->execute();
    $stmt->close();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'taller') {
    $inicio = $_POST['hora_de_inicio'];
    $fin = $_POST['hora_de_finalizacion'];
    $nro = $_POST['nro_comision'];
 
    $stmt = $conn->prepare("UPDATE talleres SET hora_de_inicio = ?, hora_de_finalizacion = ? WHERE nro_comision = ?");
    $stmt->bind_param("ssi", $inicio, $fin, $nro);
    $stmt->execute();
    $stmt->close();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'presentacion') {
    $dia = $_POST['dia'];
    $lugar = $_POST['lugar'];
    $id = $_POST['id_presentacion'];
 
    $stmt = $conn->prepare("UPDATE presentacion SET `día` = ?, lugar = ? WHERE id_presentacion = ?");
    $stmt->bind_param("ssi", $dia, $lugar, $id);
    $stmt->execute();
    $stmt->close();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'presentacion_nueva') {
    $dia = $_POST['dia'];
    $lugar = $_POST['lugar'];
 
    $stmt = $conn->prepare("INSERT INTO presentacion (`día`, lugar) VALUES (?, ?)");
    $stmt->bind_param("ss", $dia, $lugar);
    $stmt->execute();
    $stmt->close();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'taller_nuevo') {
    $nombre = $_POST['nombre_taller'];
    $dia = $_POST['dia_de_la_semana'];
    $inicio = $_POST['hora_de_inicio'];
    $fin = $_POST['hora_de_finalizacion'];
    $dniProfesor = $_POST['dni_profesor'];

    $stmt = $conn->prepare("INSERT INTO talleres (nombre_taller, dia_de_la_semana, hora_de_inicio, hora_de_finalizacion, dni_profesor) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $nombre, $dia, $inicio, $fin, $dniProfesor);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'profesor_nuevo') {
    $dni = $_POST['dni_profesor'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $bio = $_POST['biografia'];
    $idInstrumento = $_POST['id_instrumentos'];

    $stmt = $conn->prepare("INSERT INTO profesores (dni_profesor, nombre, apellido, Biografia) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $dni, $nombre, $apellido, $bio);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO especializar (dni_profesor, id_instrumentos) VALUES (?, ?)");
    $stmt->bind_param("ii", $dni, $idInstrumento);
    $stmt->execute();
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['tipo'] === 'contactado') {

    $id = $_POST['id_interesado'];

    $stmt = $conn->prepare("
        UPDATE registros
        SET contactado = 1
        WHERE id_interesado = ?
    ");

    $stmt->bind_param("i", $id);

    $stmt->execute();

    $stmt->close();
}

// Redirigir a la sección correspondiente según la acción
$anclas = [
    'profesor'           => '#profesores',
    'profesor_nuevo'     => '#profesores',
    'taller'             => '#talleres',
    'taller_nuevo'       => '#talleres',
    'presentacion'       => '#muestras',
    'presentacion_nueva' => '#muestras',
    'contactado'         => '#registros',
];

$tipo   = $_POST['tipo'] ?? '';
$ancla  = $anclas[$tipo] ?? '';

header("Location: home.php" . $ancla);
exit;