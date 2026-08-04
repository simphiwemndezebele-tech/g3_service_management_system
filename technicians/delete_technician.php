<?php
include("../config/db.php");

$id = $_GET['id'];

$sql = "DELETE FROM technicians WHERE id=?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt,"i",$id);

mysqli_stmt_execute($stmt);

header("Location:view_technicians.php");
exit();
?>