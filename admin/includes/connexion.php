<?php
$serveur = "localhost";
$utilisateur = "root"; // Par défaut, XAMPP utilise "root" sans mot de passe
$motdepasse = ""; // Laisse vide pour XAMPP
$basededonnees = "projet";

try {
    $connexion = new PDO("mysql:host=$serveur;dbname=$basededonnees", $utilisateur, $motdepasse);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connexion réussie"; // Décommente pour tester
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>