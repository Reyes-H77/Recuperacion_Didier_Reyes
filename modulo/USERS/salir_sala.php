<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

if (!isset($_SESSION['id_user'])) {
    http_response_code(403);
    echo json_encode(["error" => "No hay sesión activa"]);
    exit;
}

$id_user = $_SESSION['id_user'];
$id_sala = $_POST['id_sala'] ?? null;

if ($id_user && $id_sala) {
    // 1️⃣ Marcar al usuario como eliminado
    $stmt = $pdo->prepare("UPDATE usuario_sala SET eliminado = 1 WHERE id_user = ? AND id_sala = ?");
    $stmt->execute([$id_user, $id_sala]);

    // 2️⃣ Actualizar el número de jugadores activos en la sala
    $update = $pdo->prepare("
        UPDATE sala 
        SET jugadores_actuales = (
            SELECT COUNT(*) 
            FROM usuario_sala 
            WHERE id_sala = ? AND eliminado = 0
        )
        WHERE id_sala = ?
    ");
    $update->execute([$id_sala, $id_sala]);

    // 3️⃣ Respuesta clara para el navegador
    echo json_encode(["status" => "ok"]);
    exit;
}

http_response_code(400);
echo json_encode(["error" => "Datos incompletos"]);
exit;
?>
