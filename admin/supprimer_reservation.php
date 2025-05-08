<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID de réservation manquant.";
    header("Location: reservations.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("DELETE FROM reservations WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['message'] = "Réservation supprimée avec succès !";
header("Location: reservations.php");
exit();
?>