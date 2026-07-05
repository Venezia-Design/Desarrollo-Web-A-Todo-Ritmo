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

header("Location: home.php");
exit;