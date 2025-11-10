<?php
session_name("freefire_session");
session_start();
require_once(__DIR__ . "/../../DB/conection.php");
$db = new Database();
$con = $db->conectar();

$id_user = $_SESSION['id_user'] ?? 1110495789;
$id_personaje = $_POST['id_personaje'] ?? null;

if (!$id_personaje) {
  echo "❌ No se recibió el personaje.";
  exit;
}

$stmt = $con->prepare("UPDATE usuario SET Id_personajes = :idp WHERE id_user = :idu");
$stmt->bindParam(":idp", $id_personaje);
$stmt->bindParam(":idu", $id_user);

if ($stmt->execute()) {
  echo "✅ Personaje cambiado correctamente.";
} else {
  echo "❌ Error al actualizar el personaje.";
}
