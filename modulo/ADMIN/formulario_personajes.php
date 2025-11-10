<?php
session_name("freefire_session");
session_start();
require_once(__DIR__ . "/../../DB/conection.php");

$db = new Database();
$con = $db->conectar();

// Solo admin
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

$editar = null;

// Si se edita un personaje
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $con->prepare("SELECT * FROM personajes WHERE Id_personajes=?");
    $stmt->execute([$id]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Guardar cambios o nuevo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['Id_personajes'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $skin = $_POST['skin_actual'] ?? '';

    // Subir imagen
    if (!empty($_FILES['skin']['name'])) {
        $nombreArchivo = basename($_FILES['skin']['name']);
        $rutaDestino = "../../IMG/" . $nombreArchivo;
        if (move_uploaded_file($_FILES['skin']['tmp_name'], $rutaDestino)) {
            $skin = "IMG/" . $nombreArchivo;
        }
    }

    if ($id) {
        $sql = $con->prepare("UPDATE personajes SET nombre=?, descripcion=?, skin=? WHERE Id_personajes=?");
        $sql->execute([$nombre, $descripcion, $skin, $id]);
    } else {
        $sql = $con->prepare("INSERT INTO personajes (nombre, descripcion, skin) VALUES (?, ?, ?)");
        $sql->execute([$nombre, $descripcion, $skin]);
    }

    header("Location: crud_personajes.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $editar ? 'Editar Personaje' : 'Agregar Personaje' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url("../../IMG/fondo.jpg") no-repeat center center fixed;
      background-size: cover;
      color: #fff;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    /* Capa oscura suave */
    body::before {
      content: "";
      position: absolute;
      inset: 0;
      background: rgba(0, 0, 0, 0.65);
      z-index: 0;
    }

    .contenedor-form {
      position: relative;
      z-index: 1;
      width: 90%;
      max-width: 650px;
      background: rgba(25, 25, 25, 0.85);
      border-radius: 15px;
      padding: 35px 40px;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(6px);
    }

    h2 {
      text-align: center;
      color: #ffc107;
      margin-bottom: 25px;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 1px;
    }

    label {
      font-weight: 500;
      color: #ddd;
    }

    .form-control {
      background-color: rgba(255,255,255,0.08);
      border: 1px solid rgba(255,255,255,0.15);
      color: #fff;
      border-radius: 8px;
    }

    .form-control:focus {
      border-color: #ffc107;
      box-shadow: 0 0 8px rgba(255, 193, 7, 0.4);
    }

    textarea {
      resize: none;
    }

    .preview-imagen {
      display: flex;
      justify-content: center;
      margin-top: 15px;
    }

    .preview-imagen img {
      width: 240px;
      height: 280px;
      object-fit: contain;
      border-radius: 10px;
      border: 2px solid rgba(255,255,255,0.3);
      background-color: rgba(0,0,0,0.6);
    }

    .botones {
      display: flex;
      justify-content: space-between;
      margin-top: 25px;
    }

    .btn {
      font-weight: 600;
      border-radius: 10px;
      padding: 10px 18px;
      letter-spacing: 0.5px;
    }

    .btn-success {
      background-color: #198754;
      border: none;
    }

    .btn-success:hover {
      background-color: #157347;
    }

    .btn-secondary {
      background-color: #6c757d;
      border: none;
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body>

<div class="contenedor-form">
  <h2><?= $editar ? 'Editar Personaje' : 'Agregar Nuevo Personaje' ?></h2>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="Id_personajes" value="<?= $editar['Id_personajes'] ?? '' ?>">
    <input type="hidden" name="skin_actual" value="<?= $editar['skin'] ?? '' ?>">

    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre del personaje</label>
      <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>" required>
    </div>

    <div class="mb-3">
      <label for="descripcion" class="form-label">Descripción</label>
      <textarea name="descripcion" id="descripcion" class="form-control" rows="3"><?= htmlspecialchars($editar['descripcion'] ?? '') ?></textarea>
    </div>

    <div class="mb-3">
      <label for="skin" class="form-label">Imagen del personaje</label>
      <input type="file" name="skin" id="skin" class="form-control">
      <?php if ($editar && $editar['skin']): ?>
        <div class="preview-imagen">
          <img src="../../<?= htmlspecialchars($editar['skin']) ?>" alt="Personaje">
        </div>
      <?php endif; ?>
    </div>

    <div class="botones">
      <button type="submit" class="btn btn-success w-50"><?= $editar ? 'Actualizar' : 'Agregar' ?></button>
      <a href="crud_personajes.php" class="btn btn-secondary w-50">Cancelar</a>
    </div>
  </form>
</div>

</body>
</html>
