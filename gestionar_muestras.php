<?php
include 'conn.php';

if (!isset($_SESSION['dni_profesor'])) {
    header("Location: login_estudiante.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {

    switch ($_POST['accion']) {

        case 'crear':
            $dia = $_POST['dia'];
            $lugar = $_POST['lugar'];
            $stmt = $conn->prepare("INSERT INTO presentacion (`día`, lugar) VALUES (?, ?)");
            $stmt->bind_param("ss", $dia, $lugar);
            $stmt->execute();
            $stmt->close();
            break;

        case 'editar':
            $dia = $_POST['dia'];
            $lugar = $_POST['lugar'];
            $id = $_POST['id_presentacion'];
            $stmt = $conn->prepare("UPDATE presentacion SET `día` = ?, lugar = ? WHERE id_presentacion = ?");
            $stmt->bind_param("ssi", $dia, $lugar, $id);
            $stmt->execute();
            $stmt->close();
            break;

        case 'eliminar':
            $id = $_POST['id_presentacion'];

            // Primero hay que borrar lo que depende de esta muestra (repertorio e inscripciones)
            $stmt = $conn->prepare("DELETE FROM presentar WHERE id_presentacion = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM participacion WHERE id_presentacion = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            // Recién ahora se puede borrar la muestra en sí
            $stmt = $conn->prepare("DELETE FROM presentacion WHERE id_presentacion = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            break;

        case 'agregar_cancion':
            $idPresentacion = $_POST['id_presentacion'];
            $idCancion = $_POST['id_canciones'];
            $stmt = $conn->prepare("INSERT INTO presentar (id_presentacion, id_canciones) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPresentacion, $idCancion);
            $stmt->execute();
            $stmt->close();
            break;

        case 'quitar_cancion':
            $idPresentacion = $_POST['id_presentacion'];
            $idCancion = $_POST['id_canciones'];
            $stmt = $conn->prepare("DELETE FROM presentar WHERE id_presentacion = ? AND id_canciones = ?");
            $stmt->bind_param("ii", $idPresentacion, $idCancion);
            $stmt->execute();
            $stmt->close();
            break;

        case 'crear_cancion':
            $idPresentacion = $_POST['id_presentacion'];
            $nombreCancion = $_POST['nombre_cancion'];
            $duracion = $_POST['duracion'] . ':00'; // completa mm:ss a formato HH:MM:SS

            // Primero se crea la canción en el catálogo general
            $stmt = $conn->prepare("INSERT INTO canciones (nombre_cancion, `duración`) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombreCancion, $duracion);
            $stmt->execute();
            $idCancionNueva = $stmt->insert_id;
            $stmt->close();

            // Después se agrega automáticamente al repertorio de esta muestra
            $stmt = $conn->prepare("INSERT INTO presentar (id_presentacion, id_canciones) VALUES (?, ?)");
            $stmt->bind_param("ii", $idPresentacion, $idCancionNueva);
            $stmt->execute();
            $stmt->close();
            break;
    }
}

header("Location: panel_profesor.php?guardado=1");
exit;