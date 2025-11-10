<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Verificar sesión
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

$id_user = (int)$_SESSION['id_user'];

// Obtener nivel y tipo de usuario
$stmt = $pdo->prepare("SELECT id_niveles, id_tip_user FROM usuario WHERE id_user = ?");
$stmt->execute([$id_user]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) die("Usuario no encontrado.");

$nivel_user = (int)$user['id_niveles'];
$id_tip_user = (int)$user['id_tip_user'];

// ----------------------
// Crear salas base si no existen
// ----------------------
function crearSalaSiNoExiste($pdo, $cond_sql, $param) {
    $sql = "SELECT COUNT(*) FROM sala WHERE $cond_sql";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$param]);
    $count = (int)$stmt->fetchColumn();
    if ($count === 0) {
        $pdo->prepare("INSERT INTO sala (id_modo_juegos, id_niveles, id_mapa, id_estado, jugadores_actuales, max_jugadores, fecha_creacion)
                       VALUES (1, ?, 1, 1, 0, 5, NOW())")->execute([$param]);
    }
}
crearSalaSiNoExiste($pdo, "id_niveles = ?", 1);
crearSalaSiNoExiste($pdo, "id_niveles = ?", 2);

// ----------------------
// Consultar salas visibles según el nivel del jugador
// ----------------------
$sql = "
  SELECT s.*, m.nombre AS modo, mp.nombre AS mapa, n.nombre AS nivel, e.nombre AS estado,
    (SELECT COUNT(*) FROM usuario_sala us WHERE us.id_sala = s.id_sala AND (us.eliminado = 0 OR us.eliminado IS NULL)) AS jugadores_actuales_real
  FROM sala s
  JOIN modos_juegos m ON s.id_modo_juegos = m.id_modo_juegos
  JOIN mapa mp ON s.id_mapa = mp.id_mapa
  JOIN niveles n ON s.id_niveles = n.id_niveles
  JOIN estado e ON s.id_estado = e.id_estado
  WHERE s.id_niveles <= ?
  ORDER BY s.id_sala ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$nivel_user]);
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Salas - Free Fire</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: url("../../IMG/fondo.jpg") center/cover fixed; background-size: cover; color: #fff; font-family: 'Poppins', sans-serif; min-height:100vh; margin:0; }
.overlay { position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:0; }
.container{ position:relative; z-index:1; margin-top:60px; }
.sala-card { background: rgba(0,0,0,0.7); border-radius:10px; padding:16px; color:#fff; text-align:center; }
.btn-entrar { background:#ffcc00; color:#000; border:none; padding:8px 12px; border-radius:8px; text-decoration:none; display:inline-block; }
.btn-crear { background:#0d6efd; color:#fff; border:none; padding:8px 12px; border-radius:8px; text-decoration:none; }
.btn-lobby { background:#ff6600; color:#fff; border:none; padding:8px 14px; border-radius:8px; position:absolute; top:20px; right:30px; text-decoration:none; font-weight:bold; }
.btn-lobby:hover { background:#ff8533; }
.small-muted { color: #ddd; font-size:0.9em; }
</style>
</head>
<body>
<div class="overlay"></div>
<a href="index.php" class="btn-lobby">Volver al Lobby</a>
<div class="container">
  <h2 class="text-center mb-4">Salas Disponibles</h2>

  <div class="row">
    <?php if (empty($salas)): ?>
      <div class="col-12">
        <div class="sala-card">No hay salas disponibles para tu nivel.</div>
      </div>
    <?php else: ?>
      <?php foreach ($salas as $s): ?>
        <div class="col-md-4 mb-3">
          <div class="sala-card">
            <h5><?= htmlspecialchars($s['mapa']) ?> — <?= htmlspecialchars($s['modo']) ?></h5>
            <div class="small-muted">Nivel requerido: <?= htmlspecialchars($s['nivel']) ?></div>
            <p style="margin:8px 0;"><strong><?= (int)$s['jugadores_actuales_real'] ?></strong>/<?= (int)$s['max_jugadores'] ?> jugadores</p>
            <div class="small-muted"><?= htmlspecialchars($s['estado']) ?></div>
            <div class="mt-3">
              <?php if ((int)$s['jugadores_actuales_real'] >= (int)$s['max_jugadores']): ?>
                <button class="btn-entrar" disabled>Sala completa</button>
              <?php else: ?>
                <a href="ver_sala.php?id_sala=<?= (int)$s['id_sala'] ?>" class="btn-entrar">Entrar a la sala</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if ($id_tip_user === 1): ?>
    <div class="text-center mt-4">
      <a class="btn-crear" href="crear_sala.php">Crear sala</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
