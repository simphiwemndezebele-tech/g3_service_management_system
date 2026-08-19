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

/* ==============================
   Notification Count
   ============================== */

$user_id = $_SESSION['user_id'] ?? 0;

$unread_notifications = 0;

if ($user_id > 0) {

    $notification_query = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM notifications
         WHERE user_id = $user_id
         AND is_read = 0"
    );

    if ($notification_query) {

        $notification_data = mysqli_fetch_assoc(
            $notification_query
        );

        $unread_notifications = $notification_data['total'] ?? 0;
    }
}

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
            <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>
        </strong>

        (<?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?>)

    </div>


    <!-- ==============================
         Notification Bell
         ============================== -->

    <div class="notification-area">

        <a href="../notifications/index.php" class="notification-bell">

            🔔

            <?php if ($unread_notifications > 0): ?>

                <span class="notification-count">

                    <?php echo $unread_notifications; ?>

                </span>

            <?php endif; ?>

        </a>

    </div>

</div>