<?php
session_start();

// Connexion à la base de données
try {
    $dsn = "mysql:host=localhost;dbname=projet;charset=utf8";
    $username = "root"; // Remplace par ton utilisateur MySQL (par défaut "root" sur XAMPP)
    $password = ""; // Remplace par ton mot de passe MySQL (par défaut vide sur XAMPP)
    $connexion = new PDO($dsn, $username, $password);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Traitement des formulaires
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            // Traitement de la connexion
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            if (empty($email) || empty($password)) {
                $message = "Veuillez remplir tous les champs.";
                $message_type = "danger";
            } else {
                $stmt = $connexion->prepare("SELECT * FROM clients WHERE email = ?");
                $stmt->execute([$email]);
                $client = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($client && $client['password'] === $password) {
                    $_SESSION['client_id'] = $client['id'];
                    $_SESSION['client_nom'] = $client['nom'];
                    $_SESSION['is_admin'] = $client['is_admin'];
                    $message = "Connexion réussie ! Bienvenue, " . htmlspecialchars($client['nom']) . ".";
                    $message_type = "success";
                    
                    // Redirection selon le rôle
                    if ($client['is_admin'] == 1) {
                        header("Location: /messai-travel/admin/index.php");
                    } else {
                        header("Location: /messai-travel/homepage/index.php");
                    }
                    exit();
                } else {
                    $message = "Email ou mot de passe incorrect.";
                    $message_type = "danger";
                }
            }
        } elseif ($_POST['action'] === 'signup') {
            // Traitement de l'inscription
            $nom = trim($_POST['nom']);
            $email = trim($_POST['email']);
            $telephone = trim($_POST['telephone']);
            $adresse = trim($_POST['adresse']);
            $password = trim($_POST['password']);

            if (empty($nom) || empty($email) || empty($telephone) || empty($adresse) || empty($password)) {
                $message = "Veuillez remplir tous les champs.";
                $message_type = "danger";
            } else {
                $stmt = $connexion->prepare("SELECT id FROM clients WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $message = "Cet email est déjà utilisé.";
                    $message_type = "danger";
                } else {
                    try {
                        $stmt = $connexion->prepare("INSERT INTO clients (nom, email, telephone, adresse, password, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
                        $stmt->execute([$nom, $email, $telephone, $adresse, $password]);
                        $message = "Inscription réussie ! Veuillez vous connecter.";
                        $message_type = "success";
                    } catch (PDOException $e) {
                        $message = "Erreur lors de l'inscription : " . $e->getMessage();
                        $message_type = "danger";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Connexion - Messai Travel</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      justify-content: center;
      align-items: center;
      background: linear-gradient(rgba(255,255,255,0.9), rgba(255,255,255,0.9)), url('images/hero.jpg') center/cover no-repeat;
    }
    .login-container {
      width: 100%;
      max-width: 400px;
      margin: 20px;
    }
    .tab-links {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }
    .tab-link {
      color: var(--bleu);
      font-weight: 500;
      margin: 0 15px;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    .tab-link:hover,
    .tab-link.active {
      color: var(--jaune);
    }
    .form-group {
      margin-bottom: 15px;
    }
    .navbar-brand {
      font-size: 1.5rem;
      font-weight: 600;
      color: var(--jaune);
    }
    .navbar-brand span {
      color: var(--bleu);
    }
    .navbar {
      width: 100%;
      padding: 10px 20px;
    }
    .navbar .container-fluid {
      padding-left: 20px;
      padding-right: 20px;
    }
    .alert {
      border-radius: 6px;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg w-100">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">Messai<span>Travel</span></a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <div class="navbar-nav ms-auto">
          <a class="nav-link" href="index.php">Accueil</a>
          <a class="nav-link" href="destinations.php">Destinations</a>
          <a class="nav-link" href="offres.php">Offres</a>
          <a class="nav-link" href="contact.php">Contact</a>
          <a class="nav-link active" href="login.php">Connexion</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Login/Sign-Up Container -->
  <div class="login-container card p-4">
    <?php if ($message): ?>
      <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <h2 class="section-title" id="login-title">Connexion</h2>
    <div class="tab-links">
      <a href="#" class="tab-link active" onclick="toggleForm('login')">Connexion</a>
      <a href="#" class="tab-link" onclick="toggleForm('signup')">S'inscription</a>
    </div>
    <form id="login-form" action="login.php" method="POST" style="display: block;">
      <input type="hidden" name="action" value="login">
      <div class="form-group">
        <label for="login-email" class="form-label">Email</label>
        <input type="email" class="form-control" id="login-email" name="email" placeholder="Email" required>
      </div>
      <div class="form-group">
        <label for="login-password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="login-password" name="password" placeholder="Mot de passe" required>
      </div>
      <button type="submit" class="btn btn-yellow">Se connecter</button>
    </form>
    <form id="signup-form" action="login.php" method="POST" style="display: none;">
      <input type="hidden" name="action" value="signup">
      <div class="form-group">
        <label for="signup-nom" class="form-label">Nom complet</label>
        <input type="text" class="form-control" id="signup-nom" name="nom" placeholder="Nom complet" required>
      </div>
      <div class="form-group">
        <label for="signup-email" class="form-label">Email</label>
        <input type="email" class="form-control" id="signup-email" name="email" placeholder="Email" required>
      </div>
      <div class="form-group">
        <label for="signup-telephone" class="form-label">Téléphone</label>
        <input type="tel" class="form-control" id="signup-telephone" name="telephone" placeholder="Téléphone" required>
      </div>
      <div class="form-group">
        <label for="signup-adresse" class="form-label">Adresse</label>
        <input type="text" class="form-control" id="signup-adresse" name="adresse" placeholder="Adresse" required>
      </div>
      <div class="form-group">
        <label for="signup-password" class="form-label">Mot de passe</label>
        <input type="password" class="form-control" id="signup-password" name="password" placeholder="Mot de passe" required>
      </div>
      <button type="submit" class="btn btn-yellow">S'inscrire</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function toggleForm(formType) {
      const loginForm = document.getElementById('login-form');
      const signupForm = document.getElementById('signup-form');
      const loginTitle = document.getElementById('login-title');
      const tabLinks = document.querySelectorAll('.tab-link');
      
      if (formType === 'login') {
        loginForm.style.display = 'block';
        signupForm.style.display = 'none';
        loginTitle.textContent = 'Connexion';
        tabLinks[0].classList.add('active');
        tabLinks[1].classList.remove('active');
      } else {
        loginForm.style.display = 'none';
        signupForm.style.display = 'block';
        loginTitle.textContent = "S'inscription";
        tabLinks[0].classList.remove('active');
        tabLinks[1].classList.add('active');
      }
    }

    // Validation côté client
    document.querySelectorAll('form').forEach(form => {
      form.addEventListener('submit', function(e) {
        const email = this.querySelector('input[type="email"]')?.value;
        const password = this.querySelector('input[type="password"]').value;
        const telephone = this.querySelector('input[type="tel"]')?.value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const telephoneRegex = /^\+?[0-9]{8,15}$/;
        
        if (email && !emailRegex.test(email)) {
          e.preventDefault();
          alert('Veuillez entrer un email valide.');
          return;
        }
        if (telephone && !telephoneRegex.test(telephone)) {
          e.preventDefault();
          alert('Veuillez entrer un numéro de téléphone valide (8 à 15 chiffres).');
          return;
        }
        if (password.length < 6) {
          e.preventDefault();
          alert('Le mot de passe doit contenir au moins 6 caractères.');
        }
      });
    });
  </script>
</body>
</html>