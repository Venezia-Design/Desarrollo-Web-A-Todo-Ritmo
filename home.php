<?php
include 'conn.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$erroresForm = $_SESSION['errores_form'] ?? [];
$datosForm = $_SESSION['datos_form'] ?? [];
unset($_SESSION['errores_form'], $_SESSION['datos_form']);

$esAdmin = isset($_SESSION['rol']) && $_SESSION['rol'] === 'administrador';

// Instrumentos + profesores de cada uno
$instrumentos = [];
$res = $conn->query("SELECT id_instrumentos, nombre_de_instrumento FROM instrumentos");
while ($row = $res->fetch_assoc()) {
    $instrumentos[$row['id_instrumentos']] = [
        'nombre' => $row['nombre_de_instrumento'],
        'profesores' => []
    ];
}
$res = $conn->query("
    SELECT e.id_instrumentos, CONCAT(p.nombre, ' ', p.apellido) AS profe
    FROM especializar e
    JOIN profesores p ON p.dni_profesor = e.dni_profesor
");
while ($row = $res->fetch_assoc()) {
    if (isset($instrumentos[$row['id_instrumentos']])) {
        $instrumentos[$row['id_instrumentos']]['profesores'][] = $row['profe'];
    }
}

// Imagen por instrumento (según los archivos que armaron en Images/)
function imagenInstrumento($nombre) {
    $n = mb_strtolower($nombre);
    if (str_contains($n, 'piano'))    return 'Mesa de trabajo 8.png';
    if (str_contains($n, 'guitarra')) return 'Mesa de trabajo 6.png';
    if (str_contains($n, 'canto'))    return 'Mesa de trabajo 5.png';
    if (str_contains($n, 'ater'))     return 'Mesa de trabajo 3.png';
    if (str_contains($n, 'bajo'))     return 'Mesa de trabajo 1.png';
    if (str_contains($n, 'violin') || str_contains($n, 'violín')) return 'Mesa de trabajo 7.png';
    return 'Mesa de trabajo 8.png';
}

// Sonido por instrumento (archivos en /sonidos/)
function sonidoInstrumento($nombre) {
    $n = mb_strtolower($nombre);
    if (str_contains($n, 'piano'))                               return 'sonido_piano.mp3';
    if (str_contains($n, 'guitarra'))                            return 'sonido_guitarra.mp3';
    if (str_contains($n, 'bajo'))                                return 'sonido_bajo.wav';
    if (str_contains($n, 'violin') || str_contains($n, 'violín')) return 'sonido_violin.wav';
    if (str_contains($n, 'bater') || str_contains($n, 'bateri')) return 'sonido_bateria.wav';
    if (str_contains($n, 'canto') || str_contains($n, 'micro')) return 'sonido_microfono.wav';
}

// Profesores con sus instrumentos
$profesores = [];
$res = $conn->query("SELECT dni_profesor, nombre, apellido, Biografia FROM profesores");
while ($row = $res->fetch_assoc()) {
    $profesores[$row['dni_profesor']] = $row;
    $profesores[$row['dni_profesor']]['instrumentos'] = [];
}
$res = $conn->query("
    SELECT e.dni_profesor, i.nombre_de_instrumento
    FROM especializar e
    JOIN instrumentos i ON i.id_instrumentos = e.id_instrumentos
");
while ($row = $res->fetch_assoc()) {
    if (isset($profesores[$row['dni_profesor']])) {
        $profesores[$row['dni_profesor']]['instrumentos'][] = $row['nombre_de_instrumento'];
    }
}

// Talleres
$talleres = [];
$res = $conn->query("
    SELECT t.nro_comision, t.nombre_taller, t.dia_de_la_semana, t.hora_de_inicio, t.hora_de_finalizacion,
           CONCAT(p.nombre, ' ', p.apellido) AS profe
    FROM talleres t
    JOIN profesores p ON p.dni_profesor = t.dni_profesor
    ORDER BY t.nro_comision
");
while ($row = $res->fetch_assoc()) {
    $talleres[] = $row;
}

// Presentaciones (muestras) con repertorio
$presentaciones = [];
$res = $conn->query("SELECT id_presentacion, día, lugar FROM presentacion ORDER BY día");
while ($row = $res->fetch_assoc()) {
    $presentaciones[$row['id_presentacion']] = $row;
    $presentaciones[$row['id_presentacion']]['canciones'] = [];
}
$res = $conn->query("
    SELECT pr.id_presentacion, c.nombre_cancion, c.duración
    FROM presentar pr
    JOIN canciones c ON c.id_canciones = pr.id_canciones
");
while ($row = $res->fetch_assoc()) {
    if (isset($presentaciones[$row['id_presentacion']])) {
        $presentaciones[$row['id_presentacion']]['canciones'][] = $row;
    }
}

// Registros de interesados (solo para administrador)
$registros = [];

if ($esAdmin) {
    $sql = "
        SELECT
            r.*,
            i.nombre_de_instrumento
        FROM registros r
        JOIN instrumentos i
            ON r.id_instrumentos = i.id_instrumentos
        ORDER BY r.fecha_solicitud DESC
    ";

    $res = $conn->query($sql);

    while ($row = $res->fetch_assoc()) {
        $registros[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>A Todo Ritmo — Escuela de música</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://use.typekit.net/lqo1wek.css">
<link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- NAV -->
  <nav class="navbar navbar-expand-md navbar-atr sticky-top">
    <div class="container-fluid px-4 px-md-5">
      <a class="navbar-brand" href="#inicio">A Todo Ritmo</a>
      <button class="navbar-toggler border-0" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#navOffcanvas"
              aria-controls="navOffcanvas" aria-label="Abrir menú">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="#instrumentos">Instrumentos</a></li>
          <li class="nav-item"><a class="nav-link" href="#profesores">Profesores</a></li>
          <li class="nav-item"><a class="nav-link" href="#talleres">Horarios</a></li>
          <li class="nav-item"><a class="nav-link" href="#muestras">Muestras</a></li>
          <?php if ($esAdmin): ?>
            <li class="nav-item"><a class="nav-link" href="#registros">Interesados</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="anotarme.php">Anotarme</a></li>
          <?php endif; ?>
          <?php if (!$esAdmin): ?>
            <?php if (isset($_SESSION['dni_estudiante'])): ?>
              <li class="nav-item"><a class="nav-link" href="mi_panel.php">Mi panel</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="login_estudiante.php">Iniciar sesión</a></li>
            <?php endif; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="offcanvas offcanvas-end offcanvas-atr" tabindex="-1" id="navOffcanvas">
    <div class="offcanvas-header">
      <span class="navbar-brand m-0" style="color:inherit;">Menú</span>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="#instrumentos">Instrumentos<small>Elegí qué tocar</small></a></li>
        <li class="nav-item"><a class="nav-link" href="#profesores">Profesores<small>Quién te enseña</small></a></li>
        <li class="nav-item"><a class="nav-link" href="#talleres">Horarios<small>Talleres por día</small></a></li>
        <li class="nav-item"><a class="nav-link" href="#muestras">Muestras<small>Próximos shows</small></a></li>
        <?php if ($esAdmin): ?>
          <li class="nav-item"><a class="nav-link" href="#registros">Interesados<small>Personas anotadas</small></a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="anotarme.php">Anotarme<small>Sumate ahora</small></a></li>
        <?php endif; ?>
        <?php if (!$esAdmin): ?>
          <?php if (isset($_SESSION['dni_estudiante'])): ?>
            <li class="nav-item"><a class="nav-link" href="mi_panel.php">Mi panel</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login_estudiante.php">Iniciar sesión</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- HERO -->
  <section class="hero" id="inicio">
    <div class="hero__overlay"></div>
    <div class="hero__content">
      <p class="hero__eyebrow">Escuela de instrumentos &amp; muestras en vivo</p>
      <h1 class="hero__title">A Todo<br>Ritmo</h1>
      <p class="hero__sub">Un espacio para descubrir tu sonido, aprender un instrumento y compartirlo con otros.</p>
      <a href="anotarme.php" class="hero__cta">Quiero anotarme →</a>
    </div>
  </section>

  <!-- INSTRUMENTOS -->
  <section class="seccion" id="instrumentos">
    <div class="seccion__header">
      <p class="seccion__eyebrow">Lo que enseñamos</p>
      <h2 class="seccion__titulo">Instrumentos</h2>
    </div>

    <div class="instrumentos-grid">
      <?php foreach ($instrumentos as $inst): ?>
        <?php $sonido = sonidoInstrumento($inst['nombre']); ?>
        <button class="instrumento-card"
                data-instrumento="<?php echo htmlspecialchars($inst['nombre']); ?>"
                data-profesores="<?php echo htmlspecialchars(implode(', ', $inst['profesores'])); ?>"
                <?php if ($sonido): ?>data-sonido="sonidos/<?php echo $sonido; ?>"<?php endif; ?>>
          <img src="Images/<?php echo imagenInstrumento($inst['nombre']); ?>"
               alt="<?php echo htmlspecialchars($inst['nombre']); ?>"
               class="instrumento-card__imagen">
        </button>
      <?php endforeach; ?>
    </div>

    <div class="especializar" id="especializar" aria-live="polite" hidden>
      <div class="especializar__inner">
        <button class="especializar__cerrar" aria-label="Cerrar">✕</button>
        <p class="especializar__label">Profesores de</p>
        <p class="especializar__instrumento" id="esp-instrumento"></p>
        <p class="especializar__lista" id="esp-lista"></p>
        <a href="#profesores" class="especializar__link">Ver perfiles completos →</a>
      </div>
    </div>
  </section>

<!-- PROFESORES -->
  <section class="seccion seccion--oscura" id="profesores">
<div class="seccion__header">
<p class="seccion__eyebrow seccion__eyebrow--claro">El equipo</p>
<h2 class="seccion__titulo seccion__titulo--claro seccion__titulo--sans">Profesores</h2>
</div>
<div class="profesores-carrusel" role="list">
<?php foreach ($profesores as $p):
$iniciales = mb_strtoupper(mb_substr($p['nombre'],0,1) . mb_substr($p['apellido'],0,1));
?>
<article class="profe-card" role="listitem">
<div class="profe-card__avatar"><?php echo htmlspecialchars($iniciales); ?></div>
<h3 class="profe-card__nombre"><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></h3>
<p class="profe-card__instrumentos"><?php echo htmlspecialchars(implode(' · ', $p['instrumentos'])); ?></p>
<?php if ($esAdmin): ?>
  <form action="guardar.php" method="post" class="edit-inline">
    <input type="hidden" name="tipo" value="profesor">
    <input type="hidden" name="dni_profesor" value="<?php echo $p['dni_profesor']; ?>">
    <textarea name="biografia"><?php echo htmlspecialchars($p['Biografia']); ?></textarea>
    <button type="submit" class="btn-guardar">Guardar bio</button>
  </form>
<?php else: ?>
  <p class="profe-card__bio"><?php echo htmlspecialchars($p['Biografia']); ?></p>
<?php endif; ?>
</article>
<?php endforeach; ?>
</div>

<?php if ($esAdmin): ?>
<div class="profe-card profe-card--nuevo" style="margin-top:24px;">
  <p class="muestra-card__rep-titulo" style="margin-bottom:10px;">Agregar nuevo profesor</p>
  <form action="guardar.php" method="post" class="edit-inline">
    <input type="hidden" name="tipo" value="profesor_nuevo">
    <input type="number" name="dni_profesor" placeholder="DNI" required>
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="text" name="apellido" placeholder="Apellido" required>
    <select name="id_instrumentos" required>
      <option value="" disabled selected>Instrumento</option>
      <?php foreach ($instrumentos as $id => $inst): ?>
        <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($inst['nombre']); ?></option>
      <?php endforeach; ?>
    </select>
    <textarea name="biografia" placeholder="Biografía (opcional)"></textarea>
    <button type="submit" class="btn-guardar">Agregar profesor</button>
  </form>
</div>
<?php endif; ?>

<div class="carrusel-dots" id="carrusel-dots" aria-hidden="true"></div>
</section>

<!-- TALLERES -->
  <section class="seccion" id="talleres">
<div class="seccion__header">
<p class="seccion__eyebrow">Esta semana</p>
<h2 class="seccion__titulo">Talleres y horarios</h2>
</div>
<div class="talleres-filtros" role="group" aria-label="Filtrar por día">
<button class="filtro-btn filtro-btn--activo" data-dia="todos">Todos</button>
<button class="filtro-btn" data-dia="Lunes">Lun</button>
<button class="filtro-btn" data-dia="Martes">Mar</button>
<button class="filtro-btn" data-dia="Miércoles">Mié</button>
<button class="filtro-btn" data-dia="Jueves">Jue</button>
<button class="filtro-btn" data-dia="Viernes">Vie</button>
<button class="filtro-btn" data-dia="Sábado">Sáb</button>
</div>
<div class="talleres-lista" id="talleres-lista">
<?php foreach ($talleres as $t): ?>
<div class="taller-row" data-dia="<?php echo htmlspecialchars($t['dia_de_la_semana']); ?>">
<div class="taller-row__dia"><?php echo htmlspecialchars(mb_substr($t['dia_de_la_semana'],0,3)); ?></div>
<div class="taller-row__info">
<p class="taller-row__nombre"><?php echo htmlspecialchars($t['nombre_taller']); ?></p>
<p class="taller-row__detalle"><?php echo htmlspecialchars($t['profe']); ?></p>
<?php if ($esAdmin): ?>
  <form action="guardar.php" method="post" class="edit-inline">
    <input type="hidden" name="tipo" value="taller">
    <input type="hidden" name="nro_comision" value="<?php echo $t['nro_comision']; ?>">
    <input type="time" name="hora_de_inicio" value="<?php echo $t['hora_de_inicio']; ?>">
    <input type="time" name="hora_de_finalizacion" value="<?php echo $t['hora_de_finalizacion']; ?>">
    <button type="submit" class="btn-guardar">Guardar</button>
  </form>
<?php endif; ?>
</div>
<?php if (!$esAdmin): ?>
<div class="taller-row__hora">
<?php echo substr($t['hora_de_inicio'],0,5) . '–' . substr($t['hora_de_finalizacion'],0,5); ?>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>

<?php if ($esAdmin): ?>
<div class="taller-row taller-row--nuevo">
  <form action="guardar.php" method="post" class="edit-inline">
    <input type="hidden" name="tipo" value="taller_nuevo">
    <input type="text" name="nombre_taller" placeholder="Nombre del taller" required>
    <select name="dia_de_la_semana" required>
      <option value="" disabled selected>Día</option>
      <option value="Lunes">Lunes</option>
      <option value="Martes">Martes</option>
      <option value="Miércoles">Miércoles</option>
      <option value="Jueves">Jueves</option>
      <option value="Viernes">Viernes</option>
      <option value="Sábado">Sábado</option>
    </select>
    <input type="time" name="hora_de_inicio" required>
    <input type="time" name="hora_de_finalizacion" required>
    <select name="dni_profesor" required>
      <option value="" disabled selected>Profesor</option>
      <?php foreach ($profesores as $dni => $p): ?>
        <option value="<?php echo $dni; ?>"><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-guardar">Agregar taller</button>
  </form>
</div>
<?php endif; ?>

</div>
</section>

<!-- MUESTRAS -->
  <section class="seccion" id="muestras">
<div class="seccion__header">
<p class="seccion__eyebrow">Próximos shows</p>
<h2 class="seccion__titulo">Muestras</h2>
</div>
<div class="muestras-lista">
<article class="muestra-card muestra-card--imagen">
<img src="Images/presentaciones en vivo.jpg" alt="Muestra de fin de semestre" class="muestra-card__foto">
</article>
<?php foreach ($presentaciones as $pres):
$fecha = new DateTime($pres['día']);
$meses = ['ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
?>
<article class="muestra-card">
<div class="muestra-card__fecha">
<span class="muestra-card__dia"><?php echo $fecha->format('d'); ?></span>
<span class="muestra-card__mes"><?php echo $meses[(int)$fecha->format('n') - 1]; ?></span>
</div>
<div class="muestra-card__cuerpo">
<?php if ($esAdmin): ?>
  <form action="guardar.php" method="post" class="edit-inline">
    <input type="hidden" name="tipo" value="presentacion">
    <input type="hidden" name="id_presentacion" value="<?php echo $pres['id_presentacion']; ?>">
    <input type="date" name="dia" value="<?php echo $pres['día']; ?>">
    <input type="text" name="lugar" value="<?php echo htmlspecialchars($pres['lugar']); ?>">
    <button type="submit" class="btn-guardar">Guardar</button>
  </form>
<?php else: ?>
  <p class="muestra-card__lugar"><?php echo htmlspecialchars($pres['lugar']); ?></p>
<?php endif; ?>
<div class="muestra-card__repertorio">
<p class="muestra-card__rep-titulo">Repertorio</p>
<ul class="muestra-card__canciones">
<?php foreach ($pres['canciones'] as $c): ?>
<li>
<span><?php echo htmlspecialchars($c['nombre_cancion']); ?></span>
<span class="dur"><?php echo substr($c['duración'],3); ?></span>
</li>
<?php endforeach; ?>
</ul>
</div>
</div>
</article>
<?php endforeach; ?>
</div>
</section>

  <!-- ANOTARME: solo visible para usuarios normales -->
  <?php if (!$esAdmin): ?>
    <section class="seccion seccion--rojo" id="anotarme">
    <div class="seccion__header">
      <p class="seccion__eyebrow seccion__eyebrow--claro">¿Ya conociste todo?</p>
      <h2 class="seccion__titulo seccion__titulo--claro seccion__titulo--sans">Sumate a A Todo Ritmo</h2>
    </div>
    <p class="form-atr__nota" style="text-align:center; max-width:500px; margin:0 auto 24px;">
      Elegí tu instrumento, conocé a tus futuros profesores y sumate a los talleres cuando quieras empezar.
    </p>
    <div style="display:flex; justify-content:center;">
      <a href="anotarme.php" class="hero__cta">Quiero anotarme →</a>
    </div>
  </section>
  
  <?php endif; /* fin !$esAdmin — formulario Anotarme */ ?>

  <!-- REGISTROS DE INTERESADOS: solo visible para administrador -->
  <?php if ($esAdmin): ?>
  <section class="seccion seccion--rojo" id="registros">

    <div class="seccion__header">
      <p class="seccion__eyebrow">Administración</p>
      <h2 class="seccion__titulo">Personas interesadas</h2>
    </div>

    <div class="row g-4">

      <?php if (count($registros) > 0): ?>

        <?php foreach ($registros as $registro): ?>

          <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
              <div class="card-body">

                <h5 class="card-title">
                  <?php echo htmlspecialchars($registro['nombre']); ?>
                </h5>

                <p class="mb-2">
                  <strong>DNI:</strong><br>
                  <?php echo htmlspecialchars($registro['dni']); ?>
                </p>

                <p class="mb-2">
                  <strong>WhatsApp:</strong><br>
                  <?php echo htmlspecialchars($registro['whatsapp']); ?>
                </p>

                <p class="mb-2">
                  <strong>Instrumento:</strong><br>
                  <?php echo htmlspecialchars($registro['nombre_de_instrumento']); ?>
                </p>

                <p class="mb-3">
                  <strong>Fecha:</strong><br>
                  <?php echo date("d/m/Y", strtotime($registro['fecha_solicitud'])); ?>
                </p>

                <?php if (!$registro['contactado']): ?>

                  <span class="badge bg-warning text-dark mb-3">Pendiente</span>

                  <form action="guardar.php" method="POST">
                    <input type="hidden" name="tipo" value="contactado">
                    <input type="hidden" name="id_interesado" value="<?php echo $registro['id_interesado']; ?>">
                    <button class="btn btn-success w-100">✓ Marcar como contactado</button>
                  </form>

                <?php else: ?>

                  <span class="badge bg-success">Contactado ✓</span>

                <?php endif; ?>

              </div>
            </div>
          </div>

        <?php endforeach; ?>

      <?php else: ?>

        <p>No hay registros todavía.</p>

      <?php endif; ?>

    </div>

  </section>
  <?php endif; /* fin $esAdmin — registros */ ?>

  <!-- FOOTER -->
  <?php include_once './componentes/footer.php';?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('#navOffcanvas .nav-link').forEach(link => {
      link.addEventListener('click', () => {
        const offcanvasEl = document.getElementById('navOffcanvas');
        const instancia = bootstrap.Offcanvas.getInstance(offcanvasEl);
        if (instancia) instancia.hide();
      });
    });

    document.querySelectorAll('.instrumento-card').forEach(card => {
      card.addEventListener('click', () => {
        document.getElementById('esp-instrumento').textContent = card.dataset.instrumento;
        document.getElementById('esp-lista').textContent = card.dataset.profesores;
        const panel = document.getElementById('especializar');
        panel.hidden = false;
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      });
    });
    document.querySelector('.especializar__cerrar').addEventListener('click', () => {
      document.getElementById('especializar').hidden = true;
    });

    const carrusel = document.querySelector('.profesores-carrusel');
    const dotsContainer = document.getElementById('carrusel-dots');
    const profCards = document.querySelectorAll('.profe-card');
    if (carrusel && profCards.length) {
      profCards.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.className = 'carrusel-dot' + (i === 0 ? ' carrusel-dot--activo' : '');
        dot.setAttribute('aria-label', 'Profesor ' + (i + 1));
        dot.addEventListener('click', () => {
          profCards[i].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        });
        dotsContainer.appendChild(dot);
      });
      const obs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            const idx = Array.from(profCards).indexOf(entry.target);
            document.querySelectorAll('.carrusel-dot').forEach((d, i) => {
              d.classList.toggle('carrusel-dot--activo', i === idx);
            });
          }
        });
      }, { root: carrusel, threshold: 0.6 });
      profCards.forEach(c => obs.observe(c));
    }

    document.querySelectorAll('.filtro-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('filtro-btn--activo'));
        btn.classList.add('filtro-btn--activo');
        const dia = btn.dataset.dia;
        document.querySelectorAll('.taller-row').forEach(fila => {
          fila.style.display = (dia === 'todos' || fila.dataset.dia === dia) ? 'flex' : 'none';
        });
      });
    });

   
  <!-- Sonidos de instrumentos -->
  <script>
    (function () {
      const cache = {};

      document.querySelectorAll('.instrumento-card[data-sonido]').forEach(card => {
        const src = card.dataset.sonido;
        const audio = new Audio(src);
        audio.preload = 'auto';
        cache[src] = audio;
      });

      document.querySelectorAll('.instrumento-card[data-sonido]').forEach(card => {
        card.addEventListener('click', () => {
          const audio = cache[card.dataset.sonido];
          if (!audio) return;
          audio.currentTime = 0;
          audio.play().catch(() => {});
        });
      });
    })();
  </script>

</body>
</html>