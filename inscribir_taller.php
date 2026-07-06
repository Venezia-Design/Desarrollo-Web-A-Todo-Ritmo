<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn.php';

if (!isset($_SESSION['dni_estudiante'])) {
    header("Location: login_estudiante.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nro_comision'])) {
    $dni = $_SESSION['dni_estudiante'];
    $nroComision = $_POST['nro_comision'];
    $fechaHoy = date('Y-m-d');

    $sql = "INSERT INTO asistir (dni_estudiante, nro_comision, `fecha _de_inscripcion`) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $dni, $nroComision, $fechaHoy);
    $stmt->execute();
    $stmt->close();
}

header("Location: mi_panel.php?inscripto=1");
exit;