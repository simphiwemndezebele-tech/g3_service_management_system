<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include("../config/db.php");
include("../includes/header.php");
include("../includes/sidebar.php");

$search = "";

if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $sql = "SELECT * FROM customers
            WHERE customer_name LIKE '%$search%'
            OR company_name LIKE '%$search%'
            OR phone LIKE '%$search%'
            OR email LIKE '%$search%'
            ORDER BY id DESC";
} else {

    $sql = "SELECT * FROM customers ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);
?>

<div class="main-content">

<h1>Customer Management</h1>
<form method="GET" style="display:flex; gap:10px; margin-bottom:20px;">

    <input
        type="text"
        name="search"
        placeholder="Search..."
        value="<?php echo $search; ?>">

    <button class="btn btn-search" type="submit">
        Search
    </button>

    <a href="view_customers.php" class="btn btn-add">
        Clear
    </a>

</form>

<br>

<p>
    <a href="add_customer.php" class="btn btn-add">
+ Add New Customer
</a>
</p>

<table border="1" cellpadding="10" cellspacing="0" width="100%">

<tr>
    <th>CustomersNo.</th>
    <th>Customer Name</th>
    <th>Company</th>
    <th>Phone</th>
    <th>Email</th>
    <th>Action</th>
</tr>
<?php
$number = 1;
?>
<?php while($row = mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $number++; ?></td>

<td><?php echo $row['customer_name']; ?></td>

<td><?php echo $row['company_name']; ?></td>

<td><?php echo $row['phone']; ?></td>

<td><?php echo $row['email']; ?></td>

<td>

<a href="edit_customer.php?id=<?php echo $row['id']; ?>" class="btn btn-edit">
Edit
</a> |

<a href="delete_customer.php?id=<?php echo $row['id']; ?>"
class="btn btn-delete"
onclick="return confirm('Delete this customer?');">
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