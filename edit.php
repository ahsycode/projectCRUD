<?php
include 'connection.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $stmt = $conn->prepare("UPDATE projecten SET title = :title, desc_short = :desc_short, desc_long = :desc_long, type = :type, date = :date WHERE id = :id");
    $stmt->bindParam(':id', $_POST["id"]);
    $stmt->bindParam(':title', $_POST["title"]);
    $stmt->bindParam(':desc_long', $_POST["desc_long"]);
    $stmt->bindParam(':desc_short', $_POST["desc_short"]);
    $stmt->bindParam(':date', $_POST["date"], PDO::PARAM_STR);
    $stmt->bindParam(':type', $_POST["type"]);

    $stmt->execute();

    // Redirect back to index.php after updating
    header("location: index.php");
    exit();
}

// Fetch project details for editing
$stmt = $conn->prepare("SELECT * FROM projecten WHERE id = :id");
$stmt->bindParam(':id', $_GET["id"]);
$stmt->execute();
$result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
$projectData = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portfolio Website - Edit</title>
    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
    />
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">

<main class="form-signin w-25 m-auto">

    <form action="" method="post">
        <h1 class="h3 mb-3 fw-normal">Edit Info</h1>

        <?php foreach ($projectData as $project) { ?>

            <input type="hidden" name="id" value="<?= $project["id"]; ?>">

            <div class="form-floating">
                <input type="text" class="form-control"
                       name="title"
                       id="floatingInput"
                       placeholder="Title"
                       value="<?= $project["title"]; ?>"
                />
                <label for="floatingInput">Title</label>
            </div>

            <div class="form-floating">
                <input type="text" name="desc_short" class="form-control" id="floatingPassword" placeholder="Short Description" value="<?= $project["desc_short"]; ?>"/>
                <label for="floatingPassword">Short Description</label>
            </div>

            <div class="form-floating">
                <input type="text" name="desc_long" class="form-control" id="floatingPassword" placeholder="Long Description" value="<?= $project["desc_long"]; ?>"/>
                <label for="floatingPassword">Long Description</label>
            </div>

            <div class="form-floating">
                <input type="date" name="date" class="form-control" id="floatingPassword" value="<?= $project["date"]; ?>"/>
                <label for="floatingPassword">Date</label>
            </div>

            <div class="form-floating">
                <input type="text" name="type" class="form-control" id="floatingPassword" placeholder="Type" value="<?= $project["type"]; ?>"/>
                <label for="floatingPassword">Type</label>
            </div>

            <input type="submit" value="Submit" class="btn btn-primary w-100 py-2">

        <?php } ?>

    </form>
</main>

<script
        src="/docs/5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"
></script>

</body>
</html>