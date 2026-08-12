<?php
session_start();

require_once("../includes/permissions.php");
requireRole(['Manager', 'Reception', 'Technician']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
include("../includes/status_badge.php");

$search = "";

if (isset($_GET['search'])) {

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $sql = "SELECT
                machines.*,
                customers.customer_name
            FROM machines
            INNER JOIN customers
                ON machines.customer_id = customers.id
            WHERE
                machines.asset_number LIKE '%$search%'
                OR machines.machine_model LIKE '%$search%'
                OR machines.serial_number LIKE '%$search%'
                OR machines.ip_address LIKE '%$search%'
                OR customers.customer_name LIKE '%$search%'
            ORDER BY machines.id DESC";

} else {

    $sql = "SELECT
                machines.*,
                customers.customer_name
            FROM machines
            INNER JOIN customers
                ON machines.customer_id = customers.id
            ORDER BY machines.id DESC";

}

$result = mysqli_query($conn, $sql);
?>

<div class="main-content">

<h1>Machine Management</h1>
<form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">

    <input
        type="text"
        name="search"
        placeholder="Search..."
        value="<?php echo $search; ?>">

    <button class="btn btn-search" type="submit">
        Search
    </button>

    <a href="view_machines.php" class="btn btn-add">
        Clear
    </a>

</form>

<br>

<p>
     <a href="add_machine.php" class="btn btn-add">
+ Add New Machine
</a>
</p>

<table border="1" cellpadding="10" cellspacing="0" width="100%">

<tr>
    <th>Asset No.</th>
    <th>Customer</th>
    <th>Model</th>
    <th>Serial Number</th>
    <th>IP Address</th>
    <th>Status</th>
    <th>Action</th>
</tr>

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['asset_number']; ?></td>

<td><?php echo $row['customer_name']; ?></td>

<td><?php echo $row['machine_model']; ?></td>

<td><?php echo $row['serial_number']; ?></td>

<td><?php echo $row['ip_address']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<a href="edit_machine.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">✏️
Edit
</a> |

<a href="delete_machine.php?id=<?php echo $row['id']; ?>"
class="btn btn-delete"
onclick="return confirm('Delete this machine?');">🗑️
Delete
</a>

</td>

</tr>

<?php } ?>

</table>

</div>

<?php
include("../includes/footer.php");
?>