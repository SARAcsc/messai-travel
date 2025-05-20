<?php
session_start();
include 'includes/connexion.php';

if (!isset($_GET['id'])) {
    $_SESSION['message'] = "ID de client manquant.";
    header("Location: clients.php");
    exit();
}

$id = $_GET['id'];
$stmt = $connexion->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$client) {
    $_SESSION['message'] = "Client introuvable.";
    header("Location: clients.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']) ?: null;
    $adresse = trim($_POST['adresse']) ?: null;

    // Vérifier si l'email est déjà utilisé par un autre client
    $stmt = $connexion->prepare("SELECT id FROM clients WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        $error = "Cet email est déjà utilisé par un autre client.";
    } else {
        try {
            $stmt = $connexion->prepare("UPDATE clients SET nom = ?, email = ?, telephone = ?, adresse = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $telephone, $adresse, $id]);
            $_SESSION['message'] = "Client modifié avec succès !";
            header("Location: clients.php");
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Modifier un client - Messai Travel</title>
  
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome pour les icônes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
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
      padding: 12px 15px;
      font-size: 0.95rem;
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
      border-radius: 12px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      background-color: #fff;
    }
    .hamburger {
      display: none;
      font-size: 24px;
      cursor: pointer;
      padding: 10px;
      background-color: #243e93;
      color: white;
    }
    .form-control, .form-control:focus {
      border-radius: 8px;
      border: 1px solid #ced4da;
    }
    .btn-primary {
      background-color: #243e93;
      border-color: #243e93;
      border-radius: 8px;
      padding: 10px 20px;
    }
    .btn-primary:hover {
      background-color: #fbcd07;
      border-color: #fbcd07;
      color: #243e93;
    }
    .btn-secondary {
      background-color: #6c757d;
      border-color: #6c757d;
      border-radius: 8px;
      padding: 10px 20px;
    }
    .btn-secondary:hover {
      background-color: #5a6268;
      border-color: #5a6268;
    }
    .input-group-text {
      background-color: #f8f9fa;
      border-radius: 8px 0 0 8px;
    }
    .alert {
      border-radius: 8px;
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
      .form-group {
        margin-bottom: 1rem;
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
      <li class="nav-item"><a class="nav-link text-white" href="reservations.php">Réservations</a></li>
      <li class="nav-item"><a class="nav-link text-white active" href="clients.php">Clients</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="#">Rapports</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="#">Paramètres</a></li>
      <li class="nav-item"><a class="nav-link text-white" href="?logout=1">Déconnexion</a></li>
    </ul>
  </div>

  <!-- Contenu principal -->
  <div class="content">
    <h2 class="mb-4">Modifier un client</h2>

    <!-- Message d'erreur -->
    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Formulaire de modification -->
    <div class="card p-4">
      <h5 class="mb-3">Modifier les informations du client</h5>
      <form action="modifier_client.php?id=<?php echo $id; ?>" method="POST" id="modify-client-form">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input type="text" name="nom" class="form-control" placeholder="Nom complet" value="<?php echo htmlspecialchars($client['nom']); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-envelope"></i></span>
              <input type="email" name="email" class="form-control" placeholder="Email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-phone"></i></span>
              <input type="text" name="telephone" class="form-control" placeholder="Téléphone (+213)" value="<?php echo htmlspecialchars($client['telephone'] ?: ''); ?>">
            </div>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
              <input type="text" name="adresse" class="form-control" placeholder="Adresse" value="<?php echo htmlspecialchars($client['adresse'] ?: ''); ?>">
            </div>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary me-2">Modifier</button>
            <a href="clients.php" class="btn btn-secondary">Annuler</a>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Gestion du menu hamburger
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

    // Validation côté client
    document.getElementById('modify-client-form').addEventListener('submit', function(e) {
      const email = this.querySelector('input[name="email"]').value;
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Veuillez entrer un email valide.');
      }
    });
  </script>
</body>
</html>