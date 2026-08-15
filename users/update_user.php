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
   Check Request
================================ */

if (!isset($_POST['update_user'])) {

    header("Location: index.php");
    exit();

}


/* ==============================
   Get Form Data
================================ */

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

$username = trim($_POST['username'] ?? '');

$full_name = trim($_POST['full_name'] ?? '');

$password = $_POST['password'] ?? '';

$role = $_POST['role'] ?? '';


/* ==============================
   Validate
================================ */

if ($id <= 0) {

    die("Invalid user.");

}

if ($username === '' || $full_name === '' || $role === '') {

    die("Please complete all required fields.");

}


$allowed_roles = [
    'Manager',
    'Technician',
    'Reception'
];

if (!in_array($role, $allowed_roles, true)) {

    die("Invalid role selected.");

}


/* ==============================
   Check Duplicate Username
================================ */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM users
     WHERE username = ?
     AND id != ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "si",
    $username,
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {

    die("Username already exists. Please choose another username.");

}


/* ==============================
   Update Without Password
================================ */

if ($password === '') {

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET username = ?,
             full_name = ?,
             role = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $username,
        $full_name,
        $role,
        $id
    );

}


/* ==============================
   Update With New Password
================================ */

else {

    $hashed_password = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE users
         SET username = ?,
             full_name = ?,
             password = ?,
             role = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssssi",
        $username,
        $full_name,
        $hashed_password,
        $role,
        $id
    );

}


/* ==============================
   Execute Update
================================ */

if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php?updated=1");
    exit();

} else {

    die(
        "Failed to update user: "
        . mysqli_error($conn)
    );

}
?>