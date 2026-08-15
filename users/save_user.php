<?php
session_start();

require_once("../includes/permissions.php");

requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");

if (!isset($_POST['save_user'])) {
    header("Location: add_user.php");
    exit();
}


/* ==============================
   Get Form Data
================================ */

$username = trim($_POST['username'] ?? '');
$full_name = trim($_POST['full_name'] ?? '');
$password = $_POST['password'] ?? '';
$role = $_POST['role'] ?? '';


/* ==============================
   Validate Input
================================ */

$allowed_roles = [
    'Manager',
    'Technician',
    'Reception'
];

if (
    $username === '' ||
    $full_name === '' ||
    $password === '' ||
    !in_array($role, $allowed_roles)
) {

    die("Invalid user information.");

}


/* ==============================
   Check Duplicate Username
================================ */

$check_stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE username = ?"
);

mysqli_stmt_bind_param(
    $check_stmt,
    "s",
    $username
);

mysqli_stmt_execute($check_stmt);

$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) > 0) {

    die("Username already exists.");

}


/* ==============================
   Hash Password
================================ */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* ==============================
   Insert User
================================ */

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users
    (username, password, full_name, role)
    VALUES (?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $username,
    $hashed_password,
    $full_name,
    $role
);

if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php");
    exit();

} else {

    die("Failed to create user: " . mysqli_error($conn));

}
?>