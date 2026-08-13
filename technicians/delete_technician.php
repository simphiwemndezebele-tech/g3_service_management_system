<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Get Technician ID
   ============================== */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_technicians.php");
    exit();
}


/* ==============================
   Delete Technician
   ============================== */

$sql = "DELETE FROM technicians WHERE id=?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: view_technicians.php");
    exit();

} else {

    echo "Error deleting technician: " . mysqli_error($conn);

}

?>