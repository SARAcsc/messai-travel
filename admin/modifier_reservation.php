<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID de réservation manquant.";
    header("Location: reservations.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    $_SESSION['message'] = "Réservation introuvable.";
    header("Location: reservations.php");
    exit();
}

// Récupérer les clients, destinations et offres pour le formulaire
$clients = $connexion->query("SELECT id, nom FROM clients")->fetchAll(PDO::FETCH_ASSOC);
$destinations = $connexion->query("SELECT id, nom FROM destinations")->fetchAll(PDO::FETCH_ASSOC);
$offres = $connexion->query("SELECT o.id, o.titre, o.prix, o.destination_id FROM offres o")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'];
    $destination_id = $_POST['destination_id'];
    $offre_id = $_POST['offre_id'];
    $date_reservation = $_POST['date_reservation'];
    $prix_total = $_POST['prix_total'];
    $statut = $_POST['statut'] ?: 'en attente';

    $stmt = $connexion->prepare("UPDATE reservations SET client_id = ?, destination_id = ?, offre_id = ?, date_reservation = ?, prix_total = ?, statut = ? WHERE id = ?");
    $stmt->execute([$client_id, $destination_id, $offre_id, $date_reservation, $prix_total, $statut, $id]);

    $_SESSION['message'] = "Réservation modifiée avec succès !";
    header("Location: reservations.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modifier une réservation - Agence de voyage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Modifier une réservation</h2>
    <form action="modifier_reservation.php?id=<?php echo $id; ?>" method="POST">
      <div class="mb-3">
        <label for="client_id" class="form-label">Client</label>
        <select name="client_id" class="form-control" required>
          <option value="">Choisir un client</option>
          <?php foreach ($clients as $client): ?>
            <option value="<?php echo $client['id']; ?>" <?php echo $client['id'] == $reservation['client_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($client['nom']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="destination_id" class="form-label">Destination</label>
        <select name="destination_id" id="destination_id" class="form-control" required>
          <option value="">Choisir une destination</option>
          <?php foreach ($destinations as $dest): ?>
            <option value="<?php echo $dest['id']; ?>" <?php echo $dest['id'] == $reservation['destination_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($dest['nom']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="offre_id" class="form-label">Offre</label>
        <select name="offre_id" id="offre_id" class="form-control" required>
          <option value="">Choisir une offre</option>
          <?php foreach ($offres as $offre): ?>
            <option value="<?php echo $offre['id']; ?>" data-destination="<?php echo $offre['destination_id']; ?>" data-prix="<?php echo $offre['prix']; ?>" <?php echo $offre['id'] == $reservation['offre_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($offre['titre']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="date_reservation" class="form-label">Date de réservation</label>
        <input type="date" name="date_reservation" class="form-control" value="<?php echo $reservation['date_reservation']; ?>" required>
      </div>
      <div class="mb-3">
        <label for="prix_total" class="form-label">Prix total (DA)</label>
        <input type="number" name="prix_total" id="prix_total" class="form-control" value="<?php echo $reservation['prix_total']; ?>" step="0.01" required>
      </div>
      <div class="mb-3">
        <label for="statut" class="form-label">Statut</label>
        <select name="statut" class="form-control">
          <option value="en attente" <?php echo $reservation['statut'] == 'en attente' ? 'selected' : ''; ?>>En attente</option>
          <option value="confirmée" <?php echo $reservation['statut'] == 'confirmée' ? 'selected' : ''; ?>>Confirmée</option>
          <option value="annulée" <?php echo $reservation['statut'] == 'annulée' ? 'selected' : ''; ?>>Annulée</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Enregistrer</button>
      <a href="reservations.php" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
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