<?php
session_name("freefire_session");
session_start();
require_once("../../DB/conection.php");
$db = new Database();
$con = $db->conectar();

session_start();

// 🔒 Verificar sesión y tipo de usuario
if (!isset($_SESSION['id_user']) || $_SESSION['id_tip_user'] != 1) {
    header("Location: ../../index.php");
    exit();
}

// ✅ Obtener el ID del usuario desde la URL
$id_user = $_GET['id'] ?? null;
if (!$id_user) {
    echo "<script>alert('ID de usuario no válido'); window.location='usuarios.php';</script>";
    exit();
}

// 🔍 Consultar los datos actuales del usuario
$query = $con->prepare("SELECT * FROM usuario WHERE id_user = ?");
$query->execute([$id_user]);
$usuario = $query->fetch(PDO::FETCH_ASSOC);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Asegúrate de incluir los archivos de PHPMailer
require '../../PHPMailer/Exception.php';
require '../../PHPMailer/PHPMailer.php';
require '../../PHPMailer/SMTP.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $correo = $_POST['correo'];
    $puntos = $_POST['puntos'];
    $id_estado = $_POST['id_estado'];

    // Guardar estado anterior
    $estado_anterior = $usuario['id_estado'];

    // Actualizar usuario
    $update = $con->prepare("UPDATE usuario 
                             SET username=?, correo=?, puntos=?, id_estado=? 
                             WHERE id_user=?");
    $update->execute([$username, $correo, $puntos, $id_estado, $id_user]);

    // ✅ Actualizar el nivel automáticamente según los puntos
    require_once '../../DB/functions/niveles.php';
    actualizarNivelUsuario($con, $id_user);


    // Enviar correo si pasó a activo
    if ($estado_anterior != 1 && $id_estado == 1) {
        $mail = new PHPMailer(true);
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; // Cambia por tu SMTP
            $mail->SMTPAuth   = true;
            $mail->Username   = 'je3037913@gmail.com'; // Tu correo
            $mail->Password   = 'blwh nirb whgh gpoz';         // Contraseña
            $mail->SMTPSecure = 'tls';                   // tls o ssl
            $mail->Port       = 587;                     // 587 para tls, 465 para ssl

            // Destinatario y remitente
            $mail->setFrom('je3037913@gmail.com', 'FREE FIRE');
            $mail->addAddress($correo, $username);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = '¡Tu cuenta ha sido activada!';
            $mail->Body    = "Hola <b>$username</b>,<br><br>Tu cuenta ha sido activada correctamente. Ahora puedes iniciar sesión y disfrutar de todos los beneficios.<br><br>Saludos,<br>El equipo";
            $mail->AltBody = "Hola $username,\n\nTu cuenta ha sido activada correctamente. Ahora puedes iniciar sesión y disfrutar de todos los beneficios.\n\nSaludos,\nEl equipo";

            $mail->send();
            // echo 'Correo enviado correctamente';
        } catch (Exception $e) {
            // echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
    }

    echo "<script>alert('Usuario actualizado correctamente'); window.location='usuarios.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light p-5">

  <div class="container mt-5">
    <h2 class="text-warning text-center mb-4">Editar Usuario</h2>

    <form method="POST" class="bg-secondary p-4 rounded shadow">
      <div class="mb-3">
        <label class="form-label">Usuario</label>
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($usuario['username']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Puntos</label>
        <input type="number" name="puntos" class="form-control" value="<?= htmlspecialchars($usuario['puntos']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Estado</label>
        <select name="id_estado" class="form-select">
          <option value="1" <?= $usuario['id_estado'] == 1 ? 'selected' : '' ?>>Activo 🟢</option>
          <option value="2" <?= $usuario['id_estado'] == 2 ? 'selected' : '' ?>>Bloqueado 🔴</option>
        </select>
      </div>

      <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-warning fw-bold">Guardar Cambios</button>
        <a href="usuarios.php" class="btn btn-outline-light">Volver</a>
      </div>
    </form>
  </div>

</body>
</html>
