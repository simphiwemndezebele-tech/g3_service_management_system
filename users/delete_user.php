<?php
session_start();

require_once("../includes/permissions.php");

requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");


/* ==============================
   Check User ID
================================ */

if (!isset($_GET['id'])) {

    header("Location: index.php");
    exit();

}

$id = intval($_GET['id']);


/* ==============================
   Prevent Self Deletion
================================ */

if ($id === intval($_SESSION['user_id'])) {

    die("You cannot delete your own account while you are logged in.");

}


/* ==============================
   Check User Exists
================================ */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    die("User not found.");

}


/* ==============================
   Delete User
================================ */

$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM users WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php?deleted=1");
    exit();

} else {

    die(
        "Failed to delete user: "
        . mysqli_error($conn)
    );

}
?>