<?php include 'connection.php';?>
 
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portfolio Website - Overzichtspagina</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
  </head>
  <body>
    <main>
      <div class="container">
      <?php
      $stmt = $conn->prepare("SELECT id, title, desc_long, desc_short, type, date  FROM projecten");
      $conn = new PDO("mysql:host=$servername;dbname=crud", $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $stmt->execute();
 
      // set the resulting array to associative
      $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
      foreach($stmt->fetchAll() as $k=>$v) {
    
      }
    ?>
        <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 g-1 projects">
          <div id="project1" class="project card shadow-sm card-body m-2">
            <div class="card-text">
            <?php
              $stmt = $conn->prepare("SELECT desc_long, type, date FROM projecten");
              $stmt->execute();
              $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
              foreach ($stmt->fetchAll() as $k => $v)
            ?>
            <a href="detail.php"></a><h2>Titel van project <?php echo $_GET["id"];?></h2>
              <?php echo $v["desc_long"] ?><br>
              <?php echo $v["type"] ?><br>
              <?php echo $v["date"] ?><br>
            </div>
          </div>
        </div>
      </div>
    </main>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"
    ></script>
  </body>
</html>