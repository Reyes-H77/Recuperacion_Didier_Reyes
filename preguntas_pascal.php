<?php
session_name("freefire_session");
session_start();
require_once("DB/conection.php");
$db = new Database();
$con = $db->conectar();

// ✅ Verificar sesión
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Por favor inicia sesión para continuar'); window.location='../../index.php';</script>";
    exit();
}

// ✅ Obtener una pregunta al azar
$query = $con->prepare("SELECT * FROM preguntas_pascal ORDER BY RAND() LIMIT 1");
$query->execute();
$pregunta = $query->fetch(PDO::FETCH_ASSOC);

if (!$pregunta) {
    echo "<script>alert('No hay preguntas registradas en la base de datos'); window.location='../../index.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pregunta de Validación | Free Fire</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: url('../../img/fondo.jpg') no-repeat center center fixed;
      background-size: cover;
      color: #fff;
    }
    .pregunta-card {
      background: rgba(0,0,0,0.8);
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 0 20px rgba(255,193,7,0.5);
      max-width: 600px;
      margin: 80px auto;
    }
    .btn-warning {
      font-weight: bold;
    }
  </style>
</head>
<body>

  <div class="pregunta-card text-center">
    <h3 class="mb-4 text-warning">Pregunta de Validación</h3>
    <form action="verificar_respuesta.php" method="POST">
      <input type="hidden" name="id" value="<?php echo $pregunta['id']; ?>">

      <p class="fs-5"><?php echo htmlspecialchars($pregunta['pregunta']); ?></p>

      <div class="text-start mt-3">
        <?php if (!empty($pregunta['opcion_a'])): ?>
          <label><input type="radio" name="respuesta" value="A" required> <?php echo htmlspecialchars($pregunta['opcion_a']); ?></label><br>
        <?php endif; ?>

        <?php if (!empty($pregunta['opcion_b'])): ?>
          <label><input type="radio" name="respuesta" value="B"> <?php echo htmlspecialchars($pregunta['opcion_b']); ?></label><br>
        <?php endif; ?>

        <?php if (!empty($pregunta['opcion_c'])): ?>
          <label><input type="radio" name="respuesta" value="C"> <?php echo htmlspecialchars($pregunta['opcion_c']); ?></label><br>
        <?php endif; ?>

        <?php if (!empty($pregunta['opcion_d'])): ?>
          <label><input type="radio" name="respuesta" value="D"> <?php echo htmlspecialchars($pregunta['opcion_d']); ?></label><br>
        <?php endif; ?>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-warning w-100">Validar Respuesta</button>
      </div>
    </form>
  </div>

</body>
</html>
