<?php
include("../config/db.php");

$id = $_POST['id'];
$full_name = $_POST['full_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$specialization = $_POST['specialization'];
$status = $_POST['status'];

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

mysqli_stmt_execute($stmt);

header("Location: view_technicians.php");
exit();
?>