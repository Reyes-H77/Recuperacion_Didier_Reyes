<?php
require_once("db/conection.php");
$db = new Database();
$con = $db->conectar();
session_start();

if (isset($_POST["enviar"])) {
    $contrasena = $_POST["new_contrasefia"];
    $contrasena_Verify = $_POST["confirmar_con"];

    // Validación de campos vacíos
    if (empty($contrasena) || empty($contrasena_Verify)) {
        echo "<script>alert('DATOS VACÍOS');</script>";
    }
    // Validación de formato (solo letras y números)
    else if (!preg_match("/^[a-zA-Z0-9]+$/", $contrasena)) {
        echo "<script>alert('La contraseña solo puede contener letras y números.');</script>";
    }
    else {
        // Encriptar la contraseña
        $encripted = password_hash($contrasena, PASSWORD_BCRYPT, array("cost" => 12));

        // Verificar que coincidan
      if ($contrasena === $contrasena_Verify) {
    $sql = $con->prepare("UPDATE usuario SET `contrasena` = :password WHERE id_user = :usuario");
    $sql->bindParam(":password", $encripted, PDO::PARAM_STR);
    $sql->bindParam(":usuario", $_SESSION['usuario'], PDO::PARAM_STR);
    $sql->execute();

    header("Location: destruir_contra.php");
    exit();
} else {
    echo "<script>alert('CONTRASEÑAS DESIGUALES');</script>";
}

    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar Contraseña | Free Fire</title>
  
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="css/style.css">
  
  <style>
    body {
      background: url("img/fondo2.jpg") no-repeat center center fixed;
      background-size: cover;
    }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center vh-100">

  <!-- Tarjeta -->
  <div class="login-card p-4">
    <div class="text-center mb-3">
      <img src="img/logo.png" alt="Logo" width="100">
      <h2 class="h5 mt-2">Cambiar Contraseña</h2>
    </div>

    <form action="" method="POST">
      <!-- Nueva contraseña -->
      <div class="mb-3">
        <label for="new_contrasefia" class="form-label">Nueva Contraseña</label>
        <input type="password" class="form-control" id="new_contrasefia" name="new_contrasefia" placeholder="Ingrese nueva contraseña" required>
      </div>

      <!-- Confirmar contraseña -->
      <div class="mb-3">
        <label for="confirmar_con" class="form-label">Confirmar Contraseña</label>
        <input type="password" class="form-control" id="confirmar_con" name="confirmar_con" placeholder="Repita la contraseña" required>
      </div>

      <!-- Botones -->
      <div class="d-grid gap-2">
        <button type="submit" class="btn btn-warning fw-bold" name="enviar">Cambiar</button>
        <a href="index.php" class="btn btn-secondary">Volver</a>
      </div>
    </form>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
