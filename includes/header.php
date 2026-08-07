<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>G3 Service Management System</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/print.css">
</head>

<body>

<div class="header">

    <div class="user-info">
        Welcome,
        <strong><?php echo $_SESSION['full_name']; ?></strong>
        (<?php echo $_SESSION['role']; ?>)
    </div>

</div>