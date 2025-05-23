<?php
session_start();
include 'includes/connexion.php';

// Vérifier si l'utilisateur est connecté (simulation)
if (!isset($_SESSION['admin'])) {
    $_SESSION['admin'] = true; // À remplacer par un vrai système de connexion
}

// Gestion de la recherche
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_sql = $search_query ? "WHERE c.nom LIKE :search OR d.nom LIKE :search OR o.titre LIKE :search" : "";

// Gestion de la pagination
$limit = 10; // Nombre de réservations par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Compter le nombre total de réservations pour la pagination
$stmt = $connexion->prepare("SELECT COUNT(*) as total FROM reservations r JOIN clients c ON r.client_id = c.id JOIN destinations d ON r.destination_id = d.id JOIN offres o ON r.offre_id = o.id $search_sql");
if ($search_query) {
    $stmt->execute(['search' => "%$search_query%"]);
} else {
    $stmt->execute();
}
$total_reservations = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_reservations / $limit);

// Récupérer les réservations
$stmt = $connexion->prepare("SELECT r.*, c.nom as client_nom, d.nom as destination_nom, o.titre as offre_titre FROM reservations r JOIN clients c ON r.client_id = c.id JOIN destinations d ON r.destination_id = d.id JOIN offres o ON r.offre_id = o.id $search_sql LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($search_query) {
    $stmt->bindValue(':search', "%$search_query%");
}
$stmt->execute();
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les clients, destinations et offres pour le formulaire d'ajout
$clients = $connexion->query("SELECT id, nom FROM clients")->fetchAll(PDO::FETCH_ASSOC);
$destinations = $connexion->query("SELECT id, nom FROM destinations")->fetchAll(PDO::FETCH_ASSOC);
$offres = $connexion->query("SELECT o.id, o.titre, o.prix, o.destination_id FROM offres o")->fetchAll(PDO::FETCH_ASSOC);

// Gestion des messages
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestion des réservations - Agence de voyage</title>
  
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
    .statut-confirmée { color: green; }
    .statut-en_attente { color: orange; }
    .statut-annulée { color: red; }
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
      <li class="nav-item"><a class="nav-link text-white" href="destinations.php">Destinations</a></li>
      <li class="nav-item"><a class="nav-link text-white active" href="reservations.php">Réservations</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="clients.php">Clients</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="#">Rapports</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="#">Paramètres</a></li>
    <li class="nav-item"><a class="nav-link text-white" href="../homepage/index.php">Déconnexion</a></li>
    </ul>
  </div>

  <!-- Contenu principal -->
  <div class="content">
    <h2 class="mb-4">Gestion des réservations</h2>

    <!-- Message de confirmation -->
    <?php if ($message): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="card p-3 mb-4">
      <h5>Ajouter une réservation</h5>
      <form action="ajouter_reservation.php" method="POST">
        <div class="row g-3">
          <div class="col-md-3">
            <select name="client_id" class="form-control" required>
              <option value="">Choisir un client</option>
              <?php foreach ($clients as $client): ?>
                <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="destination_id" id="destination_id" class="form-control" required>
              <option value="">Choisir une destination</option>
              <?php foreach ($destinations as $dest): ?>
                <option value="<?php echo $dest['id']; ?>"><?php echo htmlspecialchars($dest['nom']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <select name="offre_id" id="offre_id" class="form-control" required>
              <option value="">Choisir une offre</option>
              <?php foreach ($offres as $offre): ?>
                <option value="<?php echo $offre['id']; ?>" data-destination="<?php echo $offre['destination_id']; ?>" data-prix="<?php echo $offre['prix']; ?>">
                  <?php echo htmlspecialchars($offre['titre']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-2">
            <input type="date" name="date_reservation" class="form-control" required>
          </div>
          <div class="col-md-2">
            <input type="number" name="prix_total" id="prix_total" class="form-control" placeholder="Prix (DA)" step="0.01" required>
          </div>
          <div class="col-md-2">
            <select name="statut" class="form-control">
              <option value="en attente">En attente</option>
              <option value="confirmée">Confirmée</option>
              <option value="annulée">Annulée</option>
            </select>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Ajouter</button>
          </div>
        </div>
      </form>
    </div>

    <!-- Barre de recherche -->
    <div class="search-container mb-4">
      <form action="reservations.php" method="GET">
        <input type="text" name="search" class="form-control" placeholder="Rechercher par client, destination ou offre..." value="<?php echo htmlspecialchars($search_query); ?>">
      </form>
    </div>

    <!-- Liste des réservations -->
    <div class="card p-3">
      <h5>Liste des réservations</h5>
      <table class="table mt-3">
        <thead>
          <tr>
            <th>#</th>
            <th>Client</th>
            <th>Destination</th>
            <th>Offre</th>
            <th>Date</th>
            <th>Prix (DA)</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($reservations)): ?>
            <tr>
              <td colspan="8" class="text-center">Aucune réservation trouvée.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($reservations as $index => $reservation): ?>
              <tr>
                <td><?php echo $offset + $index + 1; ?></td>
                <td><?php echo htmlspecialchars($reservation['client_nom']); ?></td>
                <td><?php echo htmlspecialchars($reservation['destination_nom']); ?></td>
                <td><?php echo htmlspecialchars($reservation['offre_titre']); ?></td>
                <td><?php echo date('d/m/Y', strtotime($reservation['date_reservation'])); ?></td>
                <td><?php echo number_format($reservation['prix_total'], 2); ?></td>
                <td class="statut-<?php echo $reservation['statut']; ?>">
                  <?php echo ucfirst($reservation['statut']); ?>
                </td>
                <td>
                  <a href="modifier_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-warning">✏️</a>
                  <a href="supprimer_reservation.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Voulez-vous vraiment supprimer cette réservation ?');">🗑️</a>
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
                <a class="page-link" href="reservations.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>">Précédent</a>
              </li>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
              <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="reservations.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search_query); ?>"><?php echo $i; ?></a>
              </li>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
              <li class="page-item">
                <a class="page-link" href="reservations.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Suivant</a>
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

    // Filtrer les offres en fonction de la destination sélectionnée
    const destinationSelect = document.getElementById('destination_id');
    const offreSelect = document.getElementById('offre_id');
    const prixInput = document.getElementById('prix_total');

    destinationSelect.addEventListener('change', () => {
      const selectedDestination = destinationSelect.value;
      Array.from(offreSelect.options).forEach(option => {
        if (option.value === '') {
          option.style.display = 'block';
        } else {
          const destinationId = option.getAttribute('data-destination');
          option.style.display = destinationId === selectedDestination ? 'block' : 'none';
        }
      });
      offreSelect.value = ''; // Réinitialiser la sélection
      prixInput.value = ''; // Réinitialiser le prix
    });

    // Pré-remplir le prix en fonction de l'offre sélectionnée
    offreSelect.addEventListener('change', () => {
      const selectedOption = offreSelect.options[offreSelect.selectedIndex];
      const prix = selectedOption ? selectedOption.getAttribute('data-prix') : '';
      prixInput.value = prix || '';
    });
  </script>
</body>
</html>