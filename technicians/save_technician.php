<?php
include("../config/db.php");

$full_name = $_POST['full_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$specialization = $_POST['specialization'];
$status = $_POST['status'];

$sql = "INSERT INTO technicians
(full_name, phone, email, specialization, status)

VALUES
(?,?,?,?,?)";

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

mysqli_stmt_execute($stmt);

header("Location: view_technicians.php");
exit();
?>