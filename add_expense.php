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

// Default values
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selectedCategory = isset($_GET['category']) ? intval($_GET['category']) : null;
$expenseDate = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '-' . date('d');

// Get all categories
$stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

$message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $amount = isset($_POST['amount']) ? (float)str_replace(',', '.', $_POST['amount']) : 0;
    $categoryId = isset($_POST['category']) ? intval($_POST['category']) : 0;
    $expenseDate = isset($_POST['expense_date']) ? $_POST['expense_date'] : date('Y-m-d');
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    // Validate form
    $errors = [];
    if (empty($title)) {
        $errors[] = "Titel is verplicht.";
    }
    if ($amount <= 0) {
        $errors[] = "Bedrag moet groter zijn dan 0.";
    }
    if ($categoryId <= 0) {
        $errors[] = "Categorie is verplicht.";
    }
    if (empty($expenseDate)) {
        $errors[] = "Datum is verplicht.";
    }

    if (empty($errors)) {
        // Insert expense
        $stmt = $conn->prepare("
            INSERT INTO expenses (user_id, category_id, title, amount, description, expense_date)
            VALUES (:user_id, :category_id, :title, :amount, :description, :expense_date)
        ");
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':category_id', $categoryId);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':expense_date', $expenseDate);
        
        try {
            if ($stmt->execute()) {
                $expenseMonth = date('n', strtotime($expenseDate));
                $expenseYear = date('Y', strtotime($expenseDate));
                
                $message = '<div class="alert alert-success">Uitgave is succesvol toegevoegd.</div>';
                
                // Clear form
                $title = '';
                $amount = '';
                $description = '';
                
                // If category was pre-selected, redirect to category detail page
                if (isset($_GET['category'])) {
                    header("Location: category_detail.php?year=$expenseYear&month=$expenseMonth&category=$categoryId");
                    exit();
                }
                
                // If month was pre-selected, redirect to month detail page
                if (isset($_GET['month']) && isset($_GET['year'])) {
                    header("Location: month_detail.php?year=$expenseYear&month=$expenseMonth");
                    exit();
                }
            } else {
                $message = '<div class="alert alert-danger">Fout bij het toevoegen van de uitgave.</div>';
            }
        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Database fout: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-danger"><ul>';
        foreach ($errors as $error) {
            $message .= "<li>$error</li>";
        }
        $message .= '</ul></div>';
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Baas - Nieuwe uitgave toevoegen</title>
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
        <h1 class="mb-4">Nieuwe uitgave toevoegen</h1>
        
        <?php echo $message; ?>
        
        <form method="post" action="add_expense.php<?php echo isset($_GET['year']) && isset($_GET['month']) ? '?year=' . $year . '&month=' . $month . (isset($_GET['category']) ? '&category=' . $_GET['category'] : '') : ''; ?>">
          <div class="mb-3">
            <label for="title" class="form-label">Titel *</label>
            <input type="text" class="form-control" id="title" name="title" required value="<?php echo isset($title) ? htmlspecialchars($title) : ''; ?>">
          </div>
          
          <div class="mb-3">
            <label for="amount" class="form-label">Bedrag (€) *</label>
            <input type="number" step="0.01" class="form-control" id="amount" name="amount" required value="<?php echo isset($amount) ? htmlspecialchars($amount) : ''; ?>">
          </div>
          
          <div class="mb-3">
            <label for="category" class="form-label">Categorie *</label>
            <select class="form-control" id="category" name="category" required>
              <option value="">Selecteer een categorie</option>
              <?php foreach ($categories as $category): ?>
                <option value="<?php echo $category['id']; ?>" <?php echo $selectedCategory == $category['id'] ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($category['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="expense_date" class="form-label">Datum *</label>
            <input type="date" class="form-control" id="expense_date" name="expense_date" required value="<?php echo $expenseDate; ?>">
          </div>
          
          <div class="mb-3">
            <label for="description" class="form-label">Omschrijving</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($description) ? htmlspecialchars($description) : ''; ?></textarea>
          </div>
          
          <div class="d-grid gap-2">
            <button type="submit" class="btn btn-danger">Uitgave toevoegen</button>
            
            <?php if (isset($_GET['year']) && isset($_GET['month']) && isset($_GET['category'])): ?>
              <a href="category_detail.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>&category=<?php echo $selectedCategory; ?>" class="btn btn-outline-secondary">Annuleren</a>
            <?php elseif (isset($_GET['year']) && isset($_GET['month'])): ?>
              <a href="month_detail.php?year=<?php echo $year; ?>&month=<?php echo $month; ?>" class="btn btn-outline-secondary">Annuleren</a>
            <?php else: ?>
              <a href="overview.php" class="btn btn-outline-secondary">Annuleren</a>
            <?php endif; ?>
          </div>
        </form>
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