<?php
session_name("freefire_session");
session_start();
require_once(__DIR__ . "/../../DB/conection.php");

$db = new Database();
$con = $db->conectar();

# 🔒 Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_user'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: ../../login.php");
    exit;
}

# 🧠 Obtener ID del usuario logueado
$id_user = $_SESSION['id_user'];

# 🎭 Traer personajes disponibles
$query = $con->query("SELECT Id_personajes, nombre, skin, descripcion FROM personajes");
$personajes = $query->fetchAll(PDO::FETCH_ASSOC);

# 👤 Obtener personaje actual del usuario

$personaje_actual_query = $con->prepare("
  SELECT Id_personajes 
  FROM usuario 
  WHERE id_user = ?
");
$personaje_actual_query->execute([$id_user]);
$personaje_actual = $personaje_actual_query->fetch(PDO::FETCH_ASSOC);

$id_personaje_actual = $personaje_actual['Id_personajes'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Seleccionar Personaje</title>
  <link rel="stylesheet" href="../../CSS/estilos.css">
</head>
<body class="fondo-personajes">
  <div class="personajes-contenedor">

    <!-- Lista -->
    <div class="personajes-lista">
      <h2 class="personajes-titulo">PERSONAJES</h2>
      <div class="personajes-grid">
        <?php foreach ($personajes as $p): ?>
          <img 
            src="../../<?= htmlspecialchars($p['skin']) ?>" 
            alt="<?= htmlspecialchars($p['nombre']) ?>" 
            class="personajes-miniatura <?= ($p['Id_personajes'] == $id_personaje_actual) ? 'personaje-actual' : '' ?>"
            data-id="<?= $p['Id_personajes'] ?>"
            data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
            data-descripcion="<?= htmlspecialchars($p['descripcion']) ?>"
            onclick="mostrarPersonaje(this)"
            >

        <?php endforeach; ?>
      </div>
    </div>

    <!-- Panel derecho -->
    <div class="personajes-detalle">
      <img src="../../<?= htmlspecialchars($personaje_actual['skin'] ?? 'IMG/default.png') ?>" alt="Personaje" class="personajes-imagen" id="skin">
      <div class="info-bloque">
  <h2 id="nombre">Selecciona un personaje</h2>
  <p id="descripcion">Haz clic en un personaje para previsualizarlo.</p>
  <button class="personajes-boton" onclick="elegirPersonaje()">ELEGIR</button>
  <button class="personajes-boton" onclick="window.location.href='index.php'">
    VOLVER AL LOBBY
  </button>
</div>

    </div>
  </div>

<script>
let personajeSeleccionado = null;

function mostrarPersonaje(elemento) {
  const id = elemento.dataset.id;
  const nombre = elemento.dataset.nombre;
  const skin = elemento.src;
  const descripcion = elemento.dataset.descripcion;

  document.getElementById('nombre').textContent = nombre;
  document.getElementById('skin').src = skin;
  document.getElementById('descripcion').textContent = descripcion;

  personajeSeleccionado = id;
}

function elegirPersonaje() {
  if (!personajeSeleccionado) {
    alert("Debes seleccionar un personaje primero.");
    return;
  }

  fetch("cambiar_personaje.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id_personaje=" + encodeURIComponent(personajeSeleccionado)
  })
  .then(res => res.text())
  .then(data => {
    alert(data);
  })
  .catch(err => console.error("Error:", err));
}
</script>

    
    
</body>
</html>
