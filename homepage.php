<?php include 'connection.php';?>

<?php
session_start();
$isLoggedIn = false;
$budgetMessage = '';

// Check if user is logged in
if (isset($_SESSION["username"]) && isset($_SESSION["password"])) {
  // Get user ID
  $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
  $stmt->bindParam(':username', $_SESSION["username"]);
  $stmt->execute();
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  
  // Only proceed if user exists in the database
  if ($user) {
    $isLoggedIn = true;
    $userId = $user['id'];
    
    // Get current month and year
    $currentMonth = date('n'); // 1-12
    $currentYear = date('Y');
    
    // Check if budget is set for current month
    $stmt = $conn->prepare("SELECT amount FROM budgets WHERE user_id = :user_id AND month = :month AND year = :year");
    $stmt->bindParam(':user_id', $userId);
    $stmt->bindParam(':month', $currentMonth);
    $stmt->bindParam(':year', $currentYear);
    $stmt->execute();
    $budget = $stmt->fetch(PDO::FETCH_ASSOC);
    $currentBudget = $budget ? $budget['amount'] : '';
    
    // Handle saving new budget
    if (isset($_POST["monthly_budget"]) && !empty($_POST["monthly_budget"])) {
      $budgetAmount = floatval($_POST["monthly_budget"]);
      
      // Check if budget already exists
      if ($budget) {
        // Update existing budget
        $stmt = $conn->prepare("UPDATE budgets SET amount = :amount, updated_at = NOW() 
                               WHERE user_id = :user_id AND month = :month AND year = :year");
      } else {
        // Insert new budget
        $stmt = $conn->prepare("INSERT INTO budgets (user_id, amount, month, year) 
                               VALUES (:user_id, :amount, :month, :year)");
      }
      
      $stmt->bindParam(':amount', $budgetAmount);
      $stmt->bindParam(':user_id', $userId);
      $stmt->bindParam(':month', $currentMonth);
      $stmt->bindParam(':year', $currentYear);
      
      if ($stmt->execute()) {
        $budgetMessage = '<div class="alert alert-success">Budget succesvol opgeslagen!</div>';
        $currentBudget = $budgetAmount;
      } else {
        $budgetMessage = '<div class="alert alert-danger">Fout bij het opslaan van het budget.</div>';
      }
    }
  } else {
    // User not found in database, clear session
    session_unset();
    session_destroy();
  }
}

// Handle login
if (isset($_GET["username"]) && isset($_GET["password"])) {
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
  $stmt->bindParam(':username', $_GET["username"]);
  $stmt->bindParam(':password', $_GET["password"]);
  $stmt->execute();
  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  foreach ($stmt->fetchAll() as $k => $v){
    $_SESSION["username"] = $_GET["username"];
    $_SESSION["password"] = $_GET["password"];
    $isLoggedIn = true;
    header("Location: homepage.php");
  }
}; 
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Baas - Homepage</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
      /* ...existing styles... */
      .custom-menu-bar {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        background: #0A2F35;
        color: #fff;
        z-index: 2000;
        padding: 1.5rem 1rem 1rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      }
      .custom-menu-bar.show {
        display: block;
      }
      .custom-menu-list {
        list-style: none;
        padding: 0;
        margin: 0 0 0 0.5rem;
      }
      .custom-menu-list li {
        margin-bottom: 0.5rem;
      }
      .custom-menu-list a {
        color: #fff;
        text-decoration: none;
        font-size: 1.1rem;
        display: block;
      }
      .custom-menu-close {
        position: absolute;
        top: 1rem;
        right: 1.2rem;
        background: none;
        border: none;
        color: #fff;
        font-size: 2rem;
        cursor: pointer;
        z-index: 2010;
      }
      .custom-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.3);
        z-index: 1999;
      }
      .custom-menu-overlay.show {
        display: block;
      }
    </style>
  </head>
  <body>
    <nav class="navbar">
      <div class="container-fluid">
        <a href="homepage.php">
          <img src="https://www.legerdesheils.nl/assets/LDH-icon-1ff3ea8bdd6cad7741bf0db6eb826da8c58dd75cc91c5cba977945615f079682.svg" alt="Leger des Heils" class="logo">
        </a>
        <button class="navbar-toggler" type="button" id="customMenuToggler" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </nav>
    <div class="custom-menu-bar" id="customMenuBar">
      <button class="custom-menu-close" id="customMenuClose" aria-label="Sluit menu">&times;</button>
      <ul class="custom-menu-list">
        <li><a href="homepage.php">- Home</a></li>
        <li><a href="overview.php">- Overzicht van uitgaven</a></li>
        <li><a href="add_expense.php">- Toevoegen van uitgaven</a></li>
        <li><a href="logout.php">- Uitloggen</a></li>
      </ul>
    </div>
    <div class="custom-menu-overlay" id="customMenuOverlay"></div>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var menuBar = document.getElementById('customMenuBar');
        var menuToggler = document.getElementById('customMenuToggler');
        var menuClose = document.getElementById('customMenuClose');
        var menuOverlay = document.getElementById('customMenuOverlay');
        function openMenu() {
          menuBar.classList.add('show');
          menuOverlay.classList.add('show');
          document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
          menuBar.classList.remove('show');
          menuOverlay.classList.remove('show');
          document.body.style.overflow = '';
        }
        menuToggler.addEventListener('click', openMenu);
        menuClose.addEventListener('click', closeMenu);
        menuOverlay.addEventListener('click', closeMenu);
      });
    </script>
    
    <div class="top-strip"></div>
    
    <div class="container">
      <div class="content-container">
        <img src="https://www.legerdesheils.nl/uploads/editor/noord-1-2.jpg" alt="Budget Baas Gebouw" class="building-image mb-4">
        
        <h1 class="title">Budget<br>Baas</h1>
        
        <?php if (!$isLoggedIn) { ?>
          <div class="login-container">
            <form action="homepage.php" method="get">
              <div class="form-floating mb-3">
                <input type="text" class="form-control" name="username" id="floatingInput" placeholder="Gebruikersnaam">
                <label for="floatingInput">Gebruikersnaam</label>
              </div>
              
              <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" id="floatingPassword" placeholder="Wachtwoord">
                <label for="floatingPassword">Wachtwoord</label>
              </div>

              <button type="submit" class="btn btn-danger w-100 login-button">INLOGGEN</button>
              <a href="#" class="forgot-password">Wachtwoord vergeten</a>
            </form>
          </div>
        <?php } else { ?>
          <?php echo $budgetMessage; ?>
          <div class="welcome-text">
            <p>Welkom <strong><?php echo $_SESSION["username"]; ?></strong>,</p>
            <p>Je maandelijkse budget is ingesteld op:</p>
          </div>
          
          <form action="homepage.php" method="post">
            <div class="form-floating mb-3">
              <input type="number" step="0.01" class="form-control" name="monthly_budget" id="monthlyBudget" placeholder="Maandelijks budget" value="<?php echo $currentBudget; ?>">
              <label for="monthlyBudget">Maandelijks budget</label>
            </div>
            
            <button type="submit" class="btn btn-danger w-100 save-button">OPSLAAN</button>
          </form>
          
          <div class="mt-4">
            <a href="overview.php" class="btn btn-outline-secondary">Bekijk uitgavenoverzicht</a>
            <?php if (strpos($_SESSION["username"], 'admin') !== false) { ?>
              <a href="admin.php" class="btn btn-outline-primary mt-2">Admin paneel</a>
            <?php } ?>
            <a href="logout.php" class="btn btn-outline-dark mt-2">Uitloggen</a>
          </div>
        <?php } ?>
      </div>
    </div>

    <footer>
      <div class="container">
        <div class="footer-content">
          <div>Leger des Heils</div>
          <div>Budget Baas</div>
          <div class="social-icons">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          </div>
          <div class="copyright">
            Copyright © 2025<br>
            All Rights Reserved.
          </div>
        </div>
      </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html> 