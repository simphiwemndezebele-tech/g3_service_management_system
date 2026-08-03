<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");

$id = $_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM machines WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<div class="main-content">

<h1>Edit Machine</h1>

<form action="update_machine.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<label>Asset Number</label><br>
<input type="text" name="asset_number" value="<?php echo $row['asset_number']; ?>">

<br><br>

<label>Brand</label><br>
<input type="text" name="brand" value="<?php echo $row['brand']; ?>">

<br><br>

<label>Machine Model</label><br>
<input type="text" name="machine_model" value="<?php echo $row['machine_model']; ?>">

<br><br>

<label>Machine Type</label><br>

<select name="machine_type">

    <option value="Printer" <?php if($row['machine_type']=="Printer") echo "selected"; ?>>Printer</option>

    <option value="Copier" <?php if($row['machine_type']=="Copier") echo "selected"; ?>>Copier</option>

    <option value="Scanner" <?php if($row['machine_type']=="Scanner") echo "selected"; ?>>Scanner</option>

    <option value="Multifunction Printer" <?php if($row['machine_type']=="Multifunction Printer") echo "selected"; ?>>Multifunction Printer</option>

</select>

<br><br>

<label>Serial Number</label><br>
<input type="text" name="serial_number" value="<?php echo $row['serial_number']; ?>">

<br><br>

<label>IP Address</label><br>
<input type="text" name="ip_address" value="<?php echo $row['ip_address']; ?>">

<br><br>

<label>Location</label><br>
<input type="text" name="location" value="<?php echo $row['location']; ?>">

<br><br>

<label>Status</label><br>

<select name="status">

    <option value="Active" <?php if($row['status']=="Active") echo "selected"; ?>>Active</option>

    <option value="In Service" <?php if($row['status']=="In Service") echo "selected"; ?>>In Service</option>

    <option value="Waiting for Parts" <?php if($row['status']=="Waiting for Parts") echo "selected"; ?>>Waiting for Parts</option>

    <option value="Ready for Collection" <?php if($row['status']=="Ready for Collection") echo "selected"; ?>>Ready for Collection</option>

    <option value="Collected" <?php if($row['status']=="Collected") echo "selected"; ?>>Collected</option>

    <option value="Out of Service" <?php if($row['status']=="Out of Service") echo "selected"; ?>>Out of Service</option>

</select>
<br><br>

<label>Installation Date</label><br>
<input type="date" name="installation_date" value="<?php echo $row['installation_date']; ?>">

<br><br>

<button type="submit" name="update">Update Machine</button>

</form>

</div>

<?php include("../includes/footer.php"); ?>