<?php
session_start();
unset($_SESSION['dni_estudiante']);
unset($_SESSION['nombre_estudiante']);
header("Location: home.php");
exit;