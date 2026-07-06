<?php
include 'conn.php';

if (!isset($_SESSION['dni_profesor'])) {
    header("Location: login_estudiante.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['biografia'])) {
    $bio = $_POST['biografia'];
    $dni = $_SESSION['dni_profesor']; // usa el DNI de la sesión, no uno que venga del formulario

    $stmt = $conn->prepare("UPDATE profesores SET Biografia = ? WHERE dni_profesor = ?");
    $stmt->bind_param("si", $bio, $dni);
    $stmt->execute();
    $stmt->close();
}

header("Location: panel_profesor.php?guardado=1");
exit;