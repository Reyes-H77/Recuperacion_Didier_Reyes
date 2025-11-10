<?php
session_name("freefire_session");
session_start();
require_once("../../DB/conection.php");

$db = new Database();
$con = $db->conectar();

// Verificar sesión activa
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

$id_usuario = $_SESSION['id_user'];

// Obtener datos completos del usuario
$sql = $con->prepare("
    SELECT 
        u.username,
        u.nombre,
        u.correo,
        u.puntos,
        u.id_niveles,
        n.nombre AS nivel,
        t.tipo AS tipo_usuario,
        e.nombre AS estado,
        p.nombre AS personaje,
        p.skin
    FROM usuario u
    LEFT JOIN niveles n ON u.id_niveles = n.id_niveles
    LEFT JOIN tip_user t ON u.id_tip_user = t.id_tip_user
    LEFT JOIN estado e ON u.id_estado = e.id_estado
    LEFT JOIN personajes p ON u.Id_personajes = p.Id_personajes
    WHERE u.id_user = ?
");
$sql->execute([$id_usuario]);
$usuario = $sql->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

// 🔹 Determinar imagen del nivel
$nivel = (int)$usuario['id_niveles'];
switch ($nivel) {
    case 1:
        $imagen_nivel = "../../IMG/nivel_oro.png";
        $nombre_nivel = "Oro";
        break;
    case 2:
        $imagen_nivel = "../../IMG/nivel_platino.png";
        $nombre_nivel = "Platino";
        break;
    case 3:
        $imagen_nivel = "../../IMG/nivel_diamante.png";
        $nombre_nivel = "Diamante";
        break;
    case 4:
        $imagen_nivel = "../../IMG/nivel_heroico.png";
        $nombre_nivel = "Heroico";
        break;
    case 5:
        $imagen_nivel = "../../IMG/nivel_maestro.png";
        $nombre_nivel = "Maestro";
        break;
    default:
        $imagen_nivel = "../../IMG/nivel_oro.png";
        $nombre_nivel = "Oro";
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url("../../IMG/fondo.jpg") no-repeat center center fixed;
      background-size: cover;
      color: #fff;
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      padding-top: 60px;
    }
    .perfil-card {
      background: rgba(0,0,0,0.6);
      border: 1px solid rgba(255,255,255,0.2);
      border-radius: 10px;
      padding: 25px;
      backdrop-filter: blur(4px);
    }
    .perfil-img {
      width: 180px;
      height: 180px;
      border-radius: 50%;
      object-fit: contain; /* 🔹 Cambiado para evitar recortes */
      background-color: rgba(0,0,0,0.3);
      border: 3px solid #ffc107;
      margin-bottom: 10px;
    }
    .nivel-img {
      width: 60px;
      height: 60px;
      margin-top: 10px;
    }
    .btn-volver {
      position: fixed;
      top: 20px;
      right: 20px;
      background: linear-gradient(45deg, #0d6efd, #6610f2);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 16px;
      font-weight: bold;
      box-shadow: 0 0 10px rgba(0,0,0,0.3);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn-volver:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(102,16,242,0.6);
    }
  </style>
</head>
<body>
<a href="index.php" class="btn-volver">Volver al Lobby</a>

<div class="container text-center">
  <div class="perfil-card mx-auto col-md-6 mt-5">
    <img src="../../<?= htmlspecialchars($usuario['skin'] ?? 'IMG/default.png') ?>" class="perfil-img" alt="Personaje">
    <h3 class="mt-2"><?= htmlspecialchars($usuario['username']) ?></h3>
    <p class="text-warning mb-2"><?= ucfirst($usuario['tipo_usuario']) ?></p>

    <!-- Imagen del nivel -->
    <div>
      <img src="<?= $imagen_nivel ?>" alt="<?= $nombre_nivel ?>" class="nivel-img">
      <p class="text-info"><?= $nombre_nivel ?></p>
    </div>

    <hr>
    <p><strong>Nombre completo:</strong> <?= htmlspecialchars($usuario['nombre']) ?></p>
    <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
    <p><strong>Nivel:</strong> <?= htmlspecialchars($usuario['nivel']) ?></p>
    <p><strong>Puntos:</strong> <?= htmlspecialchars($usuario['puntos']) ?></p>
    <p><strong>Estado:</strong> <?= htmlspecialchars($usuario['estado']) ?></p>
    <p><strong>Personaje actual:</strong> <?= htmlspecialchars($usuario['personaje']) ?></p>
    <hr>
  </div>
</div>
</body>
</html>
