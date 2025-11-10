<?php
session_name("freefire_session");
session_start();
require_once("../../DB/conection.php");
$db = new Database();
$con = $db->conectar();

// 🗑️ Si se pasa un ID por la URL, se elimina
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    $sql = $con->prepare("DELETE FROM usuario WHERE id_user = ?");
    $sql->execute([$idEliminar]);
    echo "<script>alert('Usuario eliminado correctamente'); window.location='usuarios.php';</script>";
    exit();
}

// 📥 EXPORTAR USUARIOS A CSV
if (isset($_GET['exportar'])) {
    header("Content-Type: text/csv; charset=utf-8");
    header("Content-Disposition: attachment; filename=usuarios_export.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ["ID", "Usuario", "Correo", "Puntos", "Estado", "Última Conexión"]);

    $query = $con->prepare("SELECT id_user, username, correo, puntos, id_estado, ultima_conexion FROM usuario ORDER BY id_user ASC");
    $query->execute();
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// 📤 IMPORTAR CSV (AGREGAR / ACTUALIZAR)
if (isset($_POST['importar_csv'])) {
    if (isset($_FILES['archivo_csv']) && $_FILES['archivo_csv']['error'] == 0) {
        $archivoTmp = $_FILES['archivo_csv']['tmp_name'];

        // Forzar lectura UTF-8 y eliminar BOM
        $contenido = file_get_contents($archivoTmp);
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido);
        $lineas = explode(PHP_EOL, $contenido);

        $primeraLinea = true;
        foreach ($lineas as $linea) {
            if (trim($linea) === '') continue;
            if ($primeraLinea) { $primeraLinea = false; continue; }

            $datos = str_getcsv($linea, ',');
            if (count($datos) < 6) continue;

            [$id_user, $username, $correo, $puntos, $id_estado, $ultima_conexion] = $datos;

            $id_user = trim($id_user);
            $username = trim($username);
            $correo = trim($correo);
            $puntos = trim($puntos);
            $id_estado = trim($id_estado);
            $ultima_conexion = str_replace('"', '', trim($ultima_conexion));

            if (empty($id_user) || empty($username) || empty($correo)) continue;

            // Verificar si ya existe
            $check = $con->prepare("SELECT COUNT(*) FROM usuario WHERE id_user = ?");
            $check->execute([$id_user]);
            $existe = $check->fetchColumn();

            if ($existe) {
                $update = $con->prepare("UPDATE usuario SET username=?, correo=?, puntos=?, id_estado=?, ultima_conexion=? WHERE id_user=?");
                $update->execute([$username, $correo, $puntos, $id_estado, $ultima_conexion, $id_user]);
            } else {
                $insert = $con->prepare("INSERT INTO usuario (id_user, username, correo, puntos, id_estado, ultima_conexion) VALUES (?, ?, ?, ?, ?, ?)");
                $insert->execute([$id_user, $username, $correo, $puntos, $id_estado, $ultima_conexion]);
            }
        }

        echo "<script>alert('Importación completada correctamente.'); window.location='usuarios.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error al subir el archivo CSV.');</script>";
    }
}

// 🔹 Consultar lista de usuarios
$query = $con->prepare("SELECT id_user, username, correo, puntos, id_estado, ultima_conexion FROM usuario ORDER BY id_user ASC");
$query->execute();
$usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

// ⚠️ Verificar usuarios inactivos (más de 10 días)
$alerta = $con->prepare("
    SELECT COUNT(*) AS inactivos 
    FROM usuario
    WHERE id_estado = 1
    AND DATEDIFF(NOW(), ultima_conexion) >= 10
");
$alerta->execute();
$data_alerta = $alerta->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Usuarios - Panel del Administrador</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Orbitron', sans-serif;
      color: #fff;
      background: url("../../IMG/fondo_lobby.jpg") no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
    }
    .overlay {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.7);
      z-index: -1;
    }
    table {
      border-radius: 10px;
      overflow: hidden;
    }
    .btn {
      font-family: 'Poppins', sans-serif;
    }
  </style>
</head>

<body>
  <div class="overlay"></div>

  <!-- 🔝 Botón de regresar -->
  <button class="btn btn-warning position-fixed top-0 end-0 m-4 fw-bold" onclick="window.location.href='index.php'">
    <i class="bi bi-arrow-left-circle me-2"></i> Volver al Lobby
  </button>

  <div class="container mt-5">
    <h1 class="text-warning text-center mb-4">USUARIOS REGISTRADOS</h1>

    <!-- 🔔 Alerta -->
    <?php if ($data_alerta['inactivos'] > 0): ?>
      <div class="alert alert-warning text-center fw-bold">
        Hay <?= $data_alerta['inactivos'] ?> usuarios con más de 10 días de inactividad.
      </div>
    <?php else: ?>
      <div class="alert alert-success text-center fw-bold">
        No hay usuarios inactivos por más de 10 días.
      </div>
    <?php endif; ?>

    <!-- 📦 Herramientas CSV -->
    <div class="d-flex justify-content-between align-items-center mb-4 bg-dark p-3 rounded">
      <form method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
        <input type="file" name="archivo_csv" accept=".csv" class="form-control form-control-sm bg-secondary text-light border-light" required>
        <button type="submit" name="importar_csv" class="btn btn-success btn-sm fw-bold">
          <i class="bi bi-upload me-1"></i> Importar CSV
        </button>
      </form>
      <a href="usuarios.php?exportar=1" class="btn btn-primary btn-sm fw-bold">
        <i class="bi bi-download me-1"></i> Descargar CSV
      </a>
    </div>

    <!-- 🧾 Tabla de usuarios -->
    <div class="table-responsive">
      <table class="table table-dark table-striped table-hover align-middle text-center">
        <thead class="table-warning text-dark">
          <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Puntos</th>
            <th>Última Conexión</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['id_user']) ?></td>
              <td><?= htmlspecialchars($u['username']) ?></td>
              <td><?= htmlspecialchars($u['correo']) ?></td>
              <td><?= htmlspecialchars($u['puntos']) ?></td>
              <td><?= htmlspecialchars($u['ultima_conexion']) ?></td>
              <td><?= $u['id_estado'] == 1 ? 'Activo' : ($u['id_estado'] == 2 ? 'Bloqueado' : 'Desconocido') ?></td>
              <td>
                <a href="actualizar_user.php?id=<?= $u['id_user'] ?>" class="btn btn-sm btn-warning">
                  <i class="bi bi-pencil-square"></i>
                </a>
                <a href="usuarios.php?eliminar=<?= $u['id_user'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">
                  <i class="bi bi-trash-fill"></i>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
