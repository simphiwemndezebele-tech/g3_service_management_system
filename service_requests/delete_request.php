<?php

include("../config/db.php");

$id=$_GET['id'];

$stmt=mysqli_prepare($conn,"DELETE FROM service_requests WHERE id=?");

mysqli_stmt_bind_param($stmt,"i",$id);

mysqli_stmt_execute($stmt);

header("Location:view_requests.php");

exit();

?>