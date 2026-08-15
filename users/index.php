<?php
session_start();

require_once("../includes/permissions.php");

requireRole(['Manager']);

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");
?>

<div class="main-content">

<h1>👥 User Management</h1>

<p>Manage system users and their assigned roles.</p>

<br>

<a href="add_user.php" class="btn btn-add">
    ➕ Add User
</a>

<br><br>

<table>

<tr>

<th>No.</th>
<th>Username</th>
<th>Full Name</th>
<th>Role</th>
<th>Actions</th>

</tr>

<?php

$sql = "
SELECT
    id,
    username,
    full_name,
    role
FROM users
ORDER BY id DESC
";

$result = mysqli_query($conn, $sql);

$no = 1;

if ($result && mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {

?>

<tr>

<td>
<?php echo $no++; ?>
</td>

<td>
<?php echo htmlspecialchars($row['username']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['full_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($row['role']); ?>
</td>

<td>

<a
href="edit_user.php?id=<?php echo $row['id']; ?>"
class="btn btn-edit">

✏️ Edit

</a>

<a
href="delete_user.php?id=<?php echo $row['id']; ?>"
class="btn btn-delete"
onclick="return confirm('Are you sure you want to delete this user?');"
>
🗑️ Delete
</a>

</td>

</tr>

<?php

    }

} else {

?>

<tr>

<td colspan="5" style="text-align:center;">

No users found.

</td>

</tr>

<?php

}

?>

</table>

</div>

<?php include("../includes/footer.php"); ?>