<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID de client manquant.";
    header("Location: clients.php");
    exit();
}

$id = $_GET['id'];
try {
    $stmt = $connexion->prepare("DELETE FROM clients WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['message'] = "Client et ses réservations supprimés avec succès !";
} catch (PDOException $e) {
    $_SESSION['message'] = "Erreur lors de la suppression : " . $e->getMessage();
}

header("Location: clients.php");
exit();
?>