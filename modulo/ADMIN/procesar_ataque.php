<?php
// procesar_ataque.php
session_name("freefire_session");
session_start();

header('Content-Type: application/json; charset=utf-8');
require_once("../../DB/conection.php");

try {
    // Conexión
    $db = new Database();
    $con = $db->conectar();
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Validar sesión
    if (!isset($_SESSION['id_user'])) {
        echo json_encode(["status" => "error", "mensaje" => "Sesión inválida."]);
        exit();
    }
    $id_user = (int) $_SESSION['id_user']; // atacante (fuente de verdad)

    // Campos requeridos
    $required = ['id_enemigo','id_sala','id_partida','zona','id_armas'];
    foreach ($required as $r) {
        if (!isset($_POST[$r]) || $_POST[$r] === '') {
            echo json_encode(["status" => "error", "mensaje" => "Falta el campo $r."]);
            exit();
        }
    }

    // Sanitizar inputs
    $id_enemigo = (int) $_POST['id_enemigo'];
    $id_sala    = (int) $_POST['id_sala'];
    $id_partida = (int) $_POST['id_partida'];
    $zona       = trim($_POST['zona']);
    $id_armas   = (int) $_POST['id_armas'];

    if (!in_array($zona, ['cabeza','cuerpo'], true)) {
        echo json_encode(["status" => "error", "mensaje" => "Zona inválida."]);
        exit();
    }

    // --- Verificar atacante en la sala y activo ---
    $stmt = $con->prepare("SELECT vida, eliminado FROM usuario_sala WHERE id_user = ? AND id_sala = ?");
    $stmt->execute([$id_user, $id_sala]);
    $atacante = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$atacante) {
        echo json_encode(["status" => "error", "mensaje" => "No estás registrado en esta sala."]);
        exit();
    }
    if ((int)$atacante['eliminado'] === 1) {
        echo json_encode(["status" => "error", "mensaje" => "No puedes atacar: estás eliminado."]);
        exit();
    }

    // --- Obtener daño del arma ---
    $stmt = $con->prepare("SELECT dano_cabeza, dano_cuerpo FROM armas WHERE id_armas = ?");
    $stmt->execute([$id_armas]);
    $arma = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$arma) {
        echo json_encode(["status" => "error", "mensaje" => "Arma no encontrada."]);
        exit();
    }
    $dano = ($zona === 'cabeza') ? (int)$arma['dano_cabeza'] : (int)$arma['dano_cuerpo'];

    // Iniciar transacción para mantener consistencia entre operaciones
    $con->beginTransaction();

    // ==== Actualización atómica de la víctima ====
    // Resta vida y marca eliminado si llega a 0. WHERE incluye eliminado = 0 para evitar doble daño a ya muertos.
    $stmt = $con->prepare("
        UPDATE usuario_sala
        SET vida = GREATEST(0, vida - ?),
            eliminado = CASE WHEN (vida - ?) <= 0 THEN 1 ELSE eliminado END
        WHERE id_user = ? AND id_sala = ? AND eliminado = 0
    ");
    $stmt->execute([$dano, $dano, $id_enemigo, $id_sala]);

    // Si no afectó filas, determinar causa
    if ($stmt->rowCount() === 0) {
        // Revisar si la fila existe y su estado
        $chk = $con->prepare("SELECT vida, eliminado FROM usuario_sala WHERE id_user = ? AND id_sala = ?");
        $chk->execute([$id_enemigo, $id_sala]);
        $row = $chk->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $con->rollBack();
            echo json_encode(["status" => "error", "mensaje" => "El enemigo no existe en esta sala."]);
            exit();
        }
        if ((int)$row['eliminado'] === 1) {
            $con->rollBack();
            echo json_encode(["status" => "error", "mensaje" => "El enemigo ya fue eliminado."]);
            exit();
        }
        // Estado inesperado
        $con->rollBack();
        echo json_encode(["status" => "error", "mensaje" => "No se pudo aplicar el daño (estado inconsistente)."]);
        exit();
    }

    // Registrar en tabla 'ataques'
    $stmt = $con->prepare("INSERT INTO ataques (id_partida, id_atacante, id_victima, id_arma, zona, dano, fecha) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$id_partida, $id_user, $id_enemigo, $id_armas, $zona, $dano]);
    $id_ataque = $con->lastInsertId();

    // Registrar detalle_partida para atacante (dano_causado)
    $stmt = $con->prepare("INSERT INTO detalle_partida (id_user, id_partidas, id_armas, dano_causado, dano_recibido) VALUES (?, ?, ?, ?, 0)");
    $stmt->execute([$id_user, $id_partida, $id_armas, $dano]);

    // Registrar detalle_partida para victima (dano_recibido)
    $stmt = $con->prepare("INSERT INTO detalle_partida (id_user, id_partidas, id_armas, dano_causado, dano_recibido) VALUES (?, ?, ?, 0, ?)");
    $stmt->execute([$id_enemigo, $id_partida, $id_armas, $dano]);

    // Releer vida/estado de la victima
    $stmt = $con->prepare("SELECT vida, eliminado FROM usuario_sala WHERE id_user = ? AND id_sala = ?");
    $stmt->execute([$id_enemigo, $id_sala]);
    $enemigo = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$enemigo) {
        // raro, rollback
        $con->rollBack();
        echo json_encode(["status" => "error", "mensaje" => "Error interno: no se pudo leer estado de la víctima."]);
        exit();
    }

    // Asignar puntos al atacante según regla
    $eliminado_flag = ((int)$enemigo['eliminado'] === 1) ? 1 : 0;
    $puntos = $eliminado_flag ? 50 : (($zona === 'cabeza') ? 25 : 10);
    $stmt = $con->prepare("UPDATE usuario SET puntos = puntos + ? WHERE id_user = ?");
    $stmt->execute([$puntos, $id_user]);

    $con->commit();

    // Respuesta JSON para frontend
    echo json_encode([
        "status" => "ok",
        "mensaje" => $eliminado_flag ? "💀 Has eliminado a tu enemigo. +{$puntos} pts" : "🔥 Ataque exitoso. Daño: {$dano} (+{$puntos} pts)",
        "id_enemigo" => $id_enemigo,
        "nuevaVidaVictima" => (int)$enemigo['vida'],
        "eliminado" => $eliminado_flag,
        "puntos_ganados" => $puntos,
        "id_ataque" => $id_ataque
    ]);
    exit();

} catch (Throwable $e) {
    if (isset($con) && $con->inTransaction()) {
        $con->rollBack();
    }
    error_log("Error procesar_ataque.php: " . $e->getMessage());
    // En desarrollo podrías devolver el mensaje real; en producción devuelvo genérico
    echo json_encode(["status" => "error", "mensaje" => "Error interno al procesar el ataque."]);
    exit();
}
?>
