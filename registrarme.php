<?php
require_once("DB/conection.php");
$db = new Database();
$con = $db->conectar();

// Ejecutar solo si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documento = trim($_POST['documento']);
    $nombre = trim($_POST['nombre']);
    $usuario = trim($_POST['usuario']);
    $correo = trim($_POST['correo']);
    $clave = $_POST['clave'];
    $confirmar = $_POST['confirmar'];

    // --- Validaciones básicas ---
    if ($clave !== $confirmar) {
        echo "<script>alert('Las contraseñas no coinciden'); window.history.back();</script>";
        exit();
    }

    if (strlen($clave) < 6) {
        echo "<script>alert('La contraseña debe tener al menos 6 caracteres'); window.history.back();</script>";
        exit();
    }

    if (!ctype_digit($documento)) {
        echo "<script>alert('El documento debe contener solo números'); window.history.back();</script>";
        exit();
    }

    // --- Verificar si ya existe el usuario, correo o documento ---
    $check = $con->prepare("SELECT * FROM usuario WHERE username = :usuario OR correo = :correo OR id_user = :documento");
    $check->bindParam(':usuario', $usuario);
    $check->bindParam(':correo', $correo);
    $check->bindParam(':documento', $documento);
    $check->execute();

    if ($check->rowCount() > 0) {
        echo "<script>alert('El documento, usuario o correo ya están registrados'); window.history.back();</script>";
        exit();
    }

    // --- Encriptar contraseña ---
    $clave_hash = password_hash($clave, PASSWORD_BCRYPT);

    // --- Valores por defecto ---
    $id_tip_user = 2;       // jugador
    $id_niveles = 1;        // oro
    $Id_personajes = 1;     // alok
    $id_estado = 2;         // bloqueado (lo activa admin)
    $puntos = 0;
    $fecha_actual = date('Y-m-d H:i:s');

    // --- Insertar en BD ---
    $sql = $con->prepare("INSERT INTO usuario 
        (id_user, username, nombre, correo, contrasena, puntos, id_niveles, id_tip_user, Id_personajes, id_estado, ultima_conexion)
        VALUES (:id_user, :username, :nombre, :correo, :contrasena, :puntos, :id_niveles, :id_tip_user, :Id_personajes, :id_estado, :ultima_conexion)");

    $sql->bindParam(':id_user', $documento);
    $sql->bindParam(':username', $usuario);
    $sql->bindParam(':nombre', $nombre);
    $sql->bindParam(':correo', $correo);
    $sql->bindParam(':contrasena', $clave_hash);
    $sql->bindParam(':puntos', $puntos);
    $sql->bindParam(':id_niveles', $id_niveles);
    $sql->bindParam(':id_tip_user', $id_tip_user);
    $sql->bindParam(':Id_personajes', $Id_personajes);
    $sql->bindParam(':id_estado', $id_estado);
    $sql->bindParam(':ultima_conexion', $fecha_actual);

    if ($sql->execute()) {
        echo "<script>alert('Registro exitoso. Tu cuenta está pendiente de activación por el administrador.'); window.location='index.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error al registrar el usuario.'); window.history.back();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Free Fire</title>
    <link rel="stylesheet" href="CSS/estilos.css">
</head>
<body>
    <div class="contenedor-principal-registro">
        
        <div class="seccion-video-registro">
            <video autoplay loop muted>
                <source src="videos/video-registro.mp4" type="video/mp4">
                Tu navegador no soporta video.
            </video>
        </div>

        <div class="seccion-formulario-registro">
            <h1 class="titulo-registro">Registro</h1>

            <form class="formulario-registro" method="POST" action="">
                <label for="documento" class="etiqueta-registro">Identificación</label>
                <input type="text" id="documento" name="documento" class="entrada-registro" required pattern="[0-9]+" maxlength="15" placeholder="Ingrese su número de documento">

                <label for="nombre" class="etiqueta-registro">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" class="entrada-registro" required>

                <label for="usuario" class="etiqueta-registro">Usuario</label>
                <input type="text" id="usuario" name="usuario" class="entrada-registro" required>

                <label for="correo" class="etiqueta-registro">Correo</label>
                <input type="email" id="correo" name="correo" class="entrada-registro" required>

                <label for="clave" class="etiqueta-registro">Contraseña</label>
                <input type="password" id="clave" name="clave" class="entrada-registro" required>

                <label for="confirmar" class="etiqueta-registro">Confirmar Contraseña</label>
                <input type="password" id="confirmar" name="confirmar" class="entrada-registro" required>

                <button type="submit" class="boton-registro">Registrarse</button>
            </form>
        </div>
    </div>
</body>
</html>
