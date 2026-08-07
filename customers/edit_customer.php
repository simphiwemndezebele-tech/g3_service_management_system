<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM customers WHERE id='$id'");
$row = mysqli_fetch_assoc($result);
?>

<div class="main-content">

<h1>Edit Customer</h1>

<form action="update_customer.php" method="POST">

    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

    <label>Customer Name</label><br>
    <input type="text" name="customer_name" value="<?php echo $row['customer_name']; ?>" required>

    <br><br>

    <label>Company Name</label><br>
    <input type="text" name="company_name" value="<?php echo $row['company_name']; ?>">

    <br><br>

    <label>Phone</label><br>
    <input type="text" name="phone" value="<?php echo $row['phone']; ?>">

    <br><br>

    <label>Email</label><br>
    <input type="email" name="email" value="<?php echo $row['email']; ?>">

    <br><br>

    <label>Address</label><br>
    <textarea name="address"><?php echo $row['address']; ?></textarea>

    <br><br>

    <button type="submit" name="update">
        Update Customer
    </button>

</form>

</div>

<?php
include("../includes/footer.php");
?>