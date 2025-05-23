<?php
session_start();
include 'includes/connexion.php';

// Vérifier si l'utilisateur est connecté (simulation simple)
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = true; // À remplacer par un vrai système de connexion
}

// Récupérer les statistiques
$stmt = $connexion->query("SELECT COUNT(*) as total FROM reservations");
$total_reservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $connexion->query("SELECT COUNT(*) as total FROM offres");
$total_offres = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $connexion->query("SELECT COUNT(*) as total FROM clients");
$total_clients = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $connexion->query("SELECT COUNT(*) as total FROM destinations");
$total_visiteurs = $stmt->fetch(PDO::FETCH_ASSOC)['total']; // Simulation

// Récupérer les destinations populaires
$destinations = $connexion->query("SELECT * FROM destinations LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les derniers avis
$avis = $connexion->query("SELECT * FROM avis ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);

// Recherche
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_sql = $search_query ? "WHERE nom LIKE :search OR pays LIKE :search" : "";
$stmt = $connexion->prepare("SELECT * FROM destinations $search_sql LIMIT 4");
if ($search_query) {
    $stmt->execute(['search' => "%$search_query%"]);
} else {
    $stmt->execute();
}
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Message de confirmation
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Tableau de bord - Agence de voyage</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: sans-serif;
      background-color: #f8f9fa;
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
    .stat-card {
      border-radius: 10px;
      padding: 20px;
      color: #243e93;
      font-weight: bold;
      text-align: center;
    }
    .bg-custom-yellow {
      background-color: #fbcd07;
    }
    .bg-custom-blue {
      background-color: #243e93;
      color: white !important;
    }
    .card {
      border: none;
      border-radius: 10px;
    }
    .logo {
      width: 120px;
      height: auto;
      object-fit: contain;
      margin-right: 20px;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 20px;
      background-color: white;
      border-radius: 10px;
      margin-bottom: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        margin-left: 0;
      }
      .hamburger {
        display: block;
      }
    }
  </style>
</head>
<body>

  <!-- Hamburger Button -->
  <div class="hamburger">☰</div>

  <!-- Barre latérale -->
  <div class="sidebar text-white p-3">
    <h4 class="mb-4">Messai Travel</h4>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link text-white active" href="index.php">Tableau de bord</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="offres.php">Offres touristiques</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="destinations.php">Destinations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="reservations.php">Réservations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="clients.php">Clients</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="rapports.php">Rapports</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="paramaitre.php">Paramètres</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="../homepage/index.php">Déconnexion</a></li>
  </div>

  <!-- Contenu principal -->
  <div class="content">
    <!-- Barre supérieure -->
    <div class="top-bar">
      <img src="assets/logo.jpg" alt="Logo Messai Travel" class="logo" />
    
      <div>
       
          <a href="paramaitre.php" style="text-decoration: none; color: #243e93;">
    <span class="me-3">⚙️</span> 
  </a>
  
  <a href="rapports.php" style="text-decoration: none; color: #243e93;">
    <span>📊</span> 
  </a>
      </div>
    </div>

    <!-- Message de confirmation -->
    <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="stat-card bg-custom-yellow">
          <?php echo $total_reservations; ?><br>Nombre de réservations
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-custom-blue">
          <?php echo $total_offres; ?><br>Nombre d'offres
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-custom-yellow">
          <?php echo $total_clients; ?><br>Nombre de clients
        </div>
      </div>
      <div class="col-md-3">
        <div class="stat-card bg-custom-blue">
          <?php echo $total_visiteurs; ?><br>Destinations
        </div>
      </div>
    </div>

    <!-- Formulaire pour ajouter une destination -->
    <div class="card p-3 mb-4">
      <h5>Ajouter une destination</h5>
      <form action="ajouter_destination.php" method="POST">
        <div class="row g-3">
          <div class="col-md-4">
            <input type="text" name="nom" class="form-control" placeholder="Nom de la destination" required>
          </div>
          <div class="col-md-4">
            <input type="text" name="pays" class="form-control" placeholder="Pays" required>
          </div>
          <div class="col-md-2">
            <input type="number" name="offres" class="form-control" placeholder="Offres" value="0">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Ajouter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Destinations populaires + Avis -->
    <div class="row g-4">
      <div class="col-md-6">
        <div class="card p-3">
          <h5>🌍 Destinations populaires</h5>
          <table class="table mt-3">
            <thead>
              <tr>
                <th>#</th>
                <th>Destination</th>
                <th>Pays</th>
                <th>Offres</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($destinations as $index => $destination): ?>
                <tr>
                  <td><?php echo $index + 1; ?></td>
                  <td><?php echo htmlspecialchars($destination['nom']); ?></td>
                  <td><?php echo htmlspecialchars($destination['pays']); ?></td>
                  <td><?php echo $destination['offres']; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card p-3">
          <h5>⭐ Derniers avis</h5>
          <?php foreach ($avis as $avi): ?>
            <div class="border-bottom pb-2 mt-2">
              <strong><?php echo htmlspecialchars($avi['client']); ?></strong> 
              <?php echo str_repeat('★', $avi['note']) . str_repeat('☆', 5 - $avi['note']); ?>
              <p><?php echo htmlspecialchars($avi['commentaire']); ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const hamburger = document.querySelector('.hamburger');
    const sidebar = document.querySelector('.sidebar');
    const content = document.querySelector('.content');

    hamburger.addEventListener('click', () => {
      sidebar.classList.toggle('sidebar-active');
      sidebar.classList.toggle('sidebar-hidden');
      content.classList.toggle('content-full');
    });

    // Fermer le menu sur mobile quand on clique sur un lien
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