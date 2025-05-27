<?php include 'connection.php';?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header("Location: homepage.php");
    exit();
}

// Check if year and month are set
if (!isset($_GET['year']) || !isset($_GET['month'])) {
    header("Location: overview.php");
    exit();
}

$year = intval($_GET['year']);
$month = intval($_GET['month']);

// Validate month range
if ($month < 1 || $month > 12) {
    header("Location: overview.php");
    exit();
}

// Get user ID
$stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
$stmt->bindParam(':username', $_SESSION["username"]);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = $user['id'];

// Get budget for this month
$stmt = $conn->prepare("
    SELECT amount FROM budgets
    WHERE user_id = :user_id AND month = :month AND year = :year
");
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':month', $month);
$stmt->bindParam(':year', $year);
$stmt->execute();
$budgetRow = $stmt->fetch(PDO::FETCH_ASSOC);
$budget = $budgetRow ? $budgetRow['amount'] : 0;

// Get expenses grouped by category
$stmt = $conn->prepare("
    SELECT 
        c.id as category_id,
        c.name as category_name,
        SUM(e.amount) as total_amount,
        COUNT(*) as expense_count
    FROM expenses e
    JOIN categories c ON e.category_id = c.id
    WHERE e.user_id = :user_id 
    AND MONTH(e.expense_date) = :month 
    AND YEAR(e.expense_date) = :year
    GROUP BY c.id, c.name
    ORDER BY total_amount DESC
");
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':month', $month);
$stmt->bindParam(':year', $year);
$stmt->execute();
$categoryExpenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total expenses for this month
$stmt = $conn->prepare("
    SELECT SUM(amount) as total_amount
    FROM expenses
    WHERE user_id = :user_id 
    AND MONTH(expense_date) = :month 
    AND YEAR(expense_date) = :year
");
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':month', $month);
$stmt->bindParam(':year', $year);
$stmt->execute();
$totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
$totalExpenses = $totalRow ? $totalRow['total_amount'] : 0;

// Calculate balance
$balance = $budget - $totalExpenses;
$balanceClass = $balance >= 0 ? 'text-success' : 'text-danger';

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
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Baas - Details <?php echo $monthNames[$month]; ?> <?php echo $year; ?></title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <nav class="navbar">
      <div class="container-fluid">
        <a href="homepage.php">
          <img src="https://www.legerdesheils.nl/assets/LDH-icon-1ff3ea8bdd6cad7741bf0db6eb826da8c58dd75cc91c5cba977945615f079682.svg" alt="Leger des Heils" class="logo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
      </div>
    </nav>
    
    <div class="top-strip"></div>
    
    <div class="container">
      <div class="content-container">
        <h1 class="mb-2">Uitgaven</h1>
        <hr>
        <p>Maand: <?php echo $monthNames[$month] . ' ' . $year; ?><br>Ingesteld maandbudget: &euro;<?php echo number_format($budget, 0, ',', '.'); ?>,-</p>
        <table class="table table-bordered table-sm" style="max-width: 400px;">
          <thead>
            <tr>
              <th>Categorie</th>
              <th class="text-end">Uitgaven</th>
            </tr>
          </thead>
          <tbody>
            <?php $total = 0; ?>
            <?php foreach ($categoryExpenses as $category): ?>
              <tr>
                <td><a href="category_detail.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&category=<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['category_name']); ?></a></td>
                <td class="text-end">&euro; <?php echo number_format($category['total_amount'], 2, ',', '.'); ?></td>
              </tr>
              <?php $total += $category['total_amount']; ?>
            <?php endforeach; ?>
            <tr>
              <th>Totaal</th>
              <th class="text-end" style="color: #2ecc40;">&euro; <?php echo number_format($total, 2, ',', '.'); ?></th>
            </tr>
          </tbody>
        </table>
        <?php if (empty($categoryExpenses)): ?>
          <div class="alert alert-info">
            Je hebt nog geen uitgaven geregistreerd voor deze maand.
          </div>
        <?php endif; ?>
        <div class="mt-4 mb-5">
          <a href="overview.php" class="btn btn-outline-secondary">Terug naar overzicht</a>
        </div>
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