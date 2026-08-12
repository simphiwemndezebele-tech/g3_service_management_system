<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $sql = "DELETE FROM customers WHERE id=?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {

        header("Location: view_customers.php");
        exit();

    } else {

        echo "Error deleting customer.";

    }
}
?>