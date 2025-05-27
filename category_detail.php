<?php include 'connection.php';?>

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["username"]) || !isset($_SESSION["password"])) {
    header("Location: homepage.php");
    exit();
}

// Check if required parameters are set
if (!isset($_GET['year']) || !isset($_GET['month']) || !isset($_GET['category'])) {
    header("Location: overview.php");
    exit();
}

$year = intval($_GET['year']);
$month = intval($_GET['month']);
$categoryId = intval($_GET['category']);

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

// Get category name
$stmt = $conn->prepare("SELECT name FROM categories WHERE id = :category_id");
$stmt->bindParam(':category_id', $categoryId);
$stmt->execute();
$categoryRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$categoryRow) {
    header("Location: overview.php");
    exit();
}

$categoryName = $categoryRow['name'];

// Get all expenses for this month and category
$stmt = $conn->prepare("
    SELECT 
        id,
        title,
        amount,
        description,
        expense_date,
        created_at
    FROM expenses
    WHERE user_id = :user_id 
    AND category_id = :category_id
    AND MONTH(expense_date) = :month 
    AND YEAR(expense_date) = :year
    ORDER BY expense_date DESC
");
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':category_id', $categoryId);
$stmt->bindParam(':month', $month);
$stmt->bindParam(':year', $year);
$stmt->execute();
$expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total for this category and month
$stmt = $conn->prepare("
    SELECT SUM(amount) as total_amount
    FROM expenses
    WHERE user_id = :user_id 
    AND category_id = :category_id
    AND MONTH(expense_date) = :month 
    AND YEAR(expense_date) = :year
");
$stmt->bindParam(':user_id', $userId);
$stmt->bindParam(':category_id', $categoryId);
$stmt->bindParam(':month', $month);
$stmt->bindParam(':year', $year);
$stmt->execute();
$totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
$totalAmount = $totalRow ? $totalRow['total_amount'] : 0;

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

// Process delete request
$deleteMessage = '';
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $expenseId = intval($_GET['delete']);
    
    // Verify that this expense belongs to the user
    $stmt = $conn->prepare("
        SELECT id FROM expenses
        WHERE id = :expense_id AND user_id = :user_id
    ");
    $stmt->bindParam(':expense_id', $expenseId);
    $stmt->bindParam(':user_id', $userId);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        // Delete the expense
        $stmt = $conn->prepare("DELETE FROM expenses WHERE id = :expense_id");
        $stmt->bindParam(':expense_id', $expenseId);
        
        if ($stmt->execute()) {
            $deleteMessage = '<div class="alert alert-success">Uitgave is succesvol verwijderd.</div>';
            
            // Refresh expenses list
            $stmt = $conn->prepare("
                SELECT 
                    id,
                    title,
                    amount,
                    description,
                    expense_date,
                    created_at
                FROM expenses
                WHERE user_id = :user_id 
                AND category_id = :category_id
                AND MONTH(expense_date) = :month 
                AND YEAR(expense_date) = :year
                ORDER BY expense_date DESC
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Update total
            $stmt = $conn->prepare("
                SELECT SUM(amount) as total_amount
                FROM expenses
                WHERE user_id = :user_id 
                AND category_id = :category_id
                AND MONTH(expense_date) = :month 
                AND YEAR(expense_date) = :year
            ");
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':category_id', $categoryId);
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            $totalRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $totalAmount = $totalRow ? $totalRow['total_amount'] : 0;
        } else {
            $deleteMessage = '<div class="alert alert-danger">Fout bij het verwijderen van de uitgave.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Baas - <?php echo $categoryName; ?> (<?php echo $monthNames[$month]; ?> <?php echo $year; ?>)</title>
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
        <h1 class="mb-4"><?php echo $categoryName; ?></h1>
        <h5 class="mb-4"><?php echo $monthNames[$month]; ?> <?php echo $year; ?></h5>
        
        <?php echo $deleteMessage; ?>
        
        <div class="card mb-4">
          <div class="card-header bg-light">
            <h5>Totaal <?php echo $categoryName; ?></h5>
          </div>
          <div class="card-body">
            <h3 class="text-center">€ <?php echo number_format($totalAmount, 2, ',', '.'); ?></h3>
          </div>
        </div>
        
        <a href="add_expense.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&category=<?php echo $categoryId; ?>" class="btn btn-danger mb-4">
          <i class="fas fa-plus"></i> Nieuwe uitgave toevoegen
        </a>
        
        <?php if (empty($expenses)): ?>
          <div class="alert alert-info">
            Je hebt nog geen uitgaven geregistreerd voor deze categorie in deze maand.
          </div>
        <?php else: ?>
          <h3 class="mb-3">Alle uitgaven</h3>
          
          <?php foreach ($expenses as $expense): ?>
            <div class="card mb-3">
              <div class="card-header d-flex justify-content-between align-items-center">
                <span><?php echo htmlspecialchars($expense['title']); ?></span>
                <span class="badge bg-secondary">€ <?php echo number_format($expense['amount'], 2, ',', '.'); ?></span>
              </div>
              <div class="card-body">
                <?php if (!empty($expense['description'])): ?>
                  <p><?php echo nl2br(htmlspecialchars($expense['description'])); ?></p>
                <?php endif; ?>
                <small class="text-muted">Datum: <?php echo date('d-m-Y', strtotime($expense['expense_date'])); ?></small>
              </div>
              <div class="card-footer text-end">
                <a href="category_detail.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&category=<?php echo $categoryId; ?>&delete=<?php echo $expense['id']; ?>" 
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Weet je zeker dat je deze uitgave wilt verwijderen?');">
                  <i class="fas fa-trash"></i> Verwijderen
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="mt-4 mb-5">
          <a href="month_detail.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-outline-secondary">Terug naar maandoverzicht</a>
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