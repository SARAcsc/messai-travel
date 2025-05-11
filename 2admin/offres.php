<?php
session_start();
include 'includes/connexion.php';

// Vérifier si l'utilisateur est connecté (simulation)
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = true; // À remplacer par un vrai système de connexion
}

// Gestion de la recherche
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = $search_query ? "WHERE o.titre LIKE :search OR d.nom LIKE :search OR d.pays LIKE :search" : "";

// Gestion de la pagination
$limit = 10; // Nombre d'offres par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Compter le nombre total d'offres pour la pagination
$stmt = $connexion->prepare("SELECT COUNT(*) as total FROM offres o JOIN destinations d ON o.destination_id = d.id $search_sql");
if ($search_query) {
    $stmt->execute(['search' => "%$search_query%"]);
} else {
    $stmt->execute();
}
$total_offres = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_offres / $limit);

// Récupérer les offres
$stmt = $connexion->prepare("SELECT o.*, d.nom as destination_nom FROM offres o JOIN destinations d ON o.destination_id = d.id $search_sql LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($search_query) {
    $stmt->bindValue(':search', "%$search_query%");
}
$stmt->execute();
$offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les destinations pour le formulaire d'ajout
$destinations = $connexion->query("SELECT id, nom FROM destinations")->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestion des offres touristiques - Agence de voyage</title>
  
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
    .description-cell {
      max-width: 200px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
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
      .description-cell {
        max-width: 100px;
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
      <li class="nav-item"><a class="nav-link text-white active" href="offres.php">Offres touristiques</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="destinations.php">Destinations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="reservations.php">Réservations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="clients.php">Clients</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="rapports.php">Rapports</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="paramaitre.php">Paramètres</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="?logout=1">Déconnexion</a></li>
    </ul>
  </div>

  <!-- Contenu principal -->
  <div class="content">
    <h2 class="mb-4">Gestion des offres touristiques</h2>

    <!-- Message de confirmation -->
    <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="card p-3 mb-4">
      <h5>Ajouter une offre touristique</h5>
      <form action="ajouter_offre.php" method="POST">
        <div class="row g-3">
          <div class="col-md-3">
            <select name="destination_id" class="form-control" required>
              <option value="">Choisir une destination</option>
              <?php foreach ($destinations as $dest): ?>
                <option value="<?php echo $dest['id']; ?>"><?php echo htmlspecialchars($dest['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <input type="text" name="titre" class="form-control" placeholder="Titre de l'offre" required>
          </div>
          <div class="col-md-3">
            <input type="number" name="prix" class="form-control" placeholder="Prix (DA)" step="0.01" required>
          </div>
          <div class="col-md-3">
            <input type="date" name="date_validite" class="form-control" placeholder="Date de validité">
          </div>
          <div class="col-md-9">
            <textarea name="description" class="form-control" placeholder="Description" rows="3"></textarea>
          </div>
          <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Ajouter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Barre de recherche -->
    <div class="search-container mb-4">
      <form action="offres.php" method="GET">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par titre, destination ou pays..." value="<?php echo htmlspecialchars($search_query); ?>">
      </form>
    </div>

    <!-- Liste des offres -->
    <div class="card p-3">
      <h5>Liste des offres touristiques</h5>
      <table class="table mt-3">
        <thead>
          <tr>
            <th>#</th>
            <th>Titre</th>
            <th>Destination</th>
            <th>Prix (DA)</th>
            <th>Description</th>
            <th>Date de validité</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($offres)): ?>
            <tr>
              <td colspan="7" class="text-center">Aucune offre trouvée.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($offres as $index => $offre): ?>
              <tr>
                <td><?php echo $offset + $index + 1; ?></td>
                <td><?php echo htmlspecialchars($offre['titre']); ?></td>
                <td><?php echo htmlspecialchars($offre['destination_nom']); ?></td>
                <td><?php echo number_format($offre['prix'], 2); ?></td>
                <td class="description-cell"><?php echo htmlspecialchars($offre['description'] ?: '-'); ?></td>
                <td><?php echo $offre['date_validite'] ? date('d/m/Y', strtotime($offre['date_validite'])) : '-'; ?></td>
                <td>
                  <a href="modifier_offre.php?id=<?php echo $offre['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                  <a href="supprimer_offre.php?id=<?php echo $offre['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette offre ?');">🗑️</a>
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
                <a class="page-link" href="offres.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Précédent</a>
              </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="offres.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="offres.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Suivant</a>
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