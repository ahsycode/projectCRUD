<?php
include 'connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stmt = $conn->prepare("INSERT INTO projecten (title, desc_long, desc_short, type, date) VALUES (:title, :desc_long, :desc_short, :type, :date)");
    $stmt->bindParam(':title', $_POST["title"]);
    $stmt->bindParam(':desc_long', $_POST["desc_long"]);
    $stmt->bindParam(':desc_short', $_POST["desc_short"]);
    $stmt->bindParam(':date', $_POST["date"], PDO::PARAM_STR);
    $stmt->bindParam(':type', $_POST["type"]);
    
    $stmt->execute();

    // Redirect back to index.php after insertion
    header("location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portfolio Website - Login</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
      crossorigin="anonymous"
    />
  </head>
  <body class="d-flex align-items-center py-4 bg-body-tertiary">
    <main class="form-signin w-25 m-auto">

      <form action="add.php" method="post">
        <h1 class="h3 mb-3 fw-normal">Add info</h1>


        <div class="form-floating">
          <input  type="text" class="form-control" 
            type = "text"
            name="title" 
            class = "form-control"
            id="floatingInput"
            placeholder="title"
          />
          <label for="floatingInput">title</label>
        </div>
        <div class="form-floating">
          <input
            type="text"
            name="desc_short"
            class="form-control"
            id="floatingPassword"
            placeholder="desc short"
          />
          <label for="floatingPassword">desc short</label>
        </div>

        <div class="form-floating">
          <input
            type="text"
            name="desc_long"
            class="form-control"
            id="floatingPassword"
            placeholder="desc long"
          />
          <label for="floatingPassword">desc long</label>
        </div>

        <div class="form-floating">
          <input
            type="date"
            name="date"
            class="form-control"
            id="floatingPassword"
          />
          <label for="floatingPassword">date</label>
        </div>

        <div class="form-floating">
          <input
            type="text"
            name="type"
            class="form-control"
            id="floatingPassword"
            placeholder="type"
          />
          <label for="floatingPassword">type</label>
        </div>

        <input type="submit" value = "submit" class="btn btn-primary w-100 py-2">

      </form>
    </main>
    <script
      src="/docs/5.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"
    ></script>
  </body>
</html>




