<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

$id_sala = isset($_GET['id_sala']) ? (int)$_GET['id_sala'] : 0;
if ($id_sala <= 0) {
    echo json_encode(['html' => '<p>Error: Sala inválida</p>', 'count' => 0]);
    exit;
}
// Verificar si la partida ya fue iniciada
$check = $pdo->prepare("SELECT estado_partida FROM sala WHERE id_sala = ?");
$check->execute([$id_sala]);
$estado = $check->fetchColumn();

if ($estado == 1) {
    echo json_encode(['started' => true]);
    exit;
}


// Traer jugadores activos
$stmt = $pdo->prepare("
    SELECT u.username, n.nombre AS nivel, p.skin
    FROM usuario_sala us
    JOIN usuario u ON us.id_user = u.id_user
    JOIN niveles n ON u.id_niveles = n.id_niveles
    JOIN personajes p ON u.id_personajes = p.Id_personajes
    WHERE us.id_sala = ? AND us.eliminado = 0
");
$stmt->execute([$id_sala]);
$jugadores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar HTML
$html = '';
foreach ($jugadores as $j) {
    $skin = !empty($j['skin']) ? '../../' . $j['skin'] : '../../IMG/default_skin.png';
    $html .= '
    <div class="card-player">
        <img src="' . htmlspecialchars($skin) . '" alt="' . htmlspecialchars($j['username']) . '">
        <h6>' . htmlspecialchars($j['username']) . '</h6>
        <p style="font-size:13px;">Nivel: ' . htmlspecialchars($j['nivel']) . '</p>
    </div>';
}

$count = count($jugadores);

echo json_encode([
    'html' => $html,
    'count' => $count
]);
?>
