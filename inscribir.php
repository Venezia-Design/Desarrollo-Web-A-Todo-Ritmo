<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: home.php");
    exit;
}

$nombre = trim($_POST["nombre"]);
$dni = trim($_POST["dni"]);
$instrumento = $_POST["instrumento"];
$whatsapp = trim($_POST["whatsapp"]);

// Validación básica
if (empty($nombre) || empty($dni) || empty($instrumento)) {
    die("Faltan datos obligatorios.");
}

// Verificar si ya existe un registro pendiente con ese DNI
$stmt = $conn->prepare("SELECT id_interesado FROM registros WHERE dni = ? AND contactado = 0");
$stmt->bind_param("s", $dni);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    header("Location: home.php?ok=2#anotarme");
    exit;
}

$stmt->close();

// Insertar registro
$stmt = $conn->prepare("
    INSERT INTO registros
    (nombre, dni, whatsapp, id_instrumentos)
    VALUES (?, ?, ?, ?)
");

$stmt->bind_param(
    "sssi",
    $nombre,
    $dni,
    $whatsapp,
    $instrumento
);

if ($stmt->execute()) {
    header("Location: home.php?ok=1#anotarme");
} else {
    echo "Error al guardar: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>