<?php
session_name("freefire_session");
session_start();
require_once("DB/conection.php");
$db = new Database();
$con = $db->conectar();

if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Sesión no iniciada'); window.location='../../index.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pregunta = $_POST['id'];
    $respuesta_usuario = $_POST['respuesta'];

    // Obtener la respuesta correcta
    $stmt = $con->prepare("SELECT correcta FROM preguntas_pascal WHERE id = :id");
    $stmt->bindParam(':id', $id_pregunta);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $respuesta_correcta = trim($row['correcta']);

        if (strtoupper($respuesta_usuario) === strtoupper($respuesta_correcta)) {
            // ✅ Respuesta correcta → enviar al módulo correspondiente
            if ($_SESSION['id_tip_user'] == 1) {
                header("Location:modulo/ADMIN/index.php");
            } elseif ($_SESSION['id_tip_user'] == 2) {
                header("Location:modulo/USERS/index.php");
            } else {
                echo "<script>alert('Tipo de usuario desconocido'); window.location='index.php';</script>";
            }
        } else {
            // ❌ Respuesta incorrecta → volver al index (para reloguear)
            echo "<script>alert('Respuesta incorrecta. Inténtalo de nuevo.'); window.location='index.php';</script>";
        }
        exit();
    } else {
        echo "<script>alert('Pregunta no encontrada'); window.location='../../index.php';</script>";
        exit();
    }
}
?>
