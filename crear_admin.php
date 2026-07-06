<?php
include 'conn.php';

$username = 'Admin';
$email = 'admin@atodoritmo.com';
$passwordPlano = 'admin123'; // esta va a ser tu contraseña para loguearte
$passwordHasheada = password_hash($passwordPlano, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (username, email, password, rol) VALUES (?, ?, ?, 'administrador')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $username, $email, $passwordHasheada);
$stmt->execute();

echo "Usuario admin creado con éxito.";