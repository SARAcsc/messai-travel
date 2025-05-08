<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID d'offre manquant.";
    header("Location: offres.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("DELETE FROM offres WHERE id = ?");
$stmt->execute([$id]);

$_SESSION['message'] = "Offre supprimée avec succès !";
header("Location: offres.php");
exit();
?>