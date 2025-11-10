<?php
session_name("freefire_session");
session_start();
require '../../DB/conection.php';
$db = new Database();
$pdo = $db->conectar();

$nivel_usuario = $_SESSION['nivel'] ?? 1;

// 🔹 Mostrar salas según nivel
if ($nivel_usuario == 1) {
    $condicion = "WHERE s.id_niveles = 1";
} else {
    $condicion = "WHERE s.id_niveles >= 2";
}

$sql = "
 SELECT s.id_sala, m.nombre AS modo, n.nombre AS nivel, mp.nombre AS mapa, e.nombre AS estado,
        s.max_jugadores,
        (SELECT COUNT(*) FROM sala_jugadores sj WHERE sj.id_sala = s.id_sala) AS jugadores_actuales
 FROM sala s
 JOIN modos_juegos m ON s.id_modo_juegos = m.id_modo_juegos
 JOIN niveles n ON s.id_niveles = n.id_niveles
 JOIN mapa mp ON s.id_mapa = mp.id_mapa
 JOIN estado e ON s.id_estado = e.id_estado
 $condicion
 ORDER BY s.id_sala ASC
";

$stmt = $pdo->query($sql);
$salas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($salas)) {
    echo '<div class="col-12"><div class="alert alert-info bg-dark text-white p-3">No hay salas disponibles para tu nivel.</div></div>';
    exit;
}

foreach ($salas as $s) {
    $estado_texto = (int)$s['jugadores_actuales'] >= 5 ? '🟢 Iniciando partida...' : '🕒 Esperando jugadores';
    echo '<div class="col-md-4">';
    echo '<div class="salas-card h-100">';
    echo '<h5>'.htmlspecialchars($s['modo']).' (Nivel: '.htmlspecialchars($s['nivel']).')</h5>';
    echo '<p>Mapa: '.htmlspecialchars($s['mapa']).'</p>';
    echo '<p>Estado: '.htmlspecialchars($s['estado']).'</p>';
    echo '<p>Jugadores: <strong>'.(int)$s['jugadores_actuales'].'</strong>/'.(int)$s['max_jugadores'].'</p>';
    echo '<p>'.$estado_texto.'</p>';
    echo '<a href="ver_sala.php?id_sala='. (int)$s['id_sala'] .'" class="salas-btn salas-btn-primary w-100 mt-2">Entrar a la Sala</a>';
    echo '</div></div>';
}
