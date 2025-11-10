<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

// Obtener mapas desde la BD
$query = $pdo->query("SELECT id_mapa, nombre, descripcion, imagen FROM mapa");
$mapas = $query->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Mapas - Free Fire</title>
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
  </style>
</head>
<body>
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>Selecciona un Mapa</h2>
      <a href="index.php" class="btn btn-secondary">Volver al Lobby</a>
    </div>

    <div class="row g-4">
      <?php foreach ($mapas as $mapa): ?>
        <?php
          // Verificar si hay salas disponibles
          $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM sala WHERE id_mapa = ?");
          $stmt->execute([$mapa['id_mapa']]);
          $salas_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

          // 🧩 Construir ruta correcta a la imagen
          // Si en la BD guardas "bermuda.jpeg", esto apunta a /proyecto_freefire/IMG/bermuda.jpeg
          $imgSrc = "/proyecto_freefire/IMG/" . basename($mapa['imagen']);

          // Verificar que la imagen exista físicamente
          $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $imgSrc;
          if (!file_exists($absolutePath)) {
              $imgSrc = "/proyecto_freefire/IMG/default.jpg";
          }
        ?>
        <div class="col-md-4">
          <div class="card h-100 p-3">
            <img src="<?= htmlspecialchars($imgSrc) ?>" class="mapa-img" alt="<?= htmlspecialchars($mapa['nombre']) ?>">
            <h5><?= htmlspecialchars($mapa['nombre']) ?></h5>
            <p><?= htmlspecialchars($mapa['descripcion']) ?></p>

            <?php if ($salas_count > 0): ?>
              <a href="salas.php?id_mapa=<?= $mapa['id_mapa'] ?>" class="btn btn-primary w-100">Ver Salas</a>
            <?php else: ?>
              <button class="btn btn-secondary w-100" disabled>Mapa no disponible</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>
</html>