<?php
// Simuler des données utilisateur (à remplacer par la récupération depuis la BDD)
$nom = "Messai";
$prenom = "Yacine";
$email = "messai@example.com";
$motDePasse = "motdepasse123"; // À NE PAS afficher en production
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Paramètres du profil</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Bootstrap & Font Awesome -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    body {
      font-family: sans-serif;
      background-color: #f4f4f4;
      margin: 0;
    }
    .sidebar {
      width: 220px;
      background-color: #243e93;
      min-height: 100vh;
      color: white;
      position: fixed;
      top: 0;
      left: 0;
      transition: transform 0.3s ease;
      z-index: 1000;
    }
    .sidebar-hidden {
      transform: translateX(-220px);
    }
    .sidebar a {
      color: white;
      text-decoration: none;
      display: block;
      padding: 10px 15px;
    }
    .sidebar a:hover {
      background-color: #fbcd07;
      color: #243e93;
    }
    .content {
      margin-left: 220px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }
    .content-full {
      margin-left: 0;
    }
    .top-bar {
      background: white;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 40px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }
    .top-bar img {
      height: 60px;
    }
    .top-bar h1 {
      font-size: 24px;
      margin: 0;
      color: #333;
    }
    .hamburger {
      display: none;
      font-size: 24px;
      cursor: pointer;
      padding: 10px;
    }
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-220px);
      }
      .sidebar-active {
        transform: translateX(0);
      }
      .content {
        margin-left: 50px;
      }
      .hamburger {
        display: block;
        margin-left: 20px;
        margin-top: 10px;
      }
    }
    .container {
      max-width: 600px;
      background: white;
      margin: auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    label {
      margin-top: 15px;
      font-weight: bold;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .show-password {
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    button {
      margin-top: 25px;
      padding: 12px 25px;
      background-color: #243e93;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
    }
    button:hover {
      background-color: #fbcd07;
      color: #243e93;
    }
  </style>
</head>
<body>

  <div class="hamburger">☰</div>

  <!-- Sidebar -->
  <div class="sidebar text-white p-3">
    <h4 class="mb-4">Messai Travel</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link text-white" href="index.php">Tableau de bord</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="offres.php">Offres touristiques</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="destinations.php">Destinations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="reservations.php">Réservations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="clients.php">Clients</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="rapports.php">Rapports</a></li>
      <li class="nav-item"><a class="nav-link text-white active" href="paramaitre.php">Paramètres</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="?logout=1">Déconnexion</a></li>
    </ul>
  </div>

  <!-- Contenu -->
  <div class="content">
    <div class="top-bar">
      <img src="assets/logo.jpg" alt="Logo">
      <h1>Paramètres du profil</h1>
    </div>

    <div class="container">
      <h2 class="text-center">Modifier les informations</h2>
      <form action="sauvegarder_profil.php" method="POST">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="password">Mot de passe actuel</label>
        <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($motDePasse); ?>" required>

        <div class="show-password">
          <input type="checkbox" onclick="togglePassword()"> Afficher le mot de passe
        </div>

        <!-- Bouton de soumission -->
        <button type="button" id="enregistrerBtn">Enregistrer</button>
      </form>

      <!-- Message d'erreur -->
      <div id="messageErreur" style="display: none; padding: 15px; background-color: red; color: white; text-align: center; border-radius: 5px; margin-top: 20px;">
        Modification non autorisée
      </div>
    </div>
    
  </div>

  <!-- Scripts -->
  <script>
    function togglePassword() {
      const input = document.getElementById("password");
      input.type = input.type === "password" ? "text" : "password";
    }

    document.getElementById("enregistrerBtn").addEventListener("click", function(event) {
      event.preventDefault();  // Empêche la soumission du formulaire

      // Affiche le message d'erreur
      afficherErreur();
    });

    // Fonction pour afficher le message d'erreur
    function afficherErreur() {
      const messageErreur = document.getElementById("messageErreur");
      messageErreur.style.display = "block";  // Affiche le message d'erreur
      
      // Optionnel : cache le message après 5 secondes
      setTimeout(() => {
        messageErreur.style.display = "none";
      }, 5000);
    }

    const hamburger = document.querySelector('.hamburger');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');

    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-active');
      sidebar.classList.toggle('sidebar-hidden');
      content.classList.toggle('content-full');
    });

    document.querySelectorAll('.sidebar a').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
          sidebar.classList.remove('sidebar-active');
          sidebar.classList.add('sidebar-hidden');
          content.classList.add('content-full');
        }
      });
    });
  </script>

</body>
</html>
