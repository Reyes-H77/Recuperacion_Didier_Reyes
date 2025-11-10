<?php
session_name("freefire_session");
session_start();
require_once("../../DB/conection.php");

$db = new Database();
$con = $db->conectar();

// Verificar sesión
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../index.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$id_sala = $_GET['id_sala'] ?? $_SESSION['id_sala_actual'] ?? null;

if (!$id_sala) {
    die("No se encontró la sala activa.");
}

// Guardar sala en sesión
$_SESSION['id_sala_actual'] = $id_sala;

// ✅ Verificar participación del usuario en la sala
$stmt = $con->prepare("SELECT eliminado FROM usuario_sala WHERE id_sala = :s AND id_user = :u");
$stmt->execute([':s' => $id_sala, ':u' => $id_user]);
$participa = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$participa) {
    echo "<script>alert('⚠️ No estás registrado en esta sala.'); window.location.href='salas.php';</script>";
    exit();
}

// ✅ Crear o recuperar partida
$stmt = $con->prepare("SELECT id_partida, fecha_inicio FROM partidas WHERE id_sala = :id ORDER BY fecha_inicio DESC LIMIT 1");
$stmt->execute([':id' => $id_sala]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);

if ($partida) {
    $id_partida = $partida['id_partida'];
    $fecha_partida = $partida['fecha_inicio'];
} else {
    $stmt = $con->prepare("INSERT INTO partidas (id_sala, fecha_inicio) VALUES (:id, NOW())");
    $stmt->execute([':id' => $id_sala]);
    $id_partida = $con->lastInsertId();
    $fecha_partida = date('Y-m-d H:i:s');
}

// ✅ Datos de la sala, nivel y modo
$stmt = $con->prepare("
    SELECT s.id_niveles, n.nombre AS nivel_nombre, mj.nombre AS modo_nombre
    FROM sala s
    JOIN niveles n ON s.id_niveles = n.id_niveles
    JOIN modos_juegos mj ON s.id_modo_juegos = mj.id_modo_juegos
    WHERE s.id_sala = :s
");
$stmt->execute([':s' => $id_sala]);
$sala = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Datos del jugador actual
$stmt = $con->prepare("
    SELECT u.id_user, u.username, u.puntos, us.vida, us.eliminado, u.id_niveles
    FROM usuario u 
    JOIN usuario_sala us ON u.id_user = us.id_user
    WHERE us.id_sala = :s AND u.id_user = :u
");
$stmt->execute([':s' => $id_sala, ':u' => $id_user]);
$yo = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Enemigos (los demás jugadores activos)
$stmt = $con->prepare("
    SELECT u.id_user, u.username, u.puntos, us.vida, us.eliminado
    FROM usuario_sala us
    JOIN usuario u ON us.id_user = u.id_user
    WHERE us.id_sala = :s AND us.id_user != :u AND us.eliminado = 0
");
$stmt->execute([':s' => $id_sala, ':u' => $id_user]);
$enemigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Armas según nivel
$nivelJugador = (int)($yo['id_niveles'] ?? 1);
if ($nivelJugador == 1) {
    $queryArmas = $con->query("SELECT * FROM armas WHERE id_tipo_arma IN (1,2)");
} elseif ($nivelJugador == 2) {
    $queryArmas = $con->query("SELECT * FROM armas WHERE id_tipo_arma IN (1,2,3,4,5)");
} else {
    $queryArmas = $con->query("SELECT * FROM armas");
}
$armas = $queryArmas->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>🔥 Sala de Batalla</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
  background: url("../../IMG/fondo.jpg") center/cover no-repeat fixed;
  color:white; font-family:'Poppins',sans-serif;
}
.overlay { background:rgba(0,0,0,0.7); min-height:100vh; padding-bottom:40px; }
.player-info { position:fixed; top:10px; left:10px; background:rgba(30,30,30,0.9); border:2px solid orange; border-radius:10px; padding:15px; width:260px; }
.health-bar { width:100%; height:12px; background:#333; border-radius:6px; overflow:hidden; margin-top:6px; }
.health-inner { height:100%; background:lime; transition:width .5s; }
.enemy-zone {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    max-width: 1000px;
    margin: 120px auto 0 auto;
    padding: 10px;
}
.enemy-card {
    background: rgba(30,30,30,0.85);
    border: 2px solid orange;
    border-radius: 12px;
    text-align: center;
    padding: 15px;
    transition: transform .2s;
    box-shadow: 0 0 10px rgba(255,140,0,0.3);
    cursor: pointer;
}
.enemy-card:hover {
    transform: scale(1.05);
    box-shadow: 0 0 15px rgba(255,165,0,0.6);
}
.enemy-card.selected {
    border: 2px solid red;
    box-shadow: 0 0 15px red;
}
.countdown { position:fixed; top:10px; right:20px; background:rgba(30,30,30,0.9); padding:10px 16px; border-radius:10px; border:2px solid orange; font-weight:bold; color:orange; }
</style>
</head>
<body>
<div class="overlay">
  <div class="player-info">
    <h5><?= htmlspecialchars($yo['username']) ?> 🧍‍♂️</h5>
    <div class="health-bar"><div class="health-inner" style="width:<?= $yo['vida'] ?>%"></div></div>
    <p>Puntos: <?= $yo['puntos'] ?></p>
    <p>Nivel: <?= htmlspecialchars($sala['nivel_nombre']) ?></p>
    <p>Modo: <?= htmlspecialchars($sala['modo_nombre']) ?></p>
  </div>

  <div class="enemy-zone">
    <?php foreach ($enemigos as $e): ?>
    <div class="enemy-card <?= $e['eliminado'] ? 'opacity-50' : '' ?>" data-id="<?= $e['id_user'] ?>" data-elim="<?= $e['eliminado'] ?>">
      <h5 style="color:orange;"><?= htmlspecialchars($e['username']) ?></h5>
      <div class="health-bar"><div class="health-inner" style="width:<?= $e['vida'] ?>%"></div></div>
      <p>Puntos: <?= $e['puntos'] ?></p>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="text-center mt-4">
    <form id="formAtaque">
      <input type="hidden" name="id_user" value="<?= $id_user ?>">
      <input type="hidden" name="id_enemigo" id="id_enemigo">
      <input type="hidden" name="id_sala" value="<?= $id_sala ?>">
      <input type="hidden" name="id_partida" value="<?= $id_partida ?>">

      <select id="zona" name="zona" class="form-select d-inline w-auto" required>
        <option value="">-- Zona --</option>
        <option value="cabeza">Cabeza 🎯</option>
        <option value="cuerpo">Cuerpo 💢</option>
      </select>

      <select name="id_armas" class="form-select d-inline w-auto" required>
        <option value="">-- Arma --</option>
        <?php foreach ($armas as $a): ?>
          <option value="<?= $a['id_armas'] ?>">
            <?= $a['nombre'] ?> (<?= $a['dano_cabeza'] ?>/<?= $a['dano_cuerpo'] ?>)
          </option>
        <?php endforeach; ?>
      </select>

      <button class="btn btn-danger" id="btnAtacar" disabled>ATACAR 🔫</button>
    </form>
  </div>

  <div class="countdown" id="countdownVisual">⏱️ 5:00</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById('formAtaque');
  const btnAtacar = document.getElementById('btnAtacar');
  const zona = document.getElementById('zona');
  const armaSelect = form.querySelector('select[name="id_armas"]');
  const idEnemigo = document.getElementById('id_enemigo');
  const enemyCards = document.querySelectorAll('.enemy-card');
  console.log("Enemy cards detectadas:", enemyCards.length);

  // --- SELECCIÓN DE ENEMIGO ---
  enemyCards.forEach(card => {
    card.addEventListener("click", () => {
      if (parseInt(card.dataset.elim) === 1) return; // no atacar eliminados
      enemyCards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      idEnemigo.value = card.dataset.id;
      console.log("Seleccionado enemigo:", idEnemigo.value);
      toggleAttackButton();
    });
  });

  // --- ACTIVAR BOTÓN ---
  function toggleAttackButton() {
    const listo = idEnemigo.value && zona.value && armaSelect.value;
    btnAtacar.disabled = !listo;
    btnAtacar.style.opacity = listo ? "1" : "0.5";
  }

  zona.addEventListener("change", toggleAttackButton);
  armaSelect.addEventListener("change", toggleAttackButton);

  // --- ENVÍO DEL ATAQUE ---
  form.addEventListener("submit", async e => {
    e.preventDefault();
    if (btnAtacar.disabled) return;
    btnAtacar.disabled = true;
    btnAtacar.textContent = "Atacando...";
    const data = new FormData(form);

    try {
      const res = await fetch("procesar_ataque.php", { method: "POST", body: data });
      const json = await res.json();
      console.log("Respuesta ataque:", json);
      alert(json.mensaje || "Ataque realizado");
      location.reload();
    } catch (err) {
      console.error("Error en ataque:", err);
      alert("Error al enviar el ataque.");
    } finally {
      btnAtacar.textContent = "ATACAR 🔫";
      btnAtacar.disabled = false;
    }
  });

  // --- CONTADOR ---
  let tiempo = 300;
  const cd = document.getElementById("countdownVisual");
  const timer = setInterval(() => {
    const m = Math.floor(tiempo / 60);
    const s = tiempo % 60;
    cd.textContent = `⏱️ ${m}:${s < 10 ? "0" : ""}${s}`;
    tiempo--;

    if (tiempo < 0) {
      clearInterval(timer);
      alert("⏰ ¡Tiempo terminado!");
      fetch("finalizar_partida.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id_sala=<?= $id_sala ?>&id_partida=<?= $id_partida ?>`
      }).then(() => location.href = "salas.php");
    }
  }, 1000);
});
</script>
</body>
</html>
