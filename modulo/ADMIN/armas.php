<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';

$db = new Database();
$pdo = $db->conectar();  

if (!$pdo) {
    die("❌ Error: No se pudo conectar a la base de datos.");
}

# 🧩 Crear o actualizar arma
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id_armas'] ?? null;
    $nombre = $_POST['nombre'] ?? '';
    $dano_cabeza = $_POST['dano_cabeza'] ?? 0;
    $dano_cuerpo = $_POST['dano_cuerpo'] ?? 0;
    $id_tipo_arma = $_POST['id_tipo_arma'] ?? null;

    # 📸 Manejo de imagen subida
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = basename($_FILES['imagen']['name']);
        $rutaDestino = "../../IMG/" . $nombreArchivo;

        // Mover imagen al directorio IMG
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen = "IMG/" . $nombreArchivo;
        }
    } else {
        // Si no se subió nueva imagen y estamos editando
        $imagen = $_POST['imagen_actual'] ?? null;
    }

    if ($id) {
        // 🔄 Actualizar
        $sql = "UPDATE armas SET nombre=?, dano_cabeza=?, dano_cuerpo=?, id_tipo_arma=?, imagen=? WHERE id_armas=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $dano_cabeza, $dano_cuerpo, $id_tipo_arma, $imagen, $id]);
    } else {
        // ➕ Insertar
        $sql = "INSERT INTO armas (nombre, dano_cabeza, dano_cuerpo, id_tipo_arma, imagen) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $dano_cabeza, $dano_cuerpo, $id_tipo_arma, $imagen]);
    }

    header("Location: armas.php");
    exit;
}

# 🗑️ Eliminar arma
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $pdo->prepare("DELETE FROM armas WHERE id_armas=?")->execute([$id]);
    header("Location: armas.php");
    exit;
}

# 🧠 Cargar tipos de arma
$tiposStmt = $pdo->query("SELECT id_tipo_arma, nombre FROM tipo_armas ORDER BY nombre");
$tipos = $tiposStmt->fetchAll(PDO::FETCH_ASSOC);

# 🧠 Obtener el nivel del usuario actual
session_start();
$id_user = $_SESSION['id_user'];

$stmtNivel = $pdo->prepare("SELECT id_niveles FROM usuario WHERE id_user = ?");
$stmtNivel->execute([$id_user]);
$nivel = $stmtNivel->fetchColumn();

# 🧠 Filtrar las armas según el nivel del usuario
if ($nivel == 1) {
    // Solo puede ver armas de tipo Puño y Pistola
    $armasStmt = $pdo->prepare("
        SELECT a.*, t.nombre AS tipo_nombre 
        FROM armas a 
        JOIN tipo_armas t ON a.id_tipo_arma = t.id_tipo_arma 
        WHERE t.nombre IN ('Puño', 'Pistola')
        ORDER BY t.nombre, a.nombre
    ");
    $armasStmt->execute();
} else {
    // A partir del nivel 2 puede ver todas las armas
    $armasStmt = $pdo->query("
        SELECT a.*, t.nombre AS tipo_nombre 
        FROM armas a 
        JOIN tipo_armas t ON a.id_tipo_arma = t.id_tipo_arma 
        ORDER BY t.nombre, a.nombre
    ");
}

$armas = $armasStmt->fetchAll(PDO::FETCH_ASSOC);


# ✏️ Si se va a editar
$editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $stmt = $pdo->prepare("SELECT * FROM armas WHERE id_armas=?");
    $stmt->execute([$id]);
    $editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>CRUD Armas — Free Fire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#121212; color:#fff; }
    .card { background:rgba(0, 0, 0, 0.6); border:1px solid rgba(148, 145, 145, 0.8); } 
    .btn { border-radius:6px; }
    .form-control, .form-select { background:#222; border:none; color:#fff; }
    .form-control:focus, .form-select:focus { background:#222; color:#fff; box-shadow:0 0 0 .2rem rgba(0,123,255,.25); }
    .no-image { background:#333; color:#aaa; height:150px; display:flex; justify-content:center; align-items:center; }

    /* 🏠 Botón de regreso */
    .btn-lobby {
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(45deg, #0d6efd, #6610f2);
      color: #fff;
      font-weight: bold;
      border: none;
      padding: 10px 16px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      transition: transform 0.2s, box-shadow 0.2s;
      z-index: 1000;
    }
    .btn-lobby:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(102,16,242,0.6);
      color: #fff;
    }
  </style>
</head>
<body>
<!-- 🏠 BOTÓN DE REGRESO AL LOBBY -->
<a href="index.php" class="btn-lobby">Volver al Lobby</a>

<div class="container py-5">

  <h2 class="text-center mb-4">ARMAS</h2>

  <!-- 🧾 FORMULARIO -->
  <div class="card mb-4 p-4">
    <h4><?= $editar ? 'Editar Arma' : 'Agregar Nueva Arma' ?></h4>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id_armas" value="<?= $editar['id_armas'] ?? '' ?>">
      <input type="hidden" name="imagen_actual" value="<?= $editar['imagen'] ?? '' ?>">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" required value="<?= $editar['nombre'] ?? '' ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">Daño Cabeza</label>
          <input type="number" name="dano_cabeza" class="form-control" value="<?= $editar['dano_cabeza'] ?? 0 ?>">
        </div>

        <div class="col-md-2">
          <label class="form-label">Daño Cuerpo</label>
          <input type="number" name="dano_cuerpo" class="form-control" value="<?= $editar['dano_cuerpo'] ?? 0 ?>">
        </div>

        <div class="col-md-4">
          <label class="form-label">Tipo de Arma</label>
          <select name="id_tipo_arma" class="form-select" required>
            <option value="">Seleccionar...</option>
            <?php foreach ($tipos as $tipo): ?>
              <option value="<?= $tipo['id_tipo_arma'] ?>"
                <?= isset($editar['id_tipo_arma']) && $editar['id_tipo_arma'] == $tipo['id_tipo_arma'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($tipo['nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Imagen del arma</label>
          <input type="file" name="imagen" class="form-control">
          <?php if ($editar && $editar['imagen']): ?>
            <p class="mt-2 small">Imagen actual:</p>
            <img src="../../<?= htmlspecialchars($editar['imagen']) ?>" style="height:100px;border-radius:8px;">
          <?php endif; ?>
        </div>

        <div class="col-12">
          <button type="submit" class="btn btn-success"><?= $editar ? 'Actualizar' : 'Agregar' ?></button>
          <?php if ($editar): ?>
            <a href="armas.php" class="btn btn-secondary">Cancelar</a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>

  <!-- 📋 LISTADO -->
  <div class="row g-4">
    <?php foreach ($armas as $arma): ?>
      <div class="col-md-4">
        <div class="card h-100">
          <?php
            $imgPath = "../../" . $arma['imagen']; 
            $imgFullPath = __DIR__ . "/../../" . $arma['imagen'];

            if ($arma['imagen'] && file_exists($imgFullPath)) {
              echo '<img src="'.$imgPath.'" class="card-img-top" alt="'.htmlspecialchars($arma['nombre']).'" style="height:180px;object-fit:cover;">';
            } else {
              echo '<div class="no-image">Sin imagen</div>';
            }
          ?>

          <div class="card-body">
            <h5><?= htmlspecialchars($arma['nombre']) ?></h5>
            <p class="small text-muted">Tipo: <?= htmlspecialchars($arma['tipo_nombre']) ?></p>
            <p>
              <span class="badge bg-danger">Cabeza: <?= intval($arma['dano_cabeza']) ?></span>
              <span class="badge bg-primary">Cuerpo: <?= intval($arma['dano_cuerpo']) ?></span>
            </p>
          </div>
          <div class="card-footer d-flex justify-content-between">
            <a href="?editar=<?= $arma['id_armas'] ?>" class="btn btn-warning btn-sm">Editar</a>
            <a href="?eliminar=<?= $arma['id_armas'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta arma?')">Eliminar</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
