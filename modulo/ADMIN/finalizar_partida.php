<?php
session_name("freefire_session");
require_once("../../DB/conection.php");
session_start();
header('Content-Type: application/json');

if (!isset($_POST['id_sala'], $_POST['id_partida'])) {
    echo json_encode(["status" => "error", "mensaje" => "Datos incompletos"]);
    exit;
}

$db = new Database();
$con = $db->conectar();

$id_sala = $_POST['id_sala'];
$id_partida = $_POST['id_partida'];

// ✅ 1. Marcar la partida como finalizada
$stmt = $con->prepare("UPDATE partidas SET finalizada = 1, fecha_fin = NOW() WHERE id_partida = ?");
$stmt->execute([$id_partida]);

// ✅ 2. Sacar a todos los jugadores de la sala
$stmt = $con->prepare("UPDATE usuario_sala SET eliminado = 1 WHERE id_sala = ?");
$stmt->execute([$id_sala]);

// ✅ 3. Actualizar el número de jugadores activos a 0
$stmt = $con->prepare("UPDATE sala SET jugadores_actuales = 0 WHERE id_sala = ?");
$stmt->execute([$id_sala]);

// ✅ 4. Guardar daño total de cada jugador (si tienes tabla ataques)
$stmt = $con->prepare("
    SELECT id_atacante, SUM(dano) AS total_dano
    FROM ataques
    WHERE id_partida = ?
    GROUP BY id_atacante
");
$stmt->execute([$id_partida]);
$danos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($danos as $d) {
    $update = $con->prepare("UPDATE usuario SET puntos = puntos + :dano WHERE id_user = :user");
    $update->execute([':dano' => $d['total_dano'], ':user' => $d['id_atacante']]);
}

echo json_encode(["status" => "ok", "mensaje" => "Partida finalizada correctamente"]);
exit;
?>
