<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


/* ==============================
   Load Company Settings
   ============================== */

include_once(__DIR__ . "/../config/db.php");

$settings_result = mysqli_query(
    $conn,
    "SELECT company_name FROM settings WHERE id=1"
);

$settings = mysqli_fetch_assoc($settings_result);

$company_name = $settings['company_name'] ?? 'G3 Systems';

?>

<!DOCTYPE html>
<html>

<head>

    <meta charset="UTF-8">

    <title>
        <?php echo htmlspecialchars($company_name); ?> Service Management System
    </title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/print.css">

</head>

<body>

<div class="header">

    <div class="user-info">

        Welcome,

        <strong>
            <?php echo htmlspecialchars($_SESSION['full_name']); ?>
        </strong>

        (<?php echo htmlspecialchars($_SESSION['role']); ?>)

    </div>

</div>