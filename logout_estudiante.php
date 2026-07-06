<?php
session_start();
unset($_SESSION['dni_estudiante']);
unset($_SESSION['nombre_estudiante']);
unset($_SESSION['dni_profesor']);
unset($_SESSION['nombre_profesor']);
header("Location: home.php");
exit;