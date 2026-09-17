<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: home.php");
    exit;
}

$nombre = trim($_POST["nombre"] ?? '');
$dni = trim($_POST["dni"] ?? '');
$instrumento = $_POST["instrumento"] ?? '';
$whatsapp = trim($_POST["whatsapp"] ?? '');

// Validación campo por campo
$errores = [];

if (empty($nombre)) {
    $errores['nombre'] = 'Escribí tu nombre y apellido completos.';
}
if (empty($dni)) {
    $errores['dni'] = 'Ingresá tu número de DNI, sin puntos.';
}

if (empty($whatsapp)) {
    $errores['whatsapp'] = 'Ingresa tu número de telefono sin guiones.';
}

if (!empty($errores)) {
    $_SESSION['errores_form'] = $errores;
    $_SESSION['datos_form'] = [
        'nombre' => $nombre,
        'dni' => $dni,
        'instrumento' => $instrumento,
        'whatsapp' => $whatsapp,
    ];
    header("Location: home.php#anotarme");
    exit;
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