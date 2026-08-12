<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

$id = $_GET['id'];

$sql = "SELECT * FROM job_cards WHERE id='$id'";
$result = mysqli_query($conn,$sql);
$row = mysqli_fetch_assoc($result);
?>

<div class="main-content">

<h1>Edit Job Card</h1>

<form action="update_job_card.php" method="POST">

<input type="hidden" name="id" value="<?php echo $row['id']; ?>">

<label>Job Card Number</label>

<input type="text"
value="<?php echo $row['job_card_number']; ?>"
readonly>

<br><br>

<label>Work Done</label>

<textarea
name="work_done"
rows="6"><?php echo $row['work_done']; ?></textarea>

<br><br>

<label>Remarks</label>

<textarea
name="remarks"
rows="5"><?php echo $row['remarks']; ?></textarea>

<br><br>

<label>Status</label>

<select name="status">

<option value="Open"
<?php if($row['status']=="Open") echo "selected"; ?>>
Open
</option>

<option value="In Progress"
<?php if($row['status']=="In Progress") echo "selected"; ?>>
In Progress
</option>

<option value="Completed"
<?php if($row['status']=="Completed") echo "selected"; ?>>
Completed
</option>

</select>

<br><br>

<button class="btn btn-add">
Save Changes
</button>

</form>

</div>

<?php include("../includes/footer.php"); ?>