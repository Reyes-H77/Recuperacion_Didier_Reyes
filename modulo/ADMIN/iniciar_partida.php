<?php
session_name("freefire_session");
session_start();
require_once("../../DB/conection.php");
$db = new Database();
$con = $db->conectar();

$id_sala = $_POST['id_sala'] ?? null;

if (!$id_sala) {
  echo json_encode(['status' => 'error', 'mensaje' => 'No se recibió la sala.']);
  exit;
}

// Cambiar estado a "en curso"
$stmt = $con->prepare("UPDATE sala SET estado_partida = 1 WHERE id_sala = ?");
$stmt->execute([$id_sala]);

echo json_encode(['status' => 'ok', 'mensaje' => '✅ Partida iniciada para todos los jugadores.']);
?>
