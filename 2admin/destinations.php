<?php
session_start();
include 'includes/connexion.php';

// Vérifier si l'utilisateur est connecté (simulation)
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = true; // À remplacer par un vrai système de connexion
}

// Gestion de la recherche
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = $search_query ? "WHERE nom LIKE :search OR pays LIKE :search" : "";

// Gestion de la pagination
$limit = 10; // Nombre de destinations par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Compter le nombre total de destinations pour la pagination
$stmt = $connexion->prepare("SELECT COUNT(*) as total FROM destinations $search_sql");
if ($search_query) {
    $stmt->execute(['search' => "%$search_query%"]);
} else {
    $stmt->execute();
}
$total_destinations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_destinations / $limit);

// Récupérer les destinations
$stmt = $connexion->prepare("SELECT * FROM destinations $search_sql LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($search_query) {
    $stmt->bindValue(':search', "%$search_query%");
}
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestion des destinations - Agence de voyage</title>
  
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
    .card {
      border: none;
      border-radius: 10px;
    }
    .search-container {
      max-width: 500px;
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
      <li class="nav-item"><a class="nav-link text-white" href="index.php">Tableau de bord</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="offres.php">Offres touristiques</a></li>
      <li class="nav-item"><a class="nav-link text-white active" href="destinations.php">Destinations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="reservations.php">Réservations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="clients.php">Clients</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="rapports.php">Rapports</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="paramaitre.php">Paramètres</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="?logout=1">Déconnexion</a></li>
    </ul>
  </div>

  <!-- Contenu principal -->
  <div class="content">
    <h2 class="mb-4">Gestion des destinations</h2>

    <!-- Message de confirmation -->
    <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
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

    <!-- Barre de recherche -->
    <div class="search-container mb-4">
      <form action="destinations.php" method="GET">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou pays..." value="<?php echo htmlspecialchars($search_query); ?>">
      </form>
    </div>

    <!-- Liste des destinations -->
    <div class="card p-3">
      <h5>Liste des destinations</h5>
      <table class="table mt-3">
        <thead>
          <tr>
            <th>#</th>
            <th>Destination</th>
            <th>Pays</th>
            <th>Offres</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($destinations)): ?>
            <tr>
              <td colspan="5" class="text-center">Aucune destination trouvée.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($destinations as $index => $destination): ?>
              <tr>
                <td><?php echo $offset + $index + 1; ?></td>
                <td><?php echo htmlspecialchars($destination['nom']); ?></td>
                <td><?php echo htmlspecialchars($destination['pays']); ?></td>
                <td><?php echo $destination['offres']; ?></td>
                <td>
                  <a href="modifier_destination.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                  <a href="supprimer_destination.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette destination ?');">🗑️</a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
          <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="destinations.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Précédent</a>
              </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="destinations.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="destinations.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Suivant</a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
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