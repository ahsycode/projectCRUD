<?php include 'connection.php';?>

<?php

if (isset($_GET["username"]) && isset($_GET["password"]))


 {
  $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
  $stmt->bindParam(':username', $_GET["username"]);
  $stmt->bindParam(':password', $_GET["password"]);
  $stmt->execute();
  $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
  foreach ($stmt->fetchAll() as $k => $v){
    session_start();
    $_SESSION["username"] = $_GET["username"];
    $_SESSION["password"] = $_GET["password"];
    header("Location: index.php");
  }
 }; 
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

      <form action="login.php" method="get">
        <h1 class="h3 mb-3 fw-normal">Please sign in</h1>

        <div class="form-floating">
          <input  type="text" class="form-control" 
            type = "username"
            name="username" 
            class = "form-control"
            id="floatingInput"
            placeholder="name@example.com"
          />
          <label for="floatingInput">Email address</label>
        </div>
        <div class="form-floating">
          <input
            type="password"
            name="password"
            class="form-control"
            id="floatingPassword"
            placeholder="Password"
          />
          <label for="floatingPassword">Password</label>
        </div>

        <input type="submit" value = "login" class="btn btn-primary w-100 py-2">

      </form>
    </main>
    <script
      src="/docs/5.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
      crossorigin="anonymous"
    ></script>
  </body>
</html>




