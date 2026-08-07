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

    $sql = "SELECT * FROM technicians
            WHERE full_name LIKE '%$search%'
            OR phone LIKE '%$search%'
            OR email LIKE '%$search%'
            OR specialization LIKE '%$search%'
            OR status LIKE '%$search%'
            ORDER BY id DESC";

}else{

    $sql = "SELECT * FROM technicians
            ORDER BY id DESC";
}

$result = mysqli_query($conn,$sql);
?>

<div class="main-content">

<h1>Technician Management</h1>

<form method="GET" class="search-form">

<input type="text"
name="search"
placeholder="Search technician..."
value="<?php echo $search; ?>">

<button type="submit" class="btn btn-search">
Search
</button>

<a href="view_technicians.php" class="btn btn-add">
Clear
</a>

</form>

<br>

<p>

<a href="add_technician.php" class="btn btn-add">
+ Add Technician
</a>

</p>

<table>

<tr>

<th>Tech-No.</th>
<th>Full Name</th>
<th>Phone</th>
<th>Email</th>
<th>Specialization</th>
<th>Status</th>
<th>Action</th>

</tr>

<?php

$no=1;

while($row=mysqli_fetch_assoc($result))
{

?>

<tr>

<td><?php echo $no++; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['phone']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['specialization']; ?></td>

<td><?php echo $row['status']; ?></td>

<td>

<a href="edit_technician.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">

✏️ Edit

</a>

<a href="delete_technician.php?id=<?php echo $row['id']; ?>"
class="btn btn-delete"
onclick="return confirm('Delete this technician?')">

🗑️ Delete

</a>

</td>

</tr>

<?php
}
?>

</table>

</div>

<?php
include("../includes/footer.php");
?>