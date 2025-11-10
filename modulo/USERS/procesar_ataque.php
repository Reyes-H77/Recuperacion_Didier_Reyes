<?php
session_name("freefire_session");
require_once("../../DB/conection.php");
session_start();

header('Content-Type: application/json');

// ✅ Usamos la sesión para identificar al atacante
if (
    !isset($_SESSION['id_user']) ||
    !isset($_POST['id_enemigo'], $_POST['id_sala'], $_POST['id_partida'], $_POST['zona'], $_POST['id_armas'])
) {
    echo json_encode(["status" => "error", "mensaje" => "Datos incompletos o sesión inválida."]);
    exit();
}

$db = new Database();
$con = $db->conectar();

$id_user = $_SESSION['id_user'];   // Atacante real desde la sesión
$id_enemigo = $_POST['id_enemigo'];
$id_sala = $_POST['id_sala'];
$id_partida = $_POST['id_partida'];
$zona = $_POST['zona'];
$id_armas = $_POST['id_armas'];

// ✅ Comprobar que el atacante esté dentro de la sala y activo
$stmt = $con->prepare("SELECT eliminado FROM usuario_sala WHERE id_user = ? AND id_sala = ?");
$stmt->execute([$id_user, $id_sala]);
$atacante = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$atacante || $atacante['eliminado'] == 1) {
    echo json_encode(["status" => "error", "mensaje" => "No puedes atacar, estás eliminado o fuera de la sala."]);
    exit();
}

// ✅ Obtener daño del arma
$stmt = $con->prepare("SELECT dano_cabeza, dano_cuerpo FROM armas WHERE id_armas = ?");
$stmt->execute([$id_armas]);
$arma = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$arma) {
    echo json_encode(["status" => "error", "mensaje" => "Arma no encontrada."]);
    exit();
}

// ✅ Calcular daño
$dano = ($zona === 'cabeza') ? $arma['dano_cabeza'] : $arma['dano_cuerpo'];

// ✅ Obtener vida actual del enemigo
$stmt = $con->prepare("SELECT vida, eliminado FROM usuario_sala WHERE id_user = ? AND id_sala = ?");
$stmt->execute([$id_enemigo, $id_sala]);
$enemigo = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$enemigo) {
    echo json_encode(["status" => "error", "mensaje" => "El enemigo no existe en esta sala."]);
    exit();
}
if ($enemigo['eliminado'] == 1) {
    echo json_encode(["status" => "error", "mensaje" => "⚠️ El enemigo ya fue eliminado."]);
    exit();
}

// ✅ Calcular nueva vida
$nuevaVida = max(0, $enemigo['vida'] - $dano);
$eliminado = ($nuevaVida <= 0) ? 1 : 0;

// ✅ Actualizar vida y estado
$stmt = $con->prepare("UPDATE usuario_sala SET vida = ?, eliminado = ? WHERE id_user = ? AND id_sala = ?");
$stmt->execute([$nuevaVida, $eliminado, $id_enemigo, $id_sala]);

// ✅ Registrar el ataque
$stmt = $con->prepare("
    INSERT INTO ataques (id_partida, id_atacante, id_victima, id_arma, zona, dano, fecha)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([$id_partida, $id_user, $id_enemigo, $id_armas, $zona, $dano]);

// ✅ Asignar puntos al atacante
$puntos = $eliminado ? 50 : (($zona === 'cabeza') ? 25 : 10);
$stmt = $con->prepare("UPDATE usuario SET puntos = puntos + ? WHERE id_user = ?");
$stmt->execute([$puntos, $id_user]);

// ✅ Mensaje final
if ($eliminado) {
    echo json_encode(["status" => "ok", "mensaje" => "💀 Has eliminado a tu enemigo. +$puntos pts"]);
} else {
    echo json_encode(["status" => "ok", "mensaje" => "🔥 Ataque exitoso. Daño: $dano (+$puntos pts)"]);
}
?>
