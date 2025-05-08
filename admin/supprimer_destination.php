<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID de destination manquant.";
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("DELETE FROM destinations WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['message'] = "Destination supprimée avec succès !";
header("Location: index.php");
exit();
?>