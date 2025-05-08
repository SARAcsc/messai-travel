<?php
session_start();
include 'includes/connexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']) ?: null;
    $adresse = trim($_POST['adresse']) ?: null;

    // Vérifier si l'email est déjà utilisé
    $stmt = $connexion->prepare("SELECT id FROM clients WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['message'] = "Erreur : Cet email est déjà utilisé.";
        header("Location: clients.php");
        exit();
    }

    try {
        $stmt = $connexion->prepare("INSERT INTO clients (nom, email, telephone, adresse) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $telephone, $adresse]);
        $_SESSION['message'] = "Client ajouté avec succès !";
        header("Location: clients.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = "Erreur lors de l'ajout : " . $e->getMessage();
        header("Location: clients.php");
        exit();
    }
} else {
    $_SESSION['message'] = "Méthode non autorisée.";
    header("Location: clients.php");
    exit();
}
?>