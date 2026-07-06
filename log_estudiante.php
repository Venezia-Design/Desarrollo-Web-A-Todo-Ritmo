<?php
$errorEstudiante = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['dni'])) {
    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);

    // Primero busca si es estudiante
    $sql = "SELECT dni_estudiante, nombre, apellido FROM estudiantes WHERE nombre = ? AND dni_estudiante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nombre, $dni);
    $stmt->execute();
    $estudiante = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($estudiante) {
        $_SESSION['dni_estudiante'] = $estudiante['dni_estudiante'];
        $_SESSION['nombre_estudiante'] = $estudiante['nombre'] . ' ' . $estudiante['apellido'];
        header("Location: mi_panel.php");
        exit;
    }

    // Si no es estudiante, busca si es profesor
    $sql = "SELECT dni_profesor, nombre, apellido FROM profesores WHERE nombre = ? AND dni_profesor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nombre, $dni);
    $stmt->execute();
    $profesor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($profesor) {
        $_SESSION['dni_profesor'] = $profesor['dni_profesor'];
        $_SESSION['nombre_profesor'] = $profesor['nombre'] . ' ' . $profesor['apellido'];
        header("Location: panel_profesor.php");
        exit;
    }

    // Si no coincidió con ninguna de las dos tablas
    $errorEstudiante = "Nombre o DNI incorrectos.";
}