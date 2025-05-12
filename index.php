<?php 
include 'connection.php';
include 'header.php';
?>

<div class="header-section">
    <div class="container">
        <h1>Keuzemodules</h1>
        <p>Kies een module uit de onderstaande opties om meer te leren</p>
    </div>
</div>

<div class="container">
    <div class="search-section">
        <form action="" method="get" class="row justify-content-center">
            <div class="col-md-6">
                <div class="input-group">
                    <input name="search" type="search" class="form-control" placeholder="Zoeken..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit" class="btn btn-outline-secondary">Zoeken</button>
                </div>
            </div>
        </form>
    </div>

    <?php
    try {
        if (isset($_GET["search"])) {
            $searchbtn = "%" . $_GET["search"] . "%";
            $stmt = $conn->prepare("SELECT * FROM projecten WHERE title LIKE :search ORDER BY id DESC");
            $stmt->bindParam(':search', $searchbtn);
        } else {
            $stmt = $conn->prepare("SELECT * FROM projecten ORDER BY id DESC");
        }
        $stmt->execute();
    ?>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <?php while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { ?>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-img-container">
                        <?php if(!empty($row['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['title']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x225" alt="Placeholder">
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($row['desc_short']); ?></p>
                        <div class="d-flex justify-content-start flex-wrap">
                            <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                Bekijken
                            </a>
                            <?php if (isset($_SESSION["username"])): ?>
                                <a href="add.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    Toevoegen
                                </a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    Bewerken
                                </a>
                                <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    Verwijderen
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
    ?>
</div>

<?php include 'footer.php'; ?>