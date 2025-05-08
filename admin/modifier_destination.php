<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID de destination manquant.";
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$id]);
$destination = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$destination) {
    $_SESSION['message'] = "Destination introuvable.";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $pays = $_POST['pays'];
    $offres = $_POST['offres'] ?? 0;

    $stmt = $connexion->prepare("UPDATE destinations SET nom = ?, pays = ?, offres = ? WHERE id = ?");
    $stmt->execute([$nom, $pays, $offres, $id]);

    $_SESSION['message'] = "Destination modifiée avec succès !";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modifier une destination - Agence de voyage</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Modifier une destination</h2>
    <form action="modifier_destination.php?id=<?php echo $id; ?>" method="POST">
      <div class="mb-3">
        <label for="nom" class="form-label">Nom de la destination</label>
        <input type="text" name="nom" class="form-control" value="<?php echo htmlspecialchars($destination['nom']); ?>" required>
      </div>
      <div class="mb-3">
        <label for="pays" class="form-label">Pays</label>
        <input type="text" name="pays" class="form-control" value="<?php echo htmlspecialchars($destination['pays']); ?>" required>
      </div>
      <div class="mb-3">
        <label for="offres" class="form-label">Nombre d'offres</label>
        <input type="number" name="offres" class="form-control" value="<?php echo $destination['offres']; ?>">
      </div>
      <button type="submit" class="btn btn-primary">Enregistrer</button>
      <a href="index.php" class="btn btn-secondary">Annuler</a>
    </form>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>