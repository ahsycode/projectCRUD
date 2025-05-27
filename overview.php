<?php include 'connection.php';?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header("Location: homepage.php");
    exit();
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
$stmt->bindParam(':username', $_SESSION["username"]);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'];

// Get all months with expenses
$stmt = $conn->prepare("
    SELECT 
        YEAR(expense_date) as year,
        MONTH(expense_date) as month,
        SUM(amount) as total_amount
    FROM expenses
    WHERE user_id = :user_id
    GROUP BY YEAR(expense_date), MONTH(expense_date)
    ORDER BY YEAR(expense_date) DESC, MONTH(expense_date) DESC
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$monthlyExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get budgets for all months
$stmt = $conn->prepare("
    SELECT 
        year,
        month,
        amount
    FROM budgets
    WHERE user_id = :user_id
    ORDER BY year DESC, month DESC
");
$stmt->bindParam(':user_id', $userId);
$stmt->execute();
$budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize budgets by year and month for easy lookup
$budgetsByMonth = [];
foreach ($budgets as $budget) {
    $key = $budget['year'] . '-' . $budget['month'];
    $budgetsByMonth[$key] = $budget['amount'];
}

// Dutch month names
$monthNames = [
    1 => 'Januari', 
    2 => 'Februari', 
    3 => 'Maart', 
    4 => 'April', 
    5 => 'Mei', 
    6 => 'Juni', 
    7 => 'Juli', 
    8 => 'Augustus', 
    9 => 'September', 
    10 => 'Oktober', 
    11 => 'November', 
    12 => 'December'
];

// Group months by year
$years = [];
foreach ($monthlyExpenses as $expense) {
    $years[$expense['year']][] = $expense;
}
// Also include months with a budget but no expenses
foreach ($budgets as $budget) {
    $key = $budget['year'] . '-' . $budget['month'];
    if (!isset($years[$budget['year']])) $years[$budget['year']] = [];
    $found = false;
    foreach ($years[$budget['year']] as $e) {
        if ($e['month'] == $budget['month']) {
            $found = true;
            break;
        }
    }
    if (!$found) {
        $years[$budget['year']][] = [
            'year' => $budget['year'],
            'month' => $budget['month'],
            'total_amount' => 0
        ];
    }
}
// Sort years descending, months ascending
krsort($years);
foreach ($years as &$months) {
    usort($months, function($a, $b) { return $a['month'] <=> $b['month']; });
}
unset($months);
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Baas - Uitgavenoverzicht</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
      .expenses-green { color: #2ecc40; }
      .expenses-red { color: #e74c3c; }
      .overview-table th, .overview-table td { vertical-align: middle; }
      .overview-table a { text-decoration: underline; }
      
      /* Navigation menu styles */
      #customNavMenu {
        position: fixed;
        top: 0;
        right: -280px;
        width: 280px;
        height: 100vh;
        background-color: #0A2F35;
        padding: 1rem;
        z-index: 1000;
        transition: right 0.3s ease-in-out;
      }
      #customNavMenu.show {
        right: 0;
      }
      .navbar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
      }
      .nav-item {
        margin: 0.5rem 0;
      }
      .nav-link {
        color: white !important;
        text-decoration: none;
        font-size: 1rem;
        padding: 0.5rem 0;
        display: block;
      }
      .navbar-toggler {
        z-index: 1001;
        border: none;
        background: none;
      }
      /* Close button */
      .nav-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        color: white;
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0;
      }
      /* Overlay when menu is open */
      #customNavOverlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
      }
      #customNavOverlay.show {
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
        <button class="navbar-toggler" type="button" id="customNavToggler" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </nav>
    <div id="customNavMenu">
      <button type="button" class="nav-close" id="customNavClose">
        <i class="fas fa-times"></i>
      </button>
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="homepage.php">- Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="overview.php">- Overzicht van uitgaven</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="add_expense.php">- Toevoegen van uitgaven</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">- Uitloggen</a>
        </li>
      </ul>
    </div>
    <div id="customNavOverlay"></div>
    
    <div class="top-strip"></div>
    
    <div class="container">
      <div class="content-container">
        <h1 class="mb-2">Uitgaven</h1>
        <hr>
        <p>Bekijk hier het overzicht van je uitgaven per maand:</p>
        <?php foreach ($years as $year => $months): ?>
          <h5 class="mt-4 mb-2"><?php echo $year; ?></h5>
          <table class="table table-sm overview-table mb-3">
            <thead>
              <tr>
                <th>Maand</th>
                <th>Budget</th>
                <th>Uitgaven</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($months as $expense): 
                $key = $expense['year'] . '-' . $expense['month'];
                $budget = isset($budgetsByMonth[$key]) ? $budgetsByMonth[$key] : 0;
                $amount = $expense['total_amount'];
                $isOver = $amount > $budget && $budget > 0;
                $amountClass = $amount > 0 ? ($isOver ? 'expenses-red' : 'expenses-green') : '';
              ?>
                <tr>
                  <td><a href="month_detail.php?year=<?php echo $expense['year']; ?>&month=<?php echo $expense['month']; ?>"><?php echo $monthNames[$expense['month']]; ?></a></td>
                  <td>€ <?php echo number_format($budget, 0, ',', '.'); ?></td>
                  <td class="<?php echo $amountClass; ?>">€ <?php echo $amount > 0 ? number_format($amount, 0, ',', '.') : '0'; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endforeach; ?>
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
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var navMenu = document.getElementById('customNavMenu');
        var navToggler = document.getElementById('customNavToggler');
        var navClose = document.getElementById('customNavClose');
        var navOverlay = document.getElementById('customNavOverlay');
        function openMenu() {
          navMenu.classList.add('show');
          navOverlay.classList.add('show');
          document.body.style.overflow = 'hidden';
        }
        function closeMenu() {
          navMenu.classList.remove('show');
          navOverlay.classList.remove('show');
          document.body.style.overflow = '';
        }
        navToggler.addEventListener('click', openMenu);
        navClose.addEventListener('click', closeMenu);
        navOverlay.addEventListener('click', closeMenu);
      });
    </script>
  </body>
</html> 