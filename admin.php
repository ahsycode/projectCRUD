<?php include 'connection.php';?>

<?php
session_start();

// Check if user is logged in and is admin
$isAdmin = false;
if (isset($_SESSION["username"]) && isset($_SESSION["password"])) {
    $stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = :username");
    $stmt->bindParam(':username', $_SESSION["username"]);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && $user['is_admin'] == 1) {
        $isAdmin = true;
    }
}

if (!$isAdmin) {
    header("Location: homepage.php");
    exit();
}

// Handle user actions (delete, edit, add)
$message = '';
$editUser = null;

// Delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userId = intval($_GET['delete']);
    
    // Don't allow deleting self
    if ($userId != $user['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        
        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Gebruiker is succesvol verwijderd.</div>';
        } else {
            $message = '<div class="alert alert-danger">Fout bij het verwijderen van de gebruiker.</div>';
        }
    } else {
        $message = '<div class="alert alert-danger">Je kunt je eigen account niet verwijderen.</div>';
    }
}

// Prepare for edit
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $userId = intval($_GET['edit']);
    
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = :id");
    $stmt->bindParam(':id', $userId);
    $stmt->execute();
    $editUser = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add or Edit user
    if (isset($_POST['action'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $isAdminUser = isset($_POST['is_admin']) ? 1 : 0;
        
        // Validate form
        $errors = [];
        if (empty($username)) {
            $errors[] = "Gebruikersnaam is verplicht.";
        }
        if (empty($email)) {
            $errors[] = "E-mail is verplicht.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Ongeldig e-mailadres.";
        }
        
        if ($_POST['action'] === 'add' && empty($password)) {
            $errors[] = "Wachtwoord is verplicht voor nieuwe gebruikers.";
        }
        
        if (empty($errors)) {
            if ($_POST['action'] === 'edit' && isset($_POST['user_id'])) {
                $userId = intval($_POST['user_id']);
                
                // Check if username already exists for different user
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':id', $userId);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $message = '<div class="alert alert-danger">Deze gebruikersnaam bestaat al.</div>';
                } else {
                    // Check if email already exists for different user
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':id', $userId);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $message = '<div class="alert alert-danger">Dit e-mailadres is al in gebruik.</div>';
                    } else {
                        // Update user
                        if (!empty($password)) {
                            $stmt = $conn->prepare("
                                UPDATE users SET 
                                    username = :username, 
                                    email = :email, 
                                    password = :password,
                                    is_admin = :is_admin
                                WHERE id = :id
                            ");
                            $stmt->bindParam(':password', $password);
                        } else {
                            $stmt = $conn->prepare("
                                UPDATE users SET 
                                    username = :username, 
                                    email = :email,
                                    is_admin = :is_admin
                                WHERE id = :id
                            ");
                        }
                        
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':is_admin', $isAdminUser);
                        $stmt->bindParam(':id', $userId);
                        
                        if ($stmt->execute()) {
                            $message = '<div class="alert alert-success">Gebruiker is succesvol bijgewerkt.</div>';
                            $editUser = null; // Clear edit mode
                        } else {
                            $message = '<div class="alert alert-danger">Fout bij het bijwerken van de gebruiker.</div>';
                        }
                    }
                }
            } elseif ($_POST['action'] === 'add') {
                // Check if username already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE username = :username");
                $stmt->bindParam(':username', $username);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $message = '<div class="alert alert-danger">Deze gebruikersnaam bestaat al.</div>';
                } else {
                    // Check if email already exists
                    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                    $stmt->bindParam(':email', $email);
                    $stmt->execute();
                    
                    if ($stmt->rowCount() > 0) {
                        $message = '<div class="alert alert-danger">Dit e-mailadres is al in gebruik.</div>';
                    } else {
                        // Insert new user
                        $stmt = $conn->prepare("
                            INSERT INTO users (username, email, password, is_admin)
                            VALUES (:username, :email, :password, :is_admin)
                        ");
                        $stmt->bindParam(':username', $username);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':password', $password);
                        $stmt->bindParam(':is_admin', $isAdminUser);
                        
                        if ($stmt->execute()) {
                            $message = '<div class="alert alert-success">Nieuwe gebruiker is succesvol toegevoegd.</div>';
                            // Clear form data
                            $username = '';
                            $email = '';
                            $password = '';
                            $isAdminUser = 0;
                        } else {
                            $message = '<div class="alert alert-danger">Fout bij het toevoegen van de gebruiker.</div>';
                        }
                    }
                }
            }
        } else {
            $message = '<div class="alert alert-danger"><ul>';
            foreach ($errors as $error) {
                $message .= "<li>$error</li>";
            }
            $message .= '</ul></div>';
        }
    }
}

// Get all users
$stmt = $conn->prepare("SELECT id, username, email, is_admin, created_at FROM users ORDER BY username");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Budget Baas - Admin Dashboard</title>
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
      <div class="content-container" style="max-width: 800px;">
        <h1 class="mb-4">Admin Dashboard</h1>
        <h3 class="mb-4">Gebruikersbeheer</h3>
        
        <?php echo $message; ?>
        
        <div class="card mb-4">
          <div class="card-header">
            <h5><?php echo $editUser ? 'Gebruiker bewerken' : 'Nieuwe gebruiker toevoegen'; ?></h5>
          </div>
          <div class="card-body">
            <form method="post" action="admin.php">
              <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'add'; ?>">
              <?php if ($editUser): ?>
                <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
              <?php endif; ?>
              
              <div class="mb-3">
                <label for="username" class="form-label">Gebruikersnaam *</label>
                <input type="text" class="form-control" id="username" name="username" required 
                       value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>">
              </div>
              
              <div class="mb-3">
                <label for="email" class="form-label">E-mail *</label>
                <input type="email" class="form-control" id="email" name="email" required 
                       value="<?php echo $editUser ? htmlspecialchars($editUser['email']) : ''; ?>">
              </div>
              
              <div class="mb-3">
                <label for="password" class="form-label"><?php echo $editUser ? 'Wachtwoord (laat leeg om niet te wijzigen)' : 'Wachtwoord *'; ?></label>
                <input type="password" class="form-control" id="password" name="password" <?php echo $editUser ? '' : 'required'; ?>>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" value="1">
                <label class="form-check-label" for="is_admin">Beheerder</label>
              </div>
              
              <button type="submit" class="btn btn-danger">
                <?php echo $editUser ? 'Gebruiker bijwerken' : 'Gebruiker toevoegen'; ?>
              </button>
              
              <?php if ($editUser): ?>
                <a href="admin.php" class="btn btn-outline-secondary">Annuleren</a>
              <?php endif; ?>
            </form>
          </div>
        </div>
        
        <h3 class="mb-3">Gebruikers</h3>
        
        <?php if (empty($users)): ?>
          <div class="alert alert-info">Geen gebruikers gevonden.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Gebruikersnaam</th>
                  <th>E-mail</th>
                  <th>Rol</th>
                  <th>Aangemaakt op</th>
                  <th>Acties</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($users as $userItem): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($userItem['username']); ?></td>
                    <td><?php echo htmlspecialchars($userItem['email']); ?></td>
                    <td><?php echo $userItem['is_admin'] ? 'Admin' : 'Gebruiker'; ?></td>
                    <td><?php echo date('d-m-Y', strtotime($userItem['created_at'])); ?></td>
                    <td>
                      <a href="admin.php?edit=<?php echo $userItem['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit"></i>
                      </a>
                      
                      <?php if ($userItem['id'] != $user['id']): ?>
                        <a href="admin.php?delete=<?php echo $userItem['id']; ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?');">
                          <i class="fas fa-trash"></i>
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
        
        <div class="mt-4 mb-5">
          <a href="homepage.php" class="btn btn-outline-secondary">Terug naar homepage</a>
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