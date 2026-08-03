<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="main-content">

    <h1>Add Customer</h1>

    <form action="save_customer.php" method="POST">

        <label>Customer Name</label><br>
        <input type="text" name="customer_name" required>
        <br><br>

        <label>Company Name</label><br>
        <input type="text" name="company_name">
        <br><br>

        <label>Phone Number</label><br>
        <input type="text" name="phone">
        <br><br>

        <label>Email</label><br>
        <input type="email" name="email">
        <br><br>

        <label>Address</label><br>
        <textarea name="address"></textarea>
        <br><br>

        <button type="submit" name="save">
            Save Customer
        </button>

    </form>

</div>

<?php
include("../includes/footer.php");
?>