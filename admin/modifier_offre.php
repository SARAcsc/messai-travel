<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID d'offre manquant.";
    header("Location: offres.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("SELECT * FROM offres WHERE id = ?");
$stmt->execute([$id]);
$offre = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$offre) {
    $_SESSION['message'] = "Offre introuvable.";
    header("Location: offres.php");
    exit();
}

// Récupérer les destinations pour le formulaire
$destinations = $connexion->query("SELECT id, nom FROM destinations")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destination_id = $_POST['destination_id'];
    $titre = $_POST['titre'];
    $description = $_POST['description'] ?: null;
    $prix = $_POST['prix'];
    $date_validite = $_POST['date_validite'] ?: null;

    $stmt = $connexion->prepare("UPDATE offres SET destination_id = ?, titre = ?, description = ?, prix = ?, date_validite = ? WHERE id = ?");
    $stmt->execute([$destination_id, $titre, $description, $prix, $date_validite, $id]);

    $_SESSION['message'] = "Offre modifiée avec succès !";
    header("Location: offres.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modifier une offre touristique - Agence de voyage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Modifier une offre touristique</h2>
    <form action="modifier_offre.php?id=<?php echo $id; ?>" method="POST">
      <div class="mb-3">
        <label for="destination_id" class="form-label">Destination</label>
        <select name="destination_id" class="form-control" required>
          <option value="">Choisir une destination</option>
          <?php foreach ($destinations as $dest): ?>
            <option value="<?php echo $dest['id']; ?>" <?php echo $dest['id'] == $offre['destination_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($dest['nom']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="titre" class="form-label">Titre de l'offre</label>
        <input type="text" name="titre" class="form-control" value="<?php echo htmlspecialchars($offre['titre']); ?>" required>
      </div>
      <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($offre['description'] ?: ''); ?></textarea>
      </div>
      <div class="mb-3">
        <label for="prix" class="form-label">Prix (DA)</label>
        <input type="number" name="prix" class="form-control" value="<?php echo $offre['prix']; ?>" step="0.01" required>
      </div>
      <div class="mb-3">
        <label for="date_validite" class="form-label">Date de validité</label>
        <input type="date" name="date_validite" class="form-control" value="<?php echo $offre['date_validite'] ?: ''; ?>">
      </div>
      <button type="submit" class="btn btn-primary">Enregistrer</button>
      <a href="offres.php" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>