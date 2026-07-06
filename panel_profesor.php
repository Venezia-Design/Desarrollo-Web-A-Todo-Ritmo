<?php
include 'conn.php';

if (!isset($_SESSION['dni_profesor'])) {
    header("Location: login_estudiante.php");
    exit;
}

$dniProfesor = $_SESSION['dni_profesor'];

// Bio del profesor
$sql = "SELECT Biografia FROM profesores WHERE dni_profesor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dniProfesor);
$stmt->execute();
$bio = $stmt->get_result()->fetch_assoc()['Biografia'];
$stmt->close();

// Talleres que dicta, con la lista de alumnos de cada uno
$misTalleres = [];
$sql = "SELECT nro_comision, nombre_taller, dia_de_la_semana, hora_de_inicio, hora_de_finalizacion FROM talleres WHERE dni_profesor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dniProfesor);
$stmt->execute();
$res = $stmt->get_result();
while ($t = $res->fetch_assoc()) {
    $sql2 = "
        SELECT e.nombre, e.apellido
        FROM asistir a
        JOIN estudiantes e ON e.dni_estudiante = a.dni_estudiante
        WHERE a.nro_comision = ?
    ";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $t['nro_comision']);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    $t['alumnos'] = [];
    while ($al = $res2->fetch_assoc()) {
        $t['alumnos'][] = $al['nombre'] . ' ' . $al['apellido'];
    }
    $stmt2->close();
    $misTalleres[] = $t;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del profesor — A Todo Ritmo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.typekit.net/lqo1wek.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

  <?php if (isset($_GET['guardado'])): ?>
    <div class="aviso-exito">🎶 ¡Listo! Guardamos tu descripción.</div>
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

  <section class="seccion" id="panel-profesor">
    <div class="seccion__header">
      <p class="seccion__eyebrow">Hola, <?php echo htmlspecialchars($_SESSION['nombre_profesor']); ?></p>
      <h2 class="seccion__titulo seccion__titulo--sans">Mi panel</h2>
    </div>

    <h3 style="margin-bottom:16px;">Mi descripción</h3>
    <form action="guardar_profesor.php" method="post" class="edit-inline edit-inline--claro" style="margin-bottom:48px;">
      <textarea name="biografia"><?php echo htmlspecialchars($bio); ?></textarea>
      <button type="submit" class="btn-guardar">Guardar descripción</button>
    </form>

    <h3 style="margin-bottom:16px;">Mis talleres</h3>
    <div class="talleres-lista">
      <?php if (empty($misTalleres)): ?>
        <p>Todavía no tenés talleres asignados.</p>
      <?php else: ?>
        <?php foreach ($misTalleres as $t): ?>
          <div style="padding:16px 0; border-bottom:1px solid rgba(0,0,0,0.12);">
            <p class="taller-row__nombre"><?php echo htmlspecialchars($t['nombre_taller']); ?></p>
            <p class="taller-row__detalle">
              <?php echo htmlspecialchars($t['dia_de_la_semana']); ?> ·
              <?php echo substr($t['hora_de_inicio'],0,5) . '–' . substr($t['hora_de_finalizacion'],0,5); ?>
            </p>
            <p style="margin-top:8px; font-size:13px; font-weight:700;">Alumnos anotados:</p>
            <?php if (empty($t['alumnos'])): ?>
              <p style="font-size:13px; color:rgba(0,0,0,0.5);">Todavía no hay alumnos anotados.</p>
            <?php else: ?>
              <ul style="font-size:13px; margin-top:4px;">
                <?php foreach ($t['alumnos'] as $al): ?>
                  <li><?php echo htmlspecialchars($al); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

</body>
</html>