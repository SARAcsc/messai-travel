<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Rapports</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
      background: #f4f4f4;
    }

    .logo {
      display: block;
      margin: 0 auto 20px auto;
      width: 150px;
    }

    h2 {
      text-align: center;
      margin-bottom: 40px;
    }

    .chart-container {
      width: 80%;
      margin: 0 auto 50px auto;
      background: #fff;
      padding: 20px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      border-radius: 8px;
    }

    canvas {
      margin: auto;
    }

    .back-button {
      display: block;
      margin: 30px auto;
      padding: 10px 20px;
      background-color:#243e93;
      color: white;
      font-size: 16px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
    }

    .back-button:hover {
      background-color: #fbcd07;
    }
  </style>
</head>

<body>
<div style="
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fff;
  padding: 20px;
  margin-bottom: 40px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
  border-radius: 10px;
  width: 80%;
  margin-left: auto;
  margin-right: auto;
">
<a href="index.php">
  <img src="assets/logo.jpg" alt="Logo Agence" style="width: 80px; margin-right: 20px; cursor: pointer;">
</a>
  <h2 style="margin: 0;">Tableau de bord - Rapports</h2>
</div>



  <div class="chart-container">
    <canvas id="camembert" width="400" height="200"></canvas>
  </div>

  <div class="chart-container">
    <canvas id="rendement" width="400" height="200"></canvas>
    <div style="text-align: center; margin-top: 20px; font-weight: bold; font-size: 18px;">
      Rendement pour cette année : <span id="rendementTotal">0</span> DA
    </div>
  </div>

  <div class="chart-container">
    <canvas id="offresParMois" width="400" height="200"></canvas>
  </div>

  <?php
    // Connexion à la base de données
    $host = '127.0.0.1';
    $db = 'projet';
    $user = 'root';
    $pass = '';

    try {
      $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $stmt = $pdo->query("SELECT pays, COUNT(*) AS nombre_destinations FROM destinations GROUP BY pays");
      $destinationsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      $pays = [];
      $nombreDestinations = [];

      foreach ($destinationsData as $row) {
        $pays[] = $row['pays'];
        $nombreDestinations[] = $row['nombre_destinations'];
      }

    } catch (PDOException $e) {
      echo "Erreur de connexion à la base de données: " . $e->getMessage();
    }
  ?>

  <script>
    // === Camembert ===
    const camembert = new Chart(document.getElementById('camembert'), {
      type: 'pie',
      data: {
        labels: <?php echo json_encode($pays); ?>,
        datasets: [{
          data: <?php echo json_encode($nombreDestinations); ?>,
          backgroundColor: ['#ff7043', '#ffca28', '#66bb6a', '#42a5f5', '#ab47bc']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Répartition des destinations par pays'
          }
        }
      }
    });

    // === Rendement ===
    const rendement = new Chart(document.getElementById('rendement'), {
      type: 'line',
      data: {
        labels: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai'],
        datasets: [{
          label: 'Rendement (DA)',
          data: [100000, 85000, 90000, 120000, 95000],
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Rendement Monétaire'
          }
        }
      }
    });

    const rendementTotal = rendement.data.datasets[0].data.reduce((a, b) => a + b, 0);
    document.getElementById('rendementTotal').textContent = rendementTotal.toLocaleString();

    // === Offres par mois ===
    const ctx = document.getElementById('offresParMois');
    const mois = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai'];
    const offres = [3, 5, 2, 4, 6];
    const couleurs = ['#90caf9', '#a5d6a7', '#ffe082', '#ffab91', '#ce93d8'];

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: mois,
        datasets: [{
          data: offres,
          backgroundColor: couleurs
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
            display: true,
            text: 'Offres ajoutées par mois'
          },
          datalabels: {
            anchor: 'center',
            align: 'center',
            color: '#000',
            font: {
              weight: 'bold',
              size: 12
            },
            formatter: function(value, context) {
              return mois[context.dataIndex];
            }
          }
        },
        scales: {
          x: {
            ticks: {
              display: false
            }
          },
          y: {
            beginAtZero: true
          }
        }
      },
      plugins: [ChartDataLabels]
    });
  </script>

  <!-- Bouton de retour -->
  <a href="index.php" class="back-button">Retour à l'Accueil</a>

</body>
</html>
