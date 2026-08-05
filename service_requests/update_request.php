<?php
include("../config/db.php");

$sql="UPDATE service_requests SET

customer_id=?,
machine_id=?,
technician_id=?,
issue_description=?,
priority=?,
status=?

WHERE id=?";

$stmt=mysqli_prepare($conn,$sql);

mysqli_stmt_bind_param(

$stmt,

"iiisssi",

$_POST['customer_id'],
$_POST['machine_id'],
$_POST['technician_id'],
$_POST['issue_description'],
$_POST['priority'],
$_POST['status'],
$_POST['id']

);

mysqli_stmt_execute($stmt);

header("Location:view_requests.php");
exit();
?>