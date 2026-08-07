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

$sql = "SELECT * FROM technicians WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
?>

<div class="main-content">

<h1>Edit Technician</h1>

<form action="update_technician.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<label>Full Name</label>
<input type="text" name="full_name"
value="<?php echo $row['full_name']; ?>" required>

<label>Phone Number</label>
<input type="text" name="phone"
value="<?php echo $row['phone']; ?>">

<label>Email Address</label>
<input type="email" name="email"
value="<?php echo $row['email']; ?>">

<label>Specialization</label>
<input type="text" name="specialization"
value="<?php echo $row['specialization']; ?>">

<label>Status</label>

<select name="status">

<option value="Available"
<?php if($row['status']=="Available") echo "selected"; ?>>
Available
</option>

<option value="Busy"
<?php if($row['status']=="Busy") echo "selected"; ?>>
Busy
</option>

<option value="On Leave"
<?php if($row['status']=="On Leave") echo "selected"; ?>>
On Leave
</option>

</select>

<br><br>

<button type="submit" class="btn btn-edit">
Update Technician
</button>

<a href="view_technicians.php" class="btn btn-delete">
Cancel
</a>

</form>

</div>

<?php
include("../includes/footer.php");
?>