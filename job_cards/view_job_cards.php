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

/* Search */

$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
}

/* Query */

$sql = "
SELECT

job_cards.*,

customers.customer_name,

machines.asset_number,

machines.machine_model,

technicians.full_name AS technician_name

FROM job_cards

INNER JOIN customers
ON job_cards.customer_id = customers.id

INNER JOIN machines
ON job_cards.machine_id = machines.id

INNER JOIN technicians
ON job_cards.technician_id = technicians.id

WHERE

job_card_number LIKE '%$search%'

OR customers.customer_name LIKE '%$search%'

OR machines.asset_number LIKE '%$search%'

OR technicians.full_name LIKE '%$search%'

ORDER BY job_cards.id DESC
";

$result = mysqli_query($conn,$sql);

?>

<div class="main-content">

<h1>Job Card Management</h1>

<p>Manage all generated Job Cards.</p>

<form method="GET">

<input
type="text"
name="search"
placeholder="Search Job Card..."
value="<?php echo htmlspecialchars($search); ?>">

<br><br>

<button class="btn btn-search">

🔍 Search

</button>

</form>

<br>
<table>

    <tr>
        <th>Job Card No.</th>
        <th>Customer</th>
        <th>Asset No.</th>
        <th>Machine Model</th>
        <th>Technician</th>
        <th>Status</th>
        <th>Date Created</th>
        <th>Actions</th>
    </tr>

<?php

if(mysqli_num_rows($result) > 0){

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $row['job_card_number']; ?></td>

<td><?php echo $row['customer_name']; ?></td>

<td><?php echo $row['asset_number']; ?></td>

<td><?php echo $row['machine_model']; ?></td>

<td><?php echo $row['technician_name']; ?></td>

<td>

<?php

if($row['status']=="Open"){

echo "<span style='color:orange;font-weight:bold;'>🟠 Open</span>";

}elseif($row['status']=="In Progress"){

echo "<span style='color:blue;font-weight:bold;'>🔵 In Progress</span>";

}else{

echo "<span style='color:green;font-weight:bold;'>🟢 Completed</span>";

}

?>

</td>

<td><?php echo $row['created_at']; ?></td>

<td>

<a href="edit_job_card.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">
Edit
</a>

<a href="delete_job_card.php?id=<?php echo $row['id']; ?>"
class="btn btn-delete"
onclick="return confirm('Delete this Job Card?');">
Delete
</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8" style="text-align:center;">

No Job Cards Found.

</td>

</tr>

<?php

}

?>

</table>

</div>

<?php include("../includes/footer.php"); ?>