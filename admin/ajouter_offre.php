<?php
session_start();
include 'includes/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_id = $_POST['destination_id'];
    $titre = $_POST['titre'];
    $description = $_POST['description'] ?: null;
    $prix = $_POST['prix'];
    $date_validite = $_POST['date_validite'] ?: null;

    $stmt = $connexion->prepare("INSERT INTO offres (destination_id, titre, description, prix, date_validite) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$destination_id, $titre, $description, $prix, $date_validite]);

    $_SESSION['message'] = "Offre ajoutée avec succès !";
    header("Location: offres.php");
    exit();
}
?>