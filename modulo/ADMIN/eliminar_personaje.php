<?php
session_name("freefire_session");
session_start();
require_once(__DIR__ . "/../../DB/conection.php");

$db = new Database();
$con = $db->conectar();

// Solo admin
if (!isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $con->prepare("DELETE FROM personajes WHERE Id_personajes=?");
    $stmt->execute([$id]);
}

header("Location: crud_personajes.php");
exit;
?>
