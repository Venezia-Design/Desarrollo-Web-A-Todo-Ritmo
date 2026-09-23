<?php
include 'conn.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$erroresForm = $_SESSION['errores_form'] ?? [];
$datosForm = $_SESSION['datos_form'] ?? [];
unset($_SESSION['errores_form'], $_SESSION['datos_form']);

$instrumentos = [];
$res = $conn->query("SELECT id_instrumentos, nombre_de_instrumento FROM instrumentos");
while ($row = $res->fetch_assoc()) {
    $instrumentos[$row['id_instrumentos']] = [
        'nombre' => $row['nombre_de_instrumento']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A Todo Ritmo — Anotarme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/lqo1wek.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <section class="seccion seccion--rojo" style="min-height:100vh; display:flex; align-items:center; justify-content:center; position:relative;">
        <a href="home.php" style="position:absolute; top:24px; left:24px; color:var(--crema); font-size:42px; line-height:1; z-index:10;" aria-label="Volver al sitio">←</a>
        <div style="width:100%; max-width:400px;">
            <div class="seccion__header">
                <p class="seccion__eyebrow seccion__eyebrow--claro">Sumate ahora</p>
                <h2 class="seccion__titulo seccion__titulo--claro seccion__titulo--sans">Quiero anotarme</h2>
            </div>

            <form class="form-atr" id="form-atr" action="inscribir.php" method="POST">

            <div class="form-atr__group">
            <label class="form-atr__label" for="f-nombre">Nombre completo</label>
            <input
                class="form-atr__input<?php echo isset($erroresForm['nombre']) ? ' form-atr__input--error' : ''; ?>"
                id="f-nombre"
                name="nombre"
                type="text"
                placeholder="Tu nombre y apellido"
                value="<?php echo htmlspecialchars($datosForm['nombre'] ?? ''); ?>">
            <?php if (isset($erroresForm['nombre'])): ?>
                <p class="form-atr__advertencia"><?php echo htmlspecialchars($erroresForm['nombre']); ?></p>
            <?php endif; ?>
            </div>

            <div class="form-atr__group">
            <label class="form-atr__label" for="f-dni">DNI</label>
            <input
                class="form-atr__input<?php echo isset($erroresForm['dni']) ? ' form-atr__input--error' : ''; ?>"
                id="f-dni"
                name="dni"
                type="text"
                inputmode="numeric"
                placeholder="Sin puntos"
                value="<?php echo htmlspecialchars($datosForm['dni'] ?? ''); ?>">
            <?php if (isset($erroresForm['dni'])): ?>
                <p class="form-atr__advertencia"><?php echo htmlspecialchars($erroresForm['dni']); ?></p>
            <?php endif; ?>
            </div>

            <div class="form-atr__group">
            <label class="form-atr__label" for="f-instrumento">Instrumento de interés</label>
            <select
                class="form-atr__input form-atr__select<?php echo isset($erroresForm['instrumento']) ? ' form-atr__input--error' : ''; ?>"
                id="f-instrumento"
                name="instrumento">

                <option value="" disabled <?php echo empty($datosForm['instrumento']) ? 'selected' : ''; ?>>Elegí un instrumento</option>

                <?php foreach ($instrumentos as $id => $inst): ?>
                    <option value="<?php echo $id; ?>" <?php echo (isset($datosForm['instrumento']) && (string)$datosForm['instrumento'] === (string)$id) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($inst['nombre']); ?>
                    </option>
                <?php endforeach; ?>

            </select>
            <?php if (isset($erroresForm['instrumento'])): ?>
                <p class="form-atr__advertencia"><?php echo htmlspecialchars($erroresForm['instrumento']); ?></p>
            <?php endif; ?>
            </div>

            <div class="form-atr__group">
                    <label class="form-atr__label" for="f-tel">WhatsApp</label>

                <input
                    class="form-atr__input<?php echo isset($erroresForm['whatsapp']) ? ' form-atr__input--error' : ''; ?>"
                    id="f-tel"
                    name="whatsapp"
                    type="tel"
                    inputmode="tel"
                    placeholder="+54 11 ..."
                    value="<?php echo htmlspecialchars($datosForm['whatsapp'] ?? ''); ?>">

                <?php if (isset($erroresForm['whatsapp'])): ?>
                    <p class="form-atr__advertencia"><?php echo htmlspecialchars($erroresForm['whatsapp']); ?></p>
                <?php endif; ?>
            </div>

            <button
                class="form-atr__submit"
                id="form-submit"
                type="submit">

                Enviar inscripción →

            </button>

            <p class="form-atr__nota">
                Si aún no sos estudiante, registraremos tus datos y nos comunicaremos con vos para coordinar el horario y el nivel musical.
            </p>

            </form>

            <?php if (isset($_GET['ok'])): ?>
              <div class="form-exito">
                <div class="form-exito__icono">🎶</div>
                <h3 class="form-exito__titulo">¡Listo!</h3>
                <p class="form-exito__texto">
                  Recibimos tu inscripción.
                  En las próximas 48 hs nos vamos a comunicar con vos por WhatsApp.
                </p>
              </div>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>