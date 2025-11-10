<?php
session_name("freefire_session");
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

session_start();
$id_user = $_SESSION['id_user'] ?? null;

$stmtNivel = $pdo->prepare("SELECT id_niveles FROM usuario WHERE id_user = ?");
$stmtNivel->execute([$id_user]);
$nivel = $stmtNivel->fetchColumn();

if ($nivel == 1) {
    $armasStmt = $pdo->prepare("
        SELECT a.*, t.nombre AS tipo_nombre 
        FROM armas a 
        JOIN tipo_armas t ON a.id_tipo_arma = t.id_tipo_arma 
        WHERE t.nombre IN ('Puño', 'Pistola')
        ORDER BY t.nombre, a.nombre
    ");
    $armasStmt->execute();
} else {
    $armasStmt = $pdo->query("
        SELECT a.*, t.nombre AS tipo_nombre 
        FROM armas a 
        JOIN tipo_armas t ON a.id_tipo_arma = t.id_tipo_arma 
        ORDER BY t.nombre, a.nombre
    ");
}

$armas = $armasStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Armas — Free Fire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#121212; color:#fff; }
    .card { background:rgba(0, 0, 0, 0.6); border:1px solid rgba(148, 145, 145, 0.8); } 
    .no-image { background:#333; color:#aaa; height:150px; display:flex; justify-content:center; align-items:center; }
    .btn-lobby {
      position: fixed; top: 20px; right: 20px;
      background: linear-gradient(45deg, #0d6efd, #6610f2);
      color: #fff; font-weight: bold; border: none;
      padding: 10px 16px; border-radius: 10px;
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
<a href="index.php" class="btn-lobby">Volver al Lobby</a>

<div class="container py-5">
  <h2 class="text-center mb-4">ARMAS DISPONIBLES</h2>

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
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</body>
</html>
