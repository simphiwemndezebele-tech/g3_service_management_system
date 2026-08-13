<?php

session_start();

require_once("../includes/permissions.php");

/* Manager ONLY */
requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Get Customer ID
   ============================== */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_customers.php");
    exit();
}


/* ==============================
   Delete Customer
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM customers WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: view_customers.php");
    exit();

} else {

    echo "Error deleting customer: " . mysqli_error($conn);

}

?>