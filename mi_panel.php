<?php
include 'conn.php';

if (!isset($_SESSION['dni_estudiante'])) {
    header("Location: login_estudiante.php");
    exit;
}

$dni = $_SESSION['dni_estudiante'];

// Talleres en los que está inscripto (tabla asistir)
$misTalleres = [];
$sql = "
    SELECT t.nombre_taller, t.dia_de_la_semana, t.hora_de_inicio, t.hora_de_finalizacion,
           CONCAT(p.nombre, ' ', p.apellido) AS profe
    FROM asistir a
    JOIN talleres t ON t.nro_comision = a.nro_comision
    JOIN profesores p ON p.dni_profesor = t.dni_profesor
    WHERE a.dni_estudiante = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dni);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $misTalleres[] = $row;
}
$stmt->close();

// Talleres disponibles (a los que el alumno NO está anotado todavía)
$talleresDisponibles = [];
$sql = "
    SELECT t.nro_comision, t.nombre_taller, t.dia_de_la_semana, t.hora_de_inicio, t.hora_de_finalizacion,
           CONCAT(p.nombre, ' ', p.apellido) AS profe
    FROM talleres t
    JOIN profesores p ON p.dni_profesor = t.dni_profesor
    WHERE t.nro_comision NOT IN (
        SELECT nro_comision FROM asistir WHERE dni_estudiante = ?
    )
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dni);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $talleresDisponibles[] = $row;
}
$stmt->close();

// Presentaciones en las que participa (tabla participacion)
$misPresentaciones = [];
$sql = "
    SELECT pres.día, pres.lugar
    FROM participacion part
    JOIN presentacion pres ON pres.id_presentacion = part.id_presentacion
    WHERE part.dni_estudiante = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dni);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $misPresentaciones[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi panel — A Todo Ritmo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/lqo1wek.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

  <?php if (isset($_GET['inscripto'])): ?>
    <div class="aviso-exito">🎶 ¡Listo! Quedaste inscripta en el taller.</div>
  <?php endif; ?>

  <nav class="navbar navbar-expand-md navbar-atr sticky-top">
    <div class="container-fluid px-4 px-md-5">
      <a class="navbar-brand" href="home.php">A Todo Ritmo</a>
      <ul class="navbar-nav ms-auto flex-row gap-3">
        <li class="nav-item"><a class="nav-link" href="home.php">Volver al sitio</a></li>
        <li class="nav-item"><a class="nav-link" href="logout_estudiante.php">Salir</a></li>
      </ul>
    </div>
  </nav>

  <section class="seccion" id="mi-panel">
    <div class="seccion__header">
      <p class="seccion__eyebrow">Hola, <?php echo htmlspecialchars($_SESSION['nombre_estudiante']); ?></p>
      <h2 class="seccion__titulo seccion__titulo--sans">Mi panel</h2>
    </div>

    <h3 style="margin-bottom:16px;">Mis talleres</h3>
    <div class="talleres-lista" style="margin-bottom:48px;">
      <?php if (empty($misTalleres)): ?>
        <p>Todavía no estás anotada en ningún taller.</p>
      <?php else: ?>
        <?php foreach ($misTalleres as $t): ?>
          <div class="taller-row">
            <div class="taller-row__dia"><?php echo htmlspecialchars(mb_substr($t['dia_de_la_semana'],0,3)); ?></div>
            <div class="taller-row__info">
              <p class="taller-row__nombre"><?php echo htmlspecialchars($t['nombre_taller']); ?></p>
              <p class="taller-row__detalle"><?php echo htmlspecialchars($t['profe']); ?></p>
            </div>
            <div class="taller-row__hora">
              <?php echo substr($t['hora_de_inicio'],0,5) . '–' . substr($t['hora_de_finalizacion'],0,5); ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    

    <h3 style="margin-bottom:16px;">Talleres disponibles</h3>
    <div class="talleres-lista" style="margin-bottom:48px;">
      <?php if (empty($talleresDisponibles)): ?>
        <p>Ya estás anotada en todos los talleres disponibles.</p>
      <?php else: ?>
        <?php foreach ($talleresDisponibles as $t): ?>
          <div class="taller-row">
            <div class="taller-row__dia"><?php echo htmlspecialchars(mb_substr($t['dia_de_la_semana'],0,3)); ?></div>
            <div class="taller-row__info">
              <p class="taller-row__nombre"><?php echo htmlspecialchars($t['nombre_taller']); ?></p>
              <p class="taller-row__detalle"><?php echo htmlspecialchars($t['profe']); ?></p>
            </div>
            <form action="inscribir_taller.php" method="post">
              <input type="hidden" name="nro_comision" value="<?php echo $t['nro_comision']; ?>">
              <button type="submit" class="btn-guardar">Inscribirme</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <h3 style="margin-bottom:16px;">Mis presentaciones</h3>
    <div class="muestras-lista">
      <?php if (empty($misPresentaciones)): ?>
        <p>Todavía no participás de ninguna presentación.</p>
      <?php else: ?>
        <?php foreach ($misPresentaciones as $p):
            $fecha = new DateTime($p['día']);
        ?>
          <article class="muestra-card">
            <div class="muestra-card__fecha">
              <span class="muestra-card__dia"><?php echo $fecha->format('d'); ?></span>
              <span class="muestra-card__mes"><?php echo $fecha->format('M'); ?></span>
            </div>
            <div class="muestra-card__cuerpo">
              <p class="muestra-card__lugar"><?php echo htmlspecialchars($p['lugar']); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

</body>
</html>