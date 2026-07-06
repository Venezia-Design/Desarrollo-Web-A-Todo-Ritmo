<?php
$errorEstudiante = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'], $_POST['dni'])) {
    $nombre = trim($_POST['nombre']);
    $dni = trim($_POST['dni']);

    $sql = "SELECT dni_estudiante, nombre, apellido FROM estudiantes WHERE nombre = ? AND dni_estudiante = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $nombre, $dni);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $estudiante = $resultado->fetch_assoc();
    $stmt->close();

    if ($estudiante) {
        $_SESSION['dni_estudiante'] = $estudiante['dni_estudiante'];
        $_SESSION['nombre_estudiante'] = $estudiante['nombre'] . ' ' . $estudiante['apellido'];

        header("Location: mi_panel.php");
        exit;
    } else {
        $errorEstudiante = "Nombre o DNI incorrectos.";
    }
}