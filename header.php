<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keuzemodules</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="top-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6">
                    <div class="logo-container">
                        <img src="logo.png" alt="Logo" class="logo">
                    </div>
                </div>
                <div class="col-6">
                    <nav class="auth-nav">
                        <?php if (isset($_SESSION["username"])) { ?>
                            <form action="logout.php" method="get" class="d-inline">
                                <input type="submit" value="Uitloggen" class="btn btn-outline-dark">
                            </form>
                        <?php } else { ?>
                            <a href="login.php">Aanmelden</a>
                        <?php } ?>
                    </nav>
                </div>
            </div>
        </div>
    </header>
</body>
</html> 