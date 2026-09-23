<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$dbserver = 'localhost';
$dbuser   = 'root';
$dbpass   = '';
$dbname   = 'A Todo Ritmo';

$conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function estaLogueado() {
    return isset($_SESSION['id_usuario']);
}

function esAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';
}