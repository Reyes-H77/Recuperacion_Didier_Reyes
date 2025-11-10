<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

// Verificar sesión
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

$id_user = (int)$_SESSION['id_user'];

// Comprobar permiso de administrador usando id_tip_user (no 'rol')
$stmt = $pdo->prepare("SELECT id_tip_user FROM usuario WHERE id_user = ?");
$stmt->execute([$id_user]);
$id_tip_user = $stmt->fetchColumn();
if ($id_tip_user === false) {
    die("Error: usuario no encontrado.");
}
if ((int)$id_tip_user !== 1) {
    http_response_code(403);
    die("Acceso denegado: necesitas permisos de administrador.");
}

// Cargar datos para selects
$modos = $pdo->query("SELECT * FROM modos_juegos ORDER BY id_modo_juegos ASC")->fetchAll(PDO::FETCH_ASSOC);
$niveles = $pdo->query("SELECT * FROM niveles ORDER BY id_niveles ASC")->fetchAll(PDO::FETCH_ASSOC);
$mapas = $pdo->query("SELECT * FROM mapa ORDER BY id_mapa ASC")->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario con validación mínima
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modo = isset($_POST['modo']) ? (int)$_POST['modo'] : 0;
    $nivel = isset($_POST['nivel']) ? (int)$_POST['nivel'] : 0;
    $mapa = isset($_POST['mapa']) ? (int)$_POST['mapa'] : 0;
    $max = isset($_POST['max_jugadores']) ? (int)$_POST['max_jugadores'] : 0;

    if ($modo <= 0) $errors[] = "Selecciona un modo válido.";
    if ($nivel <= 0) $errors[] = "Selecciona un nivel válido.";
    if ($mapa <= 0) $errors[] = "Selecciona un mapa válido.";
    if ($max < 2 || $max > 10) $errors[] = "Máximo de jugadores debe estar entre 2 y 10.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO sala (id_modo_juegos, id_niveles, id_mapa, id_estado, jugadores_actuales, max_jugadores, fecha_creacion) VALUES (?, ?, ?, 1, 0, ?, NOW())");
        $ok = $stmt->execute([$modo, $nivel, $mapa, $max]);

        if ($ok) {
            header("Location: salas.php");
            exit();
        } else {
            $errors[] = "Error al crear la sala (intenta nuevamente).";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Crear Sala - Free Fire</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: url("../../IMG/fondo.jpg") center/cover fixed; color:#fff; font-family:'Poppins',sans-serif; min-height:100vh; }
.overlay { position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:0; }
.container { position:relative; z-index:1; max-width:700px; margin-top:60px; }
.card { background:rgba(0,0,0,0.75); border-radius:12px; padding:20px; }
.btn-primary { background:#ffcc00; border:none; color:#000; }
.btn-secondary { background:#6c757d; border:none; color:#fff; }
.form-label { color: #fff; }
</style>
</head>
<body>
<div class="overlay"></div>
<div class="container">
  <div class="card">
    <h3 class="text-center mb-3">Crear Nueva Sala</h3>

    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach($errors as $e): ?>
          <div><?= htmlspecialchars($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label">Modo de Juego</label>
        <select name="modo" class="form-select" required>
          <option value="">-- Selecciona --</option>
          <?php foreach($modos as $m): ?>
            <option value="<?= (int)$m['id_modo_juegos'] ?>" <?= (isset($modo) && $modo == $m['id_modo_juegos']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($m['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Nivel Requerido</label>
        <select name="nivel" class="form-select" required>
          <option value="">-- Selecciona --</option>
          <?php foreach($niveles as $n): ?>
            <option value="<?= (int)$n['id_niveles'] ?>" <?= (isset($nivel) && $nivel == $n['id_niveles']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($n['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Mapa</label>
        <select name="mapa" class="form-select" required>
          <option value="">-- Selecciona --</option>
          <?php foreach($mapas as $mp): ?>
            <option value="<?= (int)$mp['id_mapa'] ?>" <?= (isset($mapa) && $mapa == $mp['id_mapa']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($mp['nombre']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Máximo de Jugadores</label>
        <input type="number" name="max_jugadores" class="form-control" value="<?= isset($max) ? (int)$max : 5 ?>" min="2" max="10" required>
        <div class="form-text text-white-50">Entre 2 y 10 jugadores (recomendado 5)</div>
      </div>

      <div class="d-flex justify-content-between">
        <a href="salas.php" class="btn btn-secondary">⬅ Volver</a>
        <button type="submit" class="btn btn-primary">Crear Sala</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
