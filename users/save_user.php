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
$email = trim($_POST['email'] ?? '');
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
    $email === '' ||
    $password === '' ||
    !in_array($role, $allowed_roles)
) {

    die("Invalid user information.");

}


/* ==============================
   Validate Email
================================ */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    die("Please enter a valid email address.");

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
   Check Duplicate Email
================================ */

$email_check_stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM users WHERE email = ?"
);

mysqli_stmt_bind_param(
    $email_check_stmt,
    "s",
    $email
);

mysqli_stmt_execute($email_check_stmt);

$email_check_result = mysqli_stmt_get_result($email_check_stmt);

if (mysqli_num_rows($email_check_result) > 0) {

    die("Email address already exists.");

}


/* ==============================
   Hash Password
================================ */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* ==============================
   Start Transaction
================================ */

mysqli_begin_transaction($conn);


try {

    /* ==============================
       Create User
    ================================ */

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO users
        (username, email, password, full_name, role)
        VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $username,
        $email,
        $hashed_password,
        $full_name,
        $role
    );

    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            "Failed to create user."
        );

    }


    /* ==============================
       Get New User ID
    ================================ */

    $user_id = mysqli_insert_id($conn);


    /* ==============================
       If Technician
       Create Technician Record
    ================================ */

    if ($role === 'Technician') {

        $technician_stmt = mysqli_prepare(
            $conn,
            "INSERT INTO technicians
            (
                full_name,
                email,
                status,
                user_id
            )
            VALUES (?, ?, 'Available', ?)"
        );

        mysqli_stmt_bind_param(
            $technician_stmt,
            "ssi",
            $full_name,
            $email,
            $user_id
        );

        if (!mysqli_stmt_execute($technician_stmt)) {

            throw new Exception(
                "Failed to create technician record."
            );

        }

    }


    /* ==============================
       Everything Successful
    ================================ */

    mysqli_commit($conn);

    header("Location: index.php");
    exit();


} catch (Exception $e) {

    /* ==============================
       Something Failed
    ================================ */

    mysqli_rollback($conn);

    die(
        "Failed to create user: " .
        htmlspecialchars($e->getMessage())
    );

}

?>