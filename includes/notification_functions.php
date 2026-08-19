<?php

/*
|--------------------------------------------------------------------------
| G3 Service Management System
| Notification Functions
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Create Notification
|--------------------------------------------------------------------------
*/

function createNotification(
    $conn,
    $user_id,
    $title,
    $message,
    $type = 'info',
    $reference_id = null
) {

    $sql = "
        INSERT INTO notifications
        (
            user_id,
            title,
            message,
            type,
            reference_id
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "isssi",
        $user_id,
        $title,
        $message,
        $type,
        $reference_id
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}


/*
|--------------------------------------------------------------------------
| Get Unread Notification Count
|--------------------------------------------------------------------------
*/

function getUnreadNotificationCount($conn, $user_id)
{

    $sql = "
        SELECT COUNT(*) AS total
        FROM notifications
        WHERE user_id = ?
        AND is_read = 0
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $row['total'] ?? 0;
}


/*
|--------------------------------------------------------------------------
| Get User Notifications
|--------------------------------------------------------------------------
*/

function getUserNotifications($conn, $user_id, $limit = 10)
{

    $limit = (int)$limit;

    $sql = "
        SELECT
            id,
            title,
            message,
            type,
            reference_id,
            is_read,
            created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT $limit
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);

    return $result;
}


/*
|--------------------------------------------------------------------------
| Mark Notification As Read
|--------------------------------------------------------------------------
*/

function markNotificationAsRead($conn, $notification_id, $user_id)
{

    $sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
        AND user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $notification_id,
        $user_id
    );

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}


/*
|--------------------------------------------------------------------------
| Mark All Notifications As Read
|--------------------------------------------------------------------------
*/

function markAllNotificationsAsRead($conn, $user_id)
{

    $sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
        AND is_read = 0
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);

    $success = mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    return $success;
}

?>