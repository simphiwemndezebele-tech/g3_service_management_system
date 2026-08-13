<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);

include("../config/db.php");


/* ==============================
   Check Form Submission
   ============================== */

if (!isset($_POST['save'])) {
    header("Location: add_customer.php");
    exit();
}


/* ==============================
   Receive Form Data
   ============================== */

$customer_name = trim($_POST['customer_name'] ?? '');
$company_name  = trim($_POST['company_name'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$email         = trim($_POST['email'] ?? '');
$address       = trim($_POST['address'] ?? '');


/* ==============================
   Validate Customer Name
   ============================== */

if ($customer_name === '') {
    die("Customer name is required.");
}


/* ==============================
   Save Customer
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO customers
    (customer_name, company_name, phone, email, address)
    VALUES (?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $customer_name,
    $company_name,
    $phone,
    $email,
    $address
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: view_customers.php");
    exit();

} else {

    echo "Error: " . mysqli_error($conn);

}

?>