<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

$id_sala = $_POST['id_sala'] ?? 0;
if ($id_sala > 0) {
    // Marcar jugadores como eliminados
    $stmt = $pdo->prepare("UPDATE usuario_sala SET eliminado = 1 WHERE id_sala = ?");
    $stmt->execute([$id_sala]);

    // Opcional: registrar fin de partida
    $pdo->prepare("UPDATE partidas SET fecha_fin = NOW() WHERE id_sala = ? ORDER BY id_partida DESC LIMIT 1")->execute([$id_sala]);
}
echo "ok";
