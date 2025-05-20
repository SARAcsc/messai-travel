<?php
session_start();
include 'includes/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $pays = $_POST['pays'];
    $offres = $_POST['offres'] ?? 0;

    $stmt = $connexion->prepare("INSERT INTO destinations (nom, pays, offres) VALUES (?, ?, ?)");
    $stmt->execute([$nom, $pays, $offres]);

    $_SESSION['message'] = "Destination ajoutée avec succès !";
    header("Location: index.php");
    exit();
}
?>