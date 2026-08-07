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

$sql = "SELECT * FROM service_requests WHERE id=?";
$stmt = mysqli_prepare($conn,$sql);
mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);
$request=mysqli_fetch_assoc($result);

$customers=mysqli_query($conn,"SELECT * FROM customers ORDER BY customer_name");
$machines=mysqli_query($conn,"SELECT * FROM machines WHERE customer_id=".$request['customer_id']." ORDER BY asset_number");
$technicians=mysqli_query($conn,"SELECT * FROM technicians ORDER BY full_name");
?>

<div class="main-content">

<h1>Edit Service Request</h1>

<form action="update_request.php" method="POST">

<input type="hidden" name="id" value="<?php echo $request['id']; ?>">

<label>Customer</label>

<select name="customer_id" required>

<?php while($c=mysqli_fetch_assoc($customers)){ ?>

<option value="<?php echo $c['id']; ?>"
<?php if($request['customer_id']==$c['id']) echo "selected"; ?>>

<?php echo $c['customer_name']; ?>

</option>

<?php } ?>

</select>

<br><br>

<label>Machine</label>

<select name="machine_id" required>

<?php while($m=mysqli_fetch_assoc($machines)){ ?>

<option value="<?php echo $m['id']; ?>"
<?php if($request['machine_id']==$m['id']) echo "selected"; ?>>

<?php echo $m['asset_number']." - ".$m['machine_model']; ?>

</option>

<?php } ?>

</select>

<br><br>

<label>Issue Description</label>

<textarea name="issue_description" rows="5" required><?php echo $request['issue_description']; ?></textarea>

<br><br>

<label>Priority</label>

<select name="priority">

<option value="Low" <?php if($request['priority']=="Low") echo "selected"; ?>>Low</option>

<option value="Medium" <?php if($request['priority']=="Medium") echo "selected"; ?>>Medium</option>

<option value="High" <?php if($request['priority']=="High") echo "selected"; ?>>High</option>

</select>

<br><br>

<label>Technician</label>

<select name="technician_id">

<?php while($t=mysqli_fetch_assoc($technicians)){ ?>

<option value="<?php echo $t['id']; ?>"
<?php if($request['technician_id']==$t['id']) echo "selected"; ?>>

<?php echo $t['full_name']; ?>

</option>

<?php } ?>

</select>

<br><br>

<label>Status</label>

<select name="status">

<option value="Pending" <?php if($request['status']=="Pending") echo "selected"; ?>>Pending</option>

<option value="In Progress" <?php if($request['status']=="In Progress") echo "selected"; ?>>In Progress</option>

<option value="Completed" <?php if($request['status']=="Completed") echo "selected"; ?>>Completed</option>

</select>

<br><br>

<button class="btn btn-edit">Update Request</button>

<a href="view_requests.php" class="btn btn-delete">Cancel</a>

</form>

</div>

<?php include("../includes/footer.php"); ?>