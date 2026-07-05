<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'log.php';

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'conn.php';
include 'log.php';

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
                <p class="seccion__eyebrow seccion__eyebrow--claro">Acceso interno</p>
                <h2 class="seccion__titulo seccion__titulo--claro seccion__titulo--sans">A Todo Ritmo</h2>
            </div>
            <form class="form-atr" action="" method="POST">
                <div class="form-atr__group">
                    <label class="form-atr__label" for="email">Email</label>
                    <input class="form-atr__input" type="email" id="email" name="email" required>
                </div>
                <div class="form-atr__group">
                    <label class="form-atr__label" for="password">Contraseña</label>
                    <input class="form-atr__input" type="password" id="password" name="password" required>
                </div>
                <?php if ($errorLogin): ?>
                    <p class="form-atr__nota" style="color:var(--crema);"><?php echo htmlspecialchars($errorLogin); ?></p>
                <?php endif; ?>
                <button class="form-atr__submit" type="submit">Iniciar sesión →</button>
            </form>
        </div>
    </section>
</body>
</html>