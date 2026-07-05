<?php
$dbserver = 'localhost';
$dbuser   = 'root';
$dbpass   = '';
$dbname   = 'A Todo Ritmo';

$conn = new mysqli($dbserver, $dbuser, $dbpass, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}