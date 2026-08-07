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

$search = "";

if(isset($_GET['search'])){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql = "SELECT
                sr.*,
                c.customer_name,
                m.asset_number,
                m.machine_model,
                t.full_name

            FROM service_requests sr

            INNER JOIN customers c
            ON sr.customer_id=c.id

            INNER JOIN machines m
            ON sr.machine_id=m.id

            INNER JOIN technicians t
            ON sr.technician_id=t.id

            WHERE

            sr.request_number LIKE '%$search%'

            OR c.customer_name LIKE '%$search%'

            OR m.asset_number LIKE '%$search%'

            OR m.machine_model LIKE '%$search%'

            OR t.full_name LIKE '%$search%'

            OR sr.status LIKE '%$search%'

            ORDER BY sr.id DESC";

}else{

    $sql = "SELECT
                sr.*,
                c.customer_name,
                m.asset_number,
                m.machine_model,
                t.full_name

            FROM service_requests sr

            INNER JOIN customers c
            ON sr.customer_id=c.id

            INNER JOIN machines m
            ON sr.machine_id=m.id

            INNER JOIN technicians t
            ON sr.technician_id=t.id

            ORDER BY sr.id DESC";

}

$result=mysqli_query($conn,$sql);

?>

<div class="main-content">

<h1>Service Request Management</h1>

<form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">

<input
type="text"
name="search"
placeholder="Search..."
value="<?php echo $search;?>">

<button class="btn btn-search">
Search
</button>

<a href="view_requests.php" class="btn btn-add">
Clear
</a>

</form>

<p>

<a href="add_request.php" class="btn btn-add">

+ Add Service Request

</a>

</p>

<table>

<tr>

<th>No.</th>

<th>Request No.</th>

<th>Customer</th>

<th>Machine</th>

<th>Technician</th>

<th>Priority</th>

<th>Status</th>

<th>Date</th>

<th>Action</th>

</tr>

<?php

$no=1;

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $no++; ?></td>

<td><?php echo $row['request_number']; ?></td>

<td><?php echo $row['customer_name']; ?></td>

<td>

<?php

echo $row['asset_number']." - ".$row['machine_model'];

?>

</td>

<td><?php echo $row['full_name']; ?></td>

<td>

<?php

if($row['priority']=="High"){

    echo "<span class='badge badge-high'>High</span>";

}elseif($row['priority']=="Medium"){

    echo "<span class='badge badge-medium'>Medium</span>";

}else{

    echo "<span class='badge badge-low'>Low</span>";

}

?>

</td>

<td>

<?php

if($row['status']=="Pending"){

    echo "<span class='badge badge-pending'>Pending</span>";

}elseif($row['status']=="In Progress"){

    echo "<span class='badge badge-progress'>In Progress</span>";

}else{

    echo "<span class='badge badge-completed'>Completed</span>";

}

?>

</td>

<td><?php echo date("d M Y",strtotime($row['request_date'])); ?></td>

<td>

<a
href="edit_request.php?id=<?php echo $row['id'];?>"
class="btn btn-edit">

✏️ Edit

</a>

<a href="../job_cards/generate_job_card.php?id=<?php echo $row['id']; ?>" class="btn btn-add">
    📋 Generate
</a>

<a
href="delete_request.php?id=<?php echo $row['id'];?>"
class="btn btn-delete"
onclick="return confirm('Delete this request?')">

🗑️ Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

<?php
include("../includes/footer.php");
?>