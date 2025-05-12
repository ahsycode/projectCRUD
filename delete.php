<?php include 'connection.php'; ?>

<?php

session_start();
if (isset($_SESSION["username"])) {

    //delete

    if (isset($_GET["id"])) {
    $stmt = $conn->prepare("DELETE FROM projecten WHERE id = :id");
    $stmt->bindparam(':id', $_GET["id"]);
    $stmt->execute();
   }
    header("location: index.php");
}
?>