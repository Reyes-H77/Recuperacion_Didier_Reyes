<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

// ======= AGREGAR MAPA =======
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
  $nombre = $_POST['nombre'];
  $descripcion = $_POST['descripcion'];

  $nombreArchivo = $_FILES['imagen']['name'];
  $rutaTemp = $_FILES['imagen']['tmp_name'];
  $destino = "../../IMG/" . $nombreArchivo;

  if (move_uploaded_file($rutaTemp, $destino)) {
    $sql = $pdo->prepare("INSERT INTO mapa (nombre, descripcion, imagen) VALUES (?, ?, ?)");
    $sql->execute([$nombre, $descripcion, $nombreArchivo]);
  }
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

// ======= ELIMINAR MAPA =======
if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
  $id = $_POST['id_mapa'];

  $stmt = $pdo->prepare("SELECT imagen FROM mapa WHERE id_mapa=?");
  $stmt->execute([$id]);
  $mapa = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($mapa) {
    $rutaImagen = "../../IMG/" . $mapa['imagen'];
    if (file_exists($rutaImagen)) unlink($rutaImagen);

    $sql = $pdo->prepare("DELETE FROM mapa WHERE id_mapa=?");
    $sql->execute([$id]);
  }
  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

// ======= EDITAR MAPA =======
if (isset($_POST['accion']) && $_POST['accion'] == 'editar_confirmar') {
  $id = $_POST['id_mapa'];
  $nombre = $_POST['nombre'];
  $descripcion = $_POST['descripcion'];

  if (!empty($_FILES['imagen']['name'])) {
    $nombreArchivo = $_FILES['imagen']['name'];
    $rutaTemp = $_FILES['imagen']['tmp_name'];
    $destino = "../../IMG/" . $nombreArchivo;
    move_uploaded_file($rutaTemp, $destino);

    $sql = $pdo->prepare("UPDATE mapa SET nombre=?, descripcion=?, imagen=? WHERE id_mapa=?");
    $sql->execute([$nombre, $descripcion, $nombreArchivo, $id]);
  } else {
    $sql = $pdo->prepare("UPDATE mapa SET nombre=?, descripcion=? WHERE id_mapa=?");
    $sql->execute([$nombre, $descripcion, $id]);
  }

  header("Location: ".$_SERVER['PHP_SELF']);
  exit;
}

// ======= CONSULTAR MAPAS =======
$query = $pdo->query("SELECT id_mapa, nombre, descripcion, imagen FROM mapa ORDER BY id_mapa DESC");
$mapas = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRUD Mapas - Free Fire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url("/proyecto_freefire/IMG/fondo.jpg") no-repeat center center fixed;
      background-size: cover;
      color: white;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      padding-top: 40px;
    }
    .card {
      background: rgba(0, 0, 0, 0.7);
      border-radius: 10px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(4px);
      padding: 15px;
      text-align: center;
      color: #fff;
    }
    .mapa-img {
      width: 100%;
      height: 160px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 10px;
    }
    .form-control {
      background-color: rgba(255,255,255,0.1);
      color: white;
      border: none;
    }
    .form-control::placeholder {
      color: #ccc;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>📍 Administrar Mapas</h2>
      <a href="index.php" class="btn btn-secondary">Volver al Lobby</a>
    </div>

    <!-- Formulario Agregar -->
    <div class="card mb-5 p-4">
      <h5>Agregar nuevo mapa</h5>
      <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-4">
            <input type="text" name="nombre" class="form-control" placeholder="Nombre del mapa" required>
          </div>
          <div class="col-md-5">
            <textarea name="descripcion" class="form-control" placeholder="Descripción" required></textarea>
          </div>
          <div class="col-md-3">
            <input type="file" name="imagen" class="form-control" accept="image/*" required>
          </div>
        </div>
        <div class="text-center mt-3">
          <button type="submit" name="accion" value="agregar" class="btn btn-success px-4">Agregar</button>
        </div>
      </form>
    </div>

    <!-- Mostrar Mapas -->
    <div class="row g-4">
      <?php foreach ($mapas as $mapa): ?>
        <?php
          $imgSrc = "/proyecto_freefire/IMG/" . basename($mapa['imagen']);
          $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $imgSrc;
          if (!file_exists($absolutePath)) {
            $imgSrc = "/proyecto_freefire/IMG/default.jpg";
          }
        ?>
        <div class="col-md-4">
          <div class="card h-100">
            <img src="<?= htmlspecialchars($imgSrc) ?>" class="mapa-img" alt="<?= htmlspecialchars($mapa['nombre']) ?>">
            <h5><?= htmlspecialchars($mapa['nombre']) ?></h5>
            <p><?= htmlspecialchars($mapa['descripcion']) ?></p>

            <div class="d-flex justify-content-center gap-2 mt-auto">
              <button class="btn btn-warning btn-sm" data-bs-toggle="collapse" data-bs-target="#edit<?= $mapa['id_mapa'] ?>">Editar</button>
              <form method="POST">
                <input type="hidden" name="id_mapa" value="<?= $mapa['id_mapa'] ?>">
                <button type="submit" name="accion" value="eliminar" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este mapa?');">Eliminar</button>
              </form>
            </div>

            <!-- Formulario Editar -->
            <div class="collapse mt-3" id="edit<?= $mapa['id_mapa'] ?>">
              <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_mapa" value="<?= $mapa['id_mapa'] ?>">
                <input type="text" name="nombre" class="form-control my-2" value="<?= htmlspecialchars($mapa['nombre']) ?>">
                <textarea name="descripcion" class="form-control my-2"><?= htmlspecialchars($mapa['descripcion']) ?></textarea>
                <input type="file" name="imagen" class="form-control my-2" accept="image/*">
                <button type="submit" name="accion" value="editar_confirmar" class="btn btn-primary btn-sm">Guardar Cambios</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>