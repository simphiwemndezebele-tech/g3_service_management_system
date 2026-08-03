<?php

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

$id = $_GET['id'];

$stmt = mysqli_prepare($conn,
"DELETE FROM machines WHERE id=?");

mysqli_stmt_bind_param($stmt,"i",$id);

if(mysqli_stmt_execute($stmt)){

header("Location: view_machines.php");
exit();

}else{

echo mysqli_error($conn);

}
?>