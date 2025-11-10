<?php
require 'DB/conection.php';

// ✅ Usamos el mismo nombre de sesión en todos los archivos
session_name("freefire_session");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = trim($_POST['correo']);
    $clave = trim($_POST['clave']);

    try {
        // 🔒 Recomendación: usar parámetros preparados y verificar por clave exacta
        $sql = "SELECT * FROM usuarios WHERE correo = :correo AND clave = :clave";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['correo' => $correo, 'clave' => $clave]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // 🧠 Guardar datos en sesión (usando claves consistentes)
            $_SESSION['id_user'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'] ?? 'Usuario';
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['correo'] = $usuario['correo'];
            unset($_SESSION['pregunta_correcta']); // Reiniciar estado previo

            // 🚀 Redirigir al archivo de preguntas
            header("Location: preguntas_pascal.php");
            exit();
        } else {
            echo "<script>alert('Usuario o contraseña incorrectos');window.location='index.php';</script>";
        }
    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
}
?>
