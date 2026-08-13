<?php

session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");


/* ==============================
   Get Customer ID
   ============================== */

$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header("Location: view_customers.php");
    exit();
}


/* ==============================
   Get Customer
   ============================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM customers
     WHERE id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    header("Location: view_customers.php");
    exit();
}

$row = mysqli_fetch_assoc($result);

?>


<div class="main-content">

<h1>Edit Customer</h1>

<form action="update_customer.php" method="POST">

    <input
        type="hidden"
        name="id"
        value="<?php echo $row['id']; ?>"
    >

    <label>Customer Name</label><br>

    <input
        type="text"
        name="customer_name"
        value="<?php echo htmlspecialchars($row['customer_name']); ?>"
        required
    >

    <br><br>


    <label>Company Name</label><br>

    <input
        type="text"
        name="company_name"
        value="<?php echo htmlspecialchars($row['company_name']); ?>"
    >

    <br><br>


    <label>Phone</label><br>

    <input
        type="text"
        name="phone"
        value="<?php echo htmlspecialchars($row['phone']); ?>"
    >

    <br><br>


    <label>Email</label><br>

    <input
        type="email"
        name="email"
        value="<?php echo htmlspecialchars($row['email']); ?>"
    >

    <br><br>


    <label>Address</label><br>

    <textarea name="address"><?php echo htmlspecialchars($row['address']); ?></textarea>

    <br><br>


    <button type="submit" name="update">
        Update Customer
    </button>

</form>

</div>


<?php
include("../includes/footer.php");
?>