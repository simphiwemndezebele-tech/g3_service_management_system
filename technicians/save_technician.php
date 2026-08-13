<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager']);

include("../config/db.php");


/* ==============================
   Check Form Submission
   ============================== */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: add_technician.php");
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


/* ==============================
   Validate Name
   ============================== */

if ($full_name === '') {
    die("Technician name is required.");
}


/* ==============================
   Save Technician
   ============================== */

$sql = "INSERT INTO technicians
(full_name, phone, email, specialization, status)
VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $full_name,
    $phone,
    $email,
    $specialization,
    $status
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: view_technicians.php");
    exit();

} else {

    echo "Error: " . mysqli_error($conn);

}

?>