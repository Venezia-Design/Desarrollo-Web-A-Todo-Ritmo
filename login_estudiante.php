<?php
include 'conn.php';
include 'log_estudiante.php';

if (isset($_SESSION['dni_estudiante'])) {
    header("Location: mi_panel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Todo Ritmo — Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/lqo1wek.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section class="seccion seccion--rojo" style="min-height:100vh; display:flex; align-items:center; justify-content:center;">
        <div style="width:100%; max-width:400px;">
            <div class="seccion__header">
                <p class="seccion__eyebrow seccion__eyebrow--claro">Alumnos</p>
                <h2 class="seccion__titulo seccion__titulo--claro seccion__titulo--sans">Mi cuenta</h2>
            </div>
            <form class="form-atr" action="" method="POST">
                <div class="form-atr__group">
                    <label class="form-atr__label" for="nombre">Nombre</label>
                    <input class="form-atr__input" type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-atr__group">
                    <label class="form-atr__label" for="dni">Contraseña (tu DNI)</label>
                    <input class="form-atr__input" type="text" id="dni" name="dni" required>
                </div>
                <?php if ($errorEstudiante): ?>
                    <p class="form-atr__nota" style="color:var(--crema);"><?php echo htmlspecialchars($errorEstudiante); ?></p>
                <?php endif; ?>
                <button class="form-atr__submit" type="submit">Iniciar sesión →</button>
            </form>
        </div>
    </section>
</body>
</html>