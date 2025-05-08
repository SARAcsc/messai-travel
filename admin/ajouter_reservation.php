<?php
session_start();
include 'includes/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $destination_id = $_POST['destination_id'];
    $offre_id = $_POST['offre_id'];
    $date_reservation = $_POST['date_reservation'];
    $prix_total = $_POST['prix_total'];
    $statut = $_POST['statut'] ?: 'en attente';

    $stmt = $connexion->prepare("INSERT INTO reservations (client_id, destination_id, offre_id, date_reservation, prix_total, statut) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$client_id, $destination_id, $offre_id, $date_reservation, $prix_total, $statut]);

    $_SESSION['message'] = "Réservation ajoutée avec succès !";
    header("Location: reservations.php");
    exit();
}
?>