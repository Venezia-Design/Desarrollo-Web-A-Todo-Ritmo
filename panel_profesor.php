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

// Todas las muestras (presentaciones) con su repertorio, para gestionarlas
$muestras = [];
$sql = "SELECT id_presentacion, día, lugar FROM presentacion ORDER BY día";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    $muestras[$row['id_presentacion']] = $row;
    $muestras[$row['id_presentacion']]['canciones'] = [];
}
$sql = "
    SELECT pr.id_presentacion, c.id_canciones, c.nombre_cancion
    FROM presentar pr
    JOIN canciones c ON c.id_canciones = pr.id_canciones
";
$res = $conn->query($sql);
while ($row = $res->fetch_assoc()) {
    if (isset($muestras[$row['id_presentacion']])) {
        $muestras[$row['id_presentacion']]['canciones'][] = $row;
    }
}

// Todas las canciones del catálogo (para el desplegable de "agregar canción")
$todasLasCanciones = [];
$res = $conn->query("SELECT id_canciones, nombre_cancion FROM canciones ORDER BY nombre_cancion");
while ($row = $res->fetch_assoc()) {
    $todasLasCanciones[] = $row;
}
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
    <div class="aviso-exito">🎶 ¡Listo! Guardamos los cambios.</div>
  <?php endif; ?>
  <?php include_once './componentes/nav_panel.php';?>

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
    <div class="talleres-lista" style="margin-bottom:48px;">
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

    <h3 style="margin-bottom:16px;">Gestionar muestras</h3>

    <div style="margin-bottom:32px;">
      <p style="font-size:13px; font-weight:700; margin-bottom:8px;">Nueva muestra</p>
      <form action="gestionar_muestras.php" method="post" class="edit-inline edit-inline--claro">
        <input type="hidden" name="accion" value="crear">
        <input type="date" name="dia" required>
        <input type="text" name="lugar" placeholder="Lugar" required>
        <button type="submit" class="btn-guardar">Crear muestra</button>
      </form>
    </div>

    <?php foreach ($muestras as $id => $m): ?>
      <div style="padding:20px 0; border-bottom:1px solid rgba(0,0,0,0.12);">

        <form action="gestionar_muestras.php" method="post" class="edit-inline edit-inline--claro">
          <input type="hidden" name="accion" value="editar">
          <input type="hidden" name="id_presentacion" value="<?php echo $id; ?>">
          <input type="date" name="dia" value="<?php echo $m['día']; ?>">
          <input type="text" name="lugar" value="<?php echo htmlspecialchars($m['lugar']); ?>">
          <div style="display:flex; gap:8px;">
            <button type="submit" class="btn-guardar">Guardar cambios</button>
          </div>
        </form>

        <form action="gestionar_muestras.php" method="post" style="margin-top:8px; display:inline;">
          <input type="hidden" name="accion" value="eliminar">
          <input type="hidden" name="id_presentacion" value="<?php echo $id; ?>">
          <button type="submit" class="btn-guardar" style="background-color:var(--bordo);"
                  onclick="return confirm('¿Seguro que querés eliminar esta muestra y todo su repertorio?');">
            Eliminar muestra
          </button>
        </form>

        <p style="margin-top:16px; font-size:13px; font-weight:700;">Repertorio:</p>
        <ul style="font-size:13px; margin-top:4px; margin-bottom:12px; list-style:none; padding:0;">
          <?php foreach ($m['canciones'] as $c): ?>
            <li style="display:flex; justify-content:space-between; align-items:center; padding:4px 0;">
              <span><?php echo htmlspecialchars($c['nombre_cancion']); ?></span>
              <form action="gestionar_muestras.php" method="post" style="display:inline;">
                <input type="hidden" name="accion" value="quitar_cancion">
                <input type="hidden" name="id_presentacion" value="<?php echo $id; ?>">
                <input type="hidden" name="id_canciones" value="<?php echo $c['id_canciones']; ?>">
                <button type="submit" class="btn-guardar" style="background-color:var(--bordo); padding:4px 10px; font-size:10px;">✕</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>

        <form action="gestionar_muestras.php" method="post" class="edit-inline edit-inline--claro">
          <input type="hidden" name="accion" value="agregar_cancion">
          <input type="hidden" name="id_presentacion" value="<?php echo $id; ?>">
          <select name="id_canciones" required>
            <option value="" disabled selected>Agregar canción al repertorio</option>
            <?php foreach ($todasLasCanciones as $c): ?>
              <option value="<?php echo $c['id_canciones']; ?>"><?php echo htmlspecialchars($c['nombre_cancion']); ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn-guardar">Agregar</button>
        </form>

        <form action="gestionar_muestras.php" method="post" class="edit-inline edit-inline--claro" style="margin-top:8px;">
          <input type="hidden" name="accion" value="crear_cancion">
          <input type="hidden" name="id_presentacion" value="<?php echo $id; ?>">
          <input type="text" name="nombre_cancion" placeholder="Nombre de la canción nueva" required>
          <input type="text" name="duracion" placeholder="Duración (mm:ss, ej: 04:30)" pattern="[0-9]{2}:[0-9]{2}" required>
          <button type="submit" class="btn-guardar">Cargar canción nueva</button>
        </form>

      </div>
    <?php endforeach; ?>

  </section>

</body>
</html>