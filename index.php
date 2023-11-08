<?php include 'connection.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Portfolio Website - Overzichtspagina</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
</head>

<body>

  <style>
    body {
      background-color: #EFABFF
    }
  </style>

  <main>
    <div class="container">
      <?php

      $stmt = $conn->prepare("SELECT id, title, desc_short, desc_long, type, datum FROM projecten");
      $conn = new PDO("mysql:host=$servername;dbname=projecten", $username, $password);
      $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $stmt->execute();

      // set the resulting array to associative
      $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
      foreach ($stmt->fetchAll() as $k => $v) {
        // echo $v["id"];  
      }

      ?>

      <div class="d-flex justify-content-center align-items-center m-4">
        <nav aria-label="search and filter">
          <input type="search" class="form-control ds-input" id="search-input" placeholder="Search..."
            aria-label="Search for..." autocomplete="off" spellcheck="false" role="combobox" aria-autocomplete="list"
            aria-expanded="false" aria-owns="algolia-autocomplete-listbox-0" dir="auto"
            style="position: relative; vertical-align: top;">
        </nav>
      </div>


      <div class="row row-cols-1 row-cols-sm-1 row-cols-md-1 g-1 projects">

        <?php
        $stmt = $conn->prepare("SELECT desc_short FROM projecten");
        $stmt->execute();



        for ($a = 1; $a < 6; $a++) { ?>

          <div id="project <?php echo $a; ?>" class="project card shadow-sm card-body m-2">
            <div class="card-text">
              <h2>Titel van project
                <?php echo $a; ?>
              </h2>
              <div>Hier komt een korte omschrijving van het project.</div>

              <a href="detail.php?id=<?php echo $a; ?> "> <button type="button" class="btn btn-sm btn-outline-secondary">
                  View
                </button></a>
              <button type="button" class="btn btn-sm btn-outline-secondary">
                Edit
              </button>
              <button type="button" class="btn btn-sm btn-outline-secondary">
                Delete
              </button>
            </div>

          </div>

        <?php }
        ; ?>

        <div class="d-flex justify-content-center align-items-center m-4">
          <nav aria-label="Page navigation example">
            <ul class="pagination">
              <li class="page-item">
                <a class="page-link" href="#">Previous</a>
              </li>
              <li class="page-item"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
          </nav>
        </div>

  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
    crossorigin="anonymous"></script>
</body>

</html>