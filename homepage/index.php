<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messai Travel</title>
  <meta name="description" content="Réservez vos aventures avec Messai Travel aux meilleurs prix.">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="index.html">
        <img src="images/logo.png" alt="Messai Travel Logo" height="40">
      </a>      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="#accueil">Accueil</a></li>
          <li class="nav-item"><a class="nav-link" href="#destinations">Destinations</a></li>
          <li class="nav-item"><a class="nav-link" href="#offres">Offres</a></li>
          <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
          <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero with Search Form -->   
<section id="accueil" class="hero">     
  <div class="container">       
    <h1 class="display-4 fw-bold">Découvrez le monde avec Messai Travel</h1>       
    <p class="lead mb-4">Réservez vos prochaines aventures aux meilleurs prix.</p>       
    <div class="row justify-content-center">         
      <div class="col-lg-8">           
        <div class="card shadow">             
          <div class="card-body">               
            <form id="searchForm" class="row g-3" aria-label="Recherche de voyage">                 
              <div class="col-md-4">                   
                <label for="destination" class="form-label">Destination</label>                   
                <select class="form-select" id="destination" name="destination" required>                     
                  <option value="" selected>Choisir...</option>                     
                  <option value="paris">Paris</option>                     
                  <option value="tokyo">Tokyo</option>                     
                  <option value="marrakech">Marrakech</option>                   
                </select>                 
              </div>                 
              <div class="col-md-4">                   
                <label for="depart" class="form-label">Date de départ</label>                   
                <input type="date" class="form-control" id="depart" name="depart" required>                 
              </div>                 
              <div class="col-md-4">                   
                <label for="retour" class="form-label">Date de retour</label>                   
                <input type="date" class="form-control" id="retour" name="retour">                 
              </div>                 
              <div class="col-12 mt-3 text-center">                   
                <button type="submit" class="btn btn-yellow btn-lg">Rechercher</button>                 
              </div>               
            </form>             
          </div>           
        </div>
        
        <!-- Results area - initially hidden -->
        <div id="searchResults" class="mt-4 d-none">
          <div class="card shadow">
            <div class="card-body">
              <h3 class="card-title">Résultats de recherche</h3>
              <div id="resultsContent">
                <!-- Results will be displayed here -->
              </div>
            </div>
          </div>
        </div>
        
        <!-- No results message - initially hidden -->
        <div id="noResults" class="mt-4 d-none">
          <div class="card shadow">
            <div class="card-body text-center">
              <h3 class="card-title">Aucun résultat trouvé</h3>
              <p>Désolé, nous n'avons pas trouvé de voyages correspondant à vos critères. Veuillez essayer une autre recherche.</p>
            </div>
          </div>
        </div>
      </div>         
    </div>     
  </div>   
</section> 

<script>
document.addEventListener('DOMContentLoaded', function() {
  const searchForm = document.getElementById('searchForm');
  const searchResults = document.getElementById('searchResults');
  const noResults = document.getElementById('noResults');
  const resultsContent = document.getElementById('resultsContent');
  
  // Sample data for demonstration (in a real app, this would come from a database)
  const availableDestinations = {
    'paris': {
      name: 'Paris',
      available: true,
      packages: [
        { title: 'Paris Romantique', nights: 3, price: '20000 DA', detailPage: 'paris.html' },
        { title: 'Découverte de Paris', nights: 5, price: '23000 DA', detailPage: 'paris.html' }
      ]
    },
    'tokyo': {
      name: 'Tokyo',
      available: true,
      packages: [
        { title: 'Tokyo Express', nights: 7, price: '299990 DA', detailPage: 'tokyo.html' },
        { title: 'Japon Complet', nights: 10, price: '299990 DA', detailPage: 'tokyo.html' }
      ]
    },
    'marrakech': {
      name: 'Marrakech',
      available: false,
      packages: []
    }
  };
  
  searchForm.addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent the form from submitting normally
    
    // Get form values
    const destination = document.getElementById('destination').value;
    const departDate = document.getElementById('depart').value;
    const returnDate = document.getElementById('retour').value;
    
    // Hide both result areas initially
    searchResults.classList.add('d-none');
    noResults.classList.add('d-none');
    
    // Check if destination exists and has packages available
    if (destination && availableDestinations[destination] && availableDestinations[destination].available) {
      // Build results HTML
      const packages = availableDestinations[destination].packages;
      let resultHTML = `<p>Voyages disponibles pour <strong>${availableDestinations[destination].name}</strong></p>`;
      resultHTML += `<p>Du ${formatDate(departDate)} au ${formatDate(returnDate || departDate)}</p>`;
      resultHTML += '<div class="list-group mt-3">';
      
      packages.forEach(pkg => {
        resultHTML += `
          <div class="list-group-item list-group-item-action">
            <div class="d-flex w-100 justify-content-between">
              <h5 class="mb-1">${pkg.title}</h5>
              <span class="text-primary fw-bold">${pkg.price}</span>
            </div>
            <p class="mb-1">${pkg.nights} nuits</p>
            <a href="${pkg.detailPage}" class="btn btn-sm btn-outline-primary mt-2">Voir détails</a>
          </div>
        `;
      });
      
      resultHTML += '</div>';
      
      // Show results
      resultsContent.innerHTML = resultHTML;
      searchResults.classList.remove('d-none');
    } else {
      // Show no results message
      noResults.classList.remove('d-none');
    }
  });
  
  // Helper function to format dates
  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric'
    });
  }
});
</script>
  <!-- Destinations -->
  <section id="destinations" class="py-5">
    <div class="container">
      <h2 class="text-center section-title mb-4">Destinations populaires</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <img src="images/paris.jpg" class="card-img-top" alt="Tour Eiffel illuminée à Paris" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Paris</h5>
              <p class="card-text">La ville de l'amour avec ses musées et cafés charmants.</p>
              <a href="paris.html" class="btn btn-yellow">Découvrir</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <img src="images/tokyo.jpg" class="card-img-top" alt="Rues animées de Tokyo avec néons" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Tokyo</h5>
              <p class="card-text">Culture japonaise moderne et traditionnelle.</p>
              <a href="tokyo.html" class="btn btn-yellow">Découvrir</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <img src="images/marrakech.jpg" class="card-img-top" alt="Souks colorés de Marrakech" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Marrakech</h5>
              <p class="card-text">Explorez les souks et les saveurs marocaines.</p>
              <a href="#" class="btn btn-yellow">Découvrir</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Offres -->
  <section id="offres" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center section-title mb-4">Offres</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <span class="badge bg-danger position-absolute top-0 end-0 mt-2 me-2">- 20%</span>
            <img src="images/paris-offre.jpg" class="card-img-top" alt="Vue de Paris avec réduction" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Offre Paris - 20% de réduction</h5>
              <p class="card-text">Profitez d'une réduction pour une visite à Paris.</p>
              <a href="paris.html#reservation" class="btn btn-yellow">Réserver</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <span class="badge bg-danger position-absolute top-0 end-0 mt-2 me-2">49999 DA</span>
            <img src="images/tokyo-offre.jpg" class="card-img-top" alt="Offre spéciale Tokyo" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Offre Tokyo - 49999 DA</h5>
              <p class="card-text">Un voyage à Tokyo à prix imbattable.</p>
              <a href="#" class="btn btn-yellow">Réserver</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm">
            <span class="badge bg-danger position-absolute top-0 end-0 mt-2 me-2">- 30%</span>
            <img src="images/marrakech-offre.jpg" class="card-img-top" alt="Offre spéciale Marrakech" loading="lazy">
            <div class="card-body">
              <h5 class="card-title">Offre Marrakech - 30% de réduction</h5>
              <p class="card-text">Découvrez Marrakech à prix réduit.</p>
              <a href="#" class="btn btn-yellow">Réserver</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Newsletter -->
  <section id="newsletter" class="py-5 bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
          <h3 class="mb-4">Restez informé de nos offres</h3>
          <p class="mb-4">Inscrivez-vous pour recevoir nos promotions exclusives.</p>
          <form action="newsletter.html" method="POST" class="row g-3 justify-content-center" aria-label="Inscription newsletter">
            <div class="col-auto">
              <input type="email" class="form-control" name="email" placeholder="Votre email" required aria-label="Adresse e-mail">
            </div>
            <div class="col-auto">
              <button type="submit" class="btn btn-yellow">S'inscrire</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>

  <!-- Testimonials -->
  <section id="temoignages" class="py-5">
    <div class="container">
      <h2 class="text-center section-title mb-5">Ce que nos clients disent</h2>
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="testimonial shadow-sm">
            <div class="d-flex align-items-center mb-3">
              <img src="images/sophie.jpg" alt="Sophie Martin, voyage à Paris" class="testimonial-img me-3" loading="lazy">
              <div>
                <h5 class="mb-0">Sophie Martin</h5>
                <small class="text-muted">Voyage à Paris</small>
              </div>
            </div>
            <p class="mb-0">« Service exceptionnel ! Paris était parfaitement organisé. »</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="testimonial shadow-sm">
            <div class="d-flex align-items-center mb-3">
              <img src="images/thomas.jpg" alt="Thomas Dubois, voyage à Tokyo" class="testimonial-img me-3" loading="lazy">
              <div>
                <h5 class="mb-0">Thomas Dubois</h5>
                <small class="text-muted">Voyage à Tokyo</small>
              </div>
            </div>
            <p class="mb-0">« Aventure incroyable au Japon ! »</p>
          </div>
        </div>
        <div class="col-md-4 mb-4">
          <div class="testimonial shadow-sm">
            <div class="d-flex align-items-center mb-3">
              <img src="images/marie.jpg" alt="Marie Leroy, voyage à Marrakech" class="testimonial-img me-3" loading="lazy">
              <div>
                <h5 class="mb-0">Marie Leroy</h5>
                <small class="text-muted">Voyage à Marrakech</small>
              </div>
            </div>
            <p class="mb-0">« Vacances inoubliables au Maroc ! »</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Trust Badges -->
  <section class="py-4">
    <div class="container">
      <div class="row text-center">
        <div class="col-md-3 mb-3">
          <i class="bi bi-shield-check fs-1 text-yellow"></i>
          <h5 class="mt-2">Paiement Sécurisé</h5>
        </div>
        <div class="col-md-3 mb-3">
          <i class="bi bi-headset fs-1 text-yellow"></i>
          <h5 class="mt-2">Support 24/7</h5>
        </div>
        <div class="col-md-3 mb-3">
          <i class="bi bi-cash-coin fs-1 text-yellow"></i>
          <h5 class="mt-2">Meilleur Prix</h5>
        </div>
        <div class="col-md-3 mb-3">
          <i class="bi bi-award fs-1 text-yellow"></i>
          <h5 class="mt-2">Agence Certifiée</h5>
        </div>
      </div>
    </div>
  </section>

  <!-- À propos -->
  <section id="apropos" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center section-title mb-4">À propos de nous</h2>
      <p class="text-center w-75 mx-auto">
        Messai Travel est une agence passionnée par la découverte du monde. Nous accompagnons nos clients dans leurs aventures aux quatre coins du globe.
      </p>
    </div>
  </section>

  <!-- Contact -->
<section id="contact" class="py-5">
  <div class="container">
    <h2 class="text-center section-title mb-4">Contactez-nous</h2>
    
    <!-- Alerte de confirmation -->
    <div class="alert alert-success w-75 mx-auto mb-4" id="confirmationMessage" role="alert" style="display: none;">
      Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.
    </div>
    
    <form id="contactForm" action="contact.html" method="POST" class="w-75 mx-auto" aria-label="Formulaire de contact">
      <div class="mb-3">
        <label for="nom" class="form-label">Nom</label>
        <input type="text" class="form-control" id="nom" name="nom" placeholder="Votre nom" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">Adresse e-mail</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="nom@exemple.com" required>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" rows="4" placeholder="Votre message..." required></textarea>
      </div>
      <button type="submit" class="btn btn-yellow">Envoyer</button>
    </form>
  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contactForm');
    const confirmationMessage = document.getElementById('confirmationMessage');
    
    form.addEventListener('submit', function(event) {
      event.preventDefault(); // Empêche le rechargement de la page
      
      // Ici, vous pourriez ajouter du code pour envoyer les données à un serveur
      // Par exemple, avec fetch() ou XMLHttpRequest
      
      // Affiche le message de confirmation
      confirmationMessage.style.display = 'block';
      
      // Réinitialise le formulaire
      form.reset();
      
      // Fait défiler jusqu'au message de confirmation
      confirmationMessage.scrollIntoView({ behavior: 'smooth' });
      
      // Masquer le message après 5 secondes
      setTimeout(function() {
        confirmationMessage.style.display = 'none';
      }, 5000); // 5 secondes
    });
  });
</script>

  <!-- Footer -->
  <footer class="footer text-center py-4">
    <div class="container">
      <div class="row">
        <div class="col-md-4 mb-3 mb-md-0">
          <h5 class="mb-3">Messai Travel</h5>
          <p class="mb-0">Votre partenaire de voyage depuis 2015.</p>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
          <h5 class="mb-3">Liens rapides</h5>
          <ul class="list-unstyled">
            <li><a href="#accueil" class="text-white">Accueil</a></li>
            <li><a href="#destinations" class="text-white">Destinations</a></li>
            <li><a href="#offres" class="text-white">Offres</a></li>
            <li><a href="#contact" class="text-white">Contact</a></li>
          </ul>
        </div>
        <div class="col-md-4">
          <h5 class="mb-3">Suivez-nous</h5>
          <div class="d-flex justify-content-center">
            <a href="https://facebook.com" class="text-white mx-2" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://instagram.com" class="text-white mx-2" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://twitter.com" class="text-white mx-2" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
          </div>
        </div>
      </div>
      <hr class="my-4 bg-light">
      <p class="mb-0">© 2025 Messai Travel. Tous droits réservés.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>