<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager']);

include("../config/db.php");


/* ==============================
   Check Form Submission
   ============================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: view_technicians.php");
    exit();
}


/* ==============================
   Get Technician ID
   ============================== */

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_technicians.php");
    exit();
}


/* ==============================
   Receive Form Data
   ============================== */

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$email = trim($_POST['email'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$status = trim($_POST['status'] ?? 'Available');


if ($full_name === '') {
    die("Technician name is required.");
}


/* ==============================
   Update Technician
   ============================== */

$sql = "UPDATE technicians
        SET
            full_name=?,
            phone=?,
            email=?,
            specialization=?,
            status=?
        WHERE id=?";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sssssi",
    $full_name,
    $phone,
    $email,
    $specialization,
    $status,
    $id
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: view_technicians.php");
    exit();

} else {

    echo "Error: " . mysqli_error($conn);

}

?>