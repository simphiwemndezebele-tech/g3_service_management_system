<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);

include("../config/db.php");

if(isset($_POST['update'])){

    $id = intval($_POST['id'] ?? 0);

    if($id <= 0){
        header("Location: view_machines.php");
        exit();
    }

$stmt = mysqli_prepare($conn,

"UPDATE machines SET
asset_number=?,
brand=?,
machine_model=?,
machine_type=?,
serial_number=?,
ip_address=?,
location=?,
status=?,
installation_date=?
WHERE id=?");

mysqli_stmt_bind_param(
$stmt,
"sssssssssi",

$_POST['asset_number'],
$_POST['brand'],
$_POST['machine_model'],
$_POST['machine_type'],
$_POST['serial_number'],
$_POST['ip_address'],
$_POST['location'],
$_POST['status'],
$_POST['installation_date'],
$_POST['id']

);

if(mysqli_stmt_execute($stmt)){

header("Location: view_machines.php");
exit();

}else{

echo mysqli_error($conn);

}

}
?>