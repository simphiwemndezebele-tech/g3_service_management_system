<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");

$customers = mysqli_query($conn, "SELECT id, customer_name FROM customers ORDER BY customer_name ASC");
?>

<div class="main-content">

<h1>Add Machine</h1>

<form action="save_machine.php" method="POST">

<label>Customer</label><br>

<select name="customer_id" required>
    <option value="">-- Select Customer --</option>

    <?php while($customer = mysqli_fetch_assoc($customers)){ ?>

        <option value="<?php echo $customer['id']; ?>">
            <?php echo $customer['customer_name']; ?>
        </option>

    <?php } ?>

</select>

<br><br>

<label>Asset Number</label><br>
<input type="text" name="asset_number">

<br><br>

<label>Brand</label><br>
<input type="text" name="brand" value="Olivetti">

<br><br>

<label>Machine Model</label><br>
<input type="text" name="machine_model" required>

<br><br>

<label>Machine Type</label><br>

<select name="machine_type">
    <option>Printer</option>
    <option>Copier</option>
    <option>Scanner</option>
    <option>Multifunction Printer</option>
</select>

<br><br>

<label>Serial Number</label><br>
<input type="text" name="serial_number">

<br><br>

<label>IP Address</label><br>
<input type="text" name="ip_address">

<br><br>

<label>Location</label><br>
<input type="text" name="location">

<br><br>

<label>Installation Date</label><br>
<input type="date" name="installation_date">

<br><br>

<label>Status</label><br>

<select name="status">
    <option>Active</option>
    <option>In Service</option>
    <option>Waiting for Parts</option>
    <option>Ready for Collection</option>
    <option>Collected</option>
    <option>Out of Service</option>
</select>

<br><br>

<button type="submit" name="save">
    Save Machine
</button>

</form>

</div>

<?php
include("../includes/footer.php");
?>